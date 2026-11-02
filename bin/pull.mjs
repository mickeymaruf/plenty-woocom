#!/usr/bin/env node
/**
 * Pulls the production site into the Local site: replaces the local database with the live one
 * (URLs rewritten to the Local URL), replaces wp-content/uploads and wp-content/plugins with the
 * live folders, and copies ACF field groups edited in the live wp-admin into acf-json/. The rest
 * of the theme is never touched; the repo is its source of truth. Production is only read.
 *
 * Everything replaced locally is kept in `<Local site>/pull-backups/<timestamp>/`, outside the repo,
 * together with the live database dump (it holds customer data, delete old backups when done).
 *
 *   npm run pull                 database, uploads, plugins, ACF JSON
 *   npm run pull -- --db-only    database and ACF JSON only
 *
 * Needs Local for Windows with the site started, plus `ssh` and `tar` on PATH and key-based SSH
 * access to the server (same as deploy). Override the source with DEPLOY_HOST / DEPLOY_SITE_ROOT /
 * DEPLOY_SITE_USER.
 */

import { spawn, execFileSync } from 'node:child_process';
import { copyFileSync, existsSync, mkdirSync, openSync, closeSync, readFileSync, readdirSync, renameSync } from 'node:fs';
import { homedir } from 'node:os';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const HOST = process.env.DEPLOY_HOST || 'root@64.176.26.129';
const SITE_ROOT = process.env.DEPLOY_SITE_ROOT || '/home/plenty/htdocs/64-176-26-129.sslip.io';
const SITE_USER = process.env.DEPLOY_SITE_USER || 'plenty';
const THEME = 'plenty';

const themeDir = resolve( dirname( fileURLToPath( import.meta.url ) ), '..' );
const publicDir = resolve( themeDir, '../../..' );
const localSiteDir = resolve( publicDir, '../..' );
const dbOnly = process.argv.includes( '--db-only' );

/** Finds the Local site that contains this theme, and the PHP, wp-cli and MySQL binaries it runs on. */
function findLocal() {
	if ( process.platform !== 'win32' ) {
		throw new Error( 'pull.mjs only knows where Local for Windows keeps its binaries.' );
	}
	const appData = join( process.env.APPDATA, 'Local' );
	const sitesFile = join( appData, 'sites.json' );
	if ( ! existsSync( sitesFile ) ) {
		throw new Error( `Local's ${ sitesFile } not found. Is Local installed?` );
	}
	const sites = JSON.parse( readFileSync( sitesFile, 'utf8' ) );
	const id = Object.keys( sites ).find( ( key ) => resolve( sites[ key ].path.replace( /^~/, homedir() ) ).toLowerCase() === localSiteDir.toLowerCase() );
	if ( ! id ) {
		throw new Error( `No Local site found at ${ localSiteDir }.` );
	}
	const site = sites[ id ];
	const resources = join( process.env.LOCALAPPDATA, 'Programs', 'Local', 'resources', 'extraResources' );
	const serviceRoots = [ join( appData, 'lightning-services' ), join( resources, 'lightning-services' ) ];

	// Service folders are named like `php-8.2.29+0`; take the first one matching the site's version.
	// Prefer bin/win64: Local's bin/win32 PHP lacks the extensions WordPress needs.
	const findBinary = ( service, file ) => {
		for ( const root of serviceRoots ) {
			const folder = existsSync( root ) && readdirSync( root ).find( ( name ) => name === `${ service.name }-${ service.version }` || name.startsWith( `${ service.name }-${ service.version }+` ) );
			const found = folder && ( findFile( join( root, folder, 'bin', 'win64' ), file ) || findFile( join( root, folder, 'bin' ), file ) );
			if ( found ) {
				return found;
			}
		}
		throw new Error( `${ file } for ${ service.name } ${ service.version } not found in Local's services.` );
	};

	const db = Object.values( site.services ).find( ( service ) => service.role === 'db' );
	return {
		url: `http://${ site.domain }`,
		php: findBinary( site.services.php, 'php.exe' ),
		phpIni: join( appData, 'run', id, 'conf', 'php', 'php.ini' ),
		wpCli: join( resources, 'bin', 'wp-cli', 'wp-cli.phar' ),
		mysql: findBinary( db, 'mysql.exe' ),
		mysqldump: findBinary( db, 'mysqldump.exe' ),
		port: String( db.ports.MYSQL[ 0 ] ),
	};
}

function findFile( dir, file, depth = 3 ) {
	if ( ! existsSync( dir ) ) {
		return null;
	}
	for ( const entry of readdirSync( dir, { withFileTypes: true } ) ) {
		if ( entry.isFile() && entry.name.toLowerCase() === file ) {
			return join( dir, entry.name );
		}
	}
	for ( const entry of readdirSync( dir, { withFileTypes: true } ) ) {
		const found = depth > 0 && entry.isDirectory() && findFile( join( dir, entry.name ), file, depth - 1 );
		if ( found ) {
			return found;
		}
	}
	return null;
}

/** DB_NAME / DB_USER / DB_PASSWORD from the Local site's wp-config.php. */
function localDbConfig() {
	const config = readFileSync( join( publicDir, 'wp-config.php' ), 'utf8' );
	const get = ( name ) => config.match( new RegExp( `define\\(\\s*'${ name }',\\s*'([^']*)'` ) )?.[ 1 ];
	return { name: get( 'DB_NAME' ), user: get( 'DB_USER' ), password: get( 'DB_PASSWORD' ) };
}

function run( cmd, args, options ) {
	return new Promise( ( resolve, reject ) => {
		const child = spawn( cmd, args, options );
		child.on( 'error', reject );
		child.on( 'close', ( code ) => ( code === 0 ? resolve( child ) : reject( new Error( `${ cmd } exited with ${ code }` ) ) ) );
		if ( options.onSpawn ) {
			options.onSpawn( child );
		}
	} );
}

/** Runs `cmd` with stdout written to `file`. */
async function runToFile( cmd, args, file, env ) {
	const fd = openSync( file, 'w' );
	try {
		await run( cmd, args, { stdio: [ 'ignore', fd, 'inherit' ], env } );
	} finally {
		closeSync( fd );
	}
}

function remote( script ) {
	return execFileSync( 'ssh', [ HOST, script ], { encoding: 'utf8' } ).trim();
}

/** Copies live acf-json files that are new or newer (by ACF's "modified" stamp) into the theme. */
function syncAcfJson( liveDir ) {
	const modified = ( file ) => JSON.parse( readFileSync( file, 'utf8' ) ).modified || 0;
	const localDir = join( themeDir, 'acf-json' );
	const copied = [];
	if ( ! existsSync( liveDir ) ) {
		return copied;
	}
	for ( const name of readdirSync( liveDir ).filter( ( file ) => file.endsWith( '.json' ) ) ) {
		const live = join( liveDir, name );
		const local = join( localDir, name );
		if ( existsSync( local ) && ( readFileSync( live, 'utf8' ) === readFileSync( local, 'utf8' ) || modified( live ) <= modified( local ) ) ) {
			continue;
		}
		copyFileSync( live, local );
		copied.push( name );
	}
	return copied;
}

async function main() {
	const local = findLocal();
	const db = localDbConfig();
	const mysqlEnv = { ...process.env, MYSQL_PWD: db.password };
	const mysqlArgs = [ '-h', '127.0.0.1', '-P', local.port, '-u', db.user ];
	const wp = ( ...args ) => execFileSync( local.php, [ '-d', 'display_startup_errors=0', '-c', local.phpIni, local.wpCli, ...args ], { cwd: publicDir, encoding: 'utf8', stdio: [ 'ignore', 'pipe', 'pipe' ] } ).trim();

	try {
		execFileSync( local.mysql, [ ...mysqlArgs, '-e', 'SELECT 1' ], { env: mysqlEnv, stdio: 'ignore' } );
	} catch {
		throw new Error( 'Cannot reach the Local database. Start the site in Local first.' );
	}

	let localUrl = local.url;
	try {
		localUrl = wp( 'option', 'get', 'home' ) || localUrl;
	} catch {
		// Empty or broken local database; fall back to the site's Local domain.
	}
	const liveUrl = remote( `sudo -u ${ SITE_USER } wp --path='${ SITE_ROOT }' option get home` );
	if ( new URL( localUrl ).host === new URL( liveUrl ).host ) {
		// A previous pull stopped before rewriting URLs.
		localUrl = local.url;
	}
	console.log( `Pulling ${ liveUrl } into ${ localUrl } ...` );

	const stamp = new Date().toLocaleString( 'sv' ).replace( /[: ]/g, '-' ); // 2026-11-02-12-31-41, local time
	const backupDir = join( localSiteDir, 'pull-backups', stamp );
	mkdirSync( backupDir, { recursive: true } );

	console.log( 'Backing up the local database ...' );
	await runToFile( local.mysqldump, [ ...mysqlArgs, '--single-transaction', db.name ], join( backupDir, 'local.sql' ), mysqlEnv );

	console.log( 'Downloading the live database ...' );
	const liveSql = join( backupDir, 'live.sql' );
	await runToFile( 'ssh', [ HOST, `sudo -u ${ SITE_USER } wp --path='${ SITE_ROOT }' db export - --quiet` ], liveSql );
	if ( ! readFileSync( liveSql, 'utf8' ).slice( -200 ).includes( '-- Dump completed' ) ) {
		throw new Error( 'The live database dump is incomplete; the local site is untouched.' );
	}

	// Download everything before changing anything locally, so a dropped connection leaves Local as it was.
	const liveDir = join( backupDir, 'live' );
	mkdirSync( liveDir );
	const remoteEntries = [ `themes/${ THEME }/acf-json`, ...( dbOnly ? [] : [ 'uploads', 'plugins' ] ) ];
	console.log( `Downloading ${ remoteEntries.join( ', ' ) } ...` );
	const ssh = spawn( 'ssh', [ HOST, `cd '${ SITE_ROOT }/wp-content' && tar -czf - ${ remoteEntries.join( ' ' ) }` ], { stdio: [ 'ignore', 'pipe', 'inherit' ] } );
	const sshDone = new Promise( ( resolve, reject ) => {
		ssh.on( 'error', reject );
		ssh.on( 'close', ( code ) => ( code === 0 ? resolve() : reject( new Error( `ssh exited with ${ code }` ) ) ) );
	} );
	// Relative paths with cwd: tar would read "C:\..." as a remote host.
	await run( 'tar', [ '-xzf', '-', '-C', 'live' ], { cwd: backupDir, stdio: [ 'pipe', 'inherit', 'inherit' ], onSpawn: ( tar ) => ssh.stdout.pipe( tar.stdin ) } );
	await sshDone;

	console.log( 'Importing the live database ...' );
	execFileSync( local.mysql, [ ...mysqlArgs, '-e', `DROP DATABASE IF EXISTS \`${ db.name }\`; CREATE DATABASE \`${ db.name }\`;` ], { env: mysqlEnv } );
	const sqlFd = openSync( liveSql, 'r' );
	try {
		await run( local.mysql, [ ...mysqlArgs, db.name ], { stdio: [ sqlFd, 'inherit', 'inherit' ], env: mysqlEnv } );
	} finally {
		closeSync( sqlFd );
	}

	// Plain URLs, JSON-escaped URLs, then any remaining bare host names.
	const liveHost = new URL( liveUrl ).host;
	const localHost = new URL( localUrl ).host;
	for ( const [ from, to ] of [
		[ liveUrl, localUrl ],
		[ liveUrl.replaceAll( '/', '\\/' ), localUrl.replaceAll( '/', '\\/' ) ],
		[ liveHost, localHost ],
	] ) {
		console.log( wp( 'search-replace', from, to, '--all-tables', '--precise', '--report-changed-only', '--format=count' ) + ` × ${ from } → ${ to }` );
	}

	if ( ! dbOnly ) {
		const wpContent = join( publicDir, 'wp-content' );
		for ( const folder of [ 'uploads', 'plugins' ] ) {
			if ( existsSync( join( wpContent, folder ) ) ) {
				renameSync( join( wpContent, folder ), join( backupDir, `local-${ folder }` ) );
			}
			renameSync( join( liveDir, folder ), join( wpContent, folder ) );
		}
		console.log( 'Replaced uploads and plugins.' );
	}

	const acfChanges = syncAcfJson( join( liveDir, 'themes', THEME, 'acf-json' ) );

	wp( 'cache', 'flush' );
	wp( 'rewrite', 'flush' );
	wp( 'transient', 'delete', '--all' );

	console.log( `\nDone. Local now matches ${ liveUrl }; log in with your live wp-admin account.` );
	console.log( `Replaced local files and both database dumps are in ${ backupDir }` );
	if ( acfChanges.length ) {
		console.log( `ACF field groups changed on live were copied into acf-json/ (commit them): ${ acfChanges.join( ', ' ) }` );
	}
	console.log( 'Payment gateways now carry the live credentials: switch them to test credentials before testing checkout.' );
}

main().catch( ( error ) => {
	console.error( error.message );
	process.exit( 1 );
} );
