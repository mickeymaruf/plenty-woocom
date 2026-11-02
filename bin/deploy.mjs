#!/usr/bin/env node
/**
 * Deploys the theme to the production server: packs the theme with tar, streams it over SSH,
 * unpacks it next to the live theme and swaps the two folders. The previous version is kept
 * as `.plenty-prev` for rollback (`npm run deploy -- --rollback`).
 *
 * Run through `npm run deploy`, which builds dist/app.css first. Needs `tar` and `ssh` on PATH
 * (both ship with Windows 10+ and Git Bash) and key-based SSH access to the server.
 *
 * Override the target with DEPLOY_HOST / DEPLOY_THEMES_DIR, e.g. for a staging site.
 */

import { spawn, execSync } from 'node:child_process';
import { existsSync, readFileSync, readdirSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const HOST = process.env.DEPLOY_HOST || 'root@64.176.26.129';
const THEMES_DIR = process.env.DEPLOY_THEMES_DIR || '/home/plenty/htdocs/64-176-26-129.sslip.io/wp-content/themes';
const SITE_USER = process.env.DEPLOY_SITE_USER || 'plenty';
const PHP_FPM = process.env.DEPLOY_PHP_FPM || 'php8.3-fpm';
const THEME = 'plenty';

// Top-level entries that are development-only and never deployed.
const SKIP = new Set( [ '.git', '.gitignore', 'node_modules', 'src', 'bin', 'package.json', 'package-lock.json', 'CLAUDE.md', '.env', '.DS_Store', 'Thumbs.db' ] );

const themeDir = join( dirname( fileURLToPath( import.meta.url ) ), '..' );
const rollback = process.argv.includes( '--rollback' );

function remoteScript() {
	const t = THEMES_DIR;
	if ( rollback ) {
		return `set -euo pipefail
cd '${ t }'
test -d .${ THEME }-prev || { echo 'No previous version to roll back to.' >&2; exit 1; }
rm -rf .${ THEME }-new && mv ${ THEME } .${ THEME }-new && mv .${ THEME }-prev ${ THEME } && mv .${ THEME }-new .${ THEME }-prev
systemctl reload ${ PHP_FPM }
echo 'Rolled back (the version just replaced is now .${ THEME }-prev).'`;
	}
	return `set -euo pipefail
cd '${ t }'
rm -rf .${ THEME }-new && mkdir .${ THEME }-new
tar -xzf - -C .${ THEME }-new
test -f .${ THEME }-new/style.css && test -f .${ THEME }-new/dist/app.css || { echo 'Upload incomplete, live theme untouched.' >&2; exit 1; }
chown -R ${ SITE_USER }:${ SITE_USER } .${ THEME }-new
find .${ THEME }-new -type d -exec chmod 755 {} + && find .${ THEME }-new -type f -exec chmod 644 {} +
rm -rf .${ THEME }-prev && mv ${ THEME } .${ THEME }-prev && mv .${ THEME }-new ${ THEME }
systemctl reload ${ PHP_FPM }
echo "Deployed $(find ${ THEME } -type f | wc -l) files."`;
}

/**
 * ACF field groups saved in the live wp-admin are written to the live theme's acf-json/, which a
 * deploy would overwrite. Returns the live files that are missing locally or have a newer "modified".
 */
function liveAcfChanges() {
	const output = execSync( `ssh ${ HOST } "cd '${ THEMES_DIR }/${ THEME }/acf-json' 2>/dev/null && grep -H '\\"modified\\"' *.json || true"`, { encoding: 'utf8' } );
	return output.split( '\n' ).flatMap( ( line ) => {
		const match = line.match( /^([\w.-]+\.json):\s*"modified":\s*(\d+)/ );
		if ( ! match ) {
			return [];
		}
		const local = join( themeDir, 'acf-json', match[ 1 ] );
		const localModified = existsSync( local ) ? JSON.parse( readFileSync( local, 'utf8' ) ).modified || 0 : -1;
		return Number( match[ 2 ] ) > localModified ? [ match[ 1 ] ] : [];
	} );
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

async function main() {
	if ( rollback ) {
		await run( 'ssh', [ HOST, remoteScript() ], { stdio: 'inherit' } );
		return;
	}

	if ( ! existsSync( join( themeDir, 'dist', 'app.css' ) ) ) {
		throw new Error( 'dist/app.css is missing, run `npm run build` first.' );
	}

	try {
		const dirty = execSync( 'git status --porcelain', { cwd: themeDir, encoding: 'utf8' } ).trim();
		if ( dirty ) {
			console.warn( 'Warning: deploying uncommitted changes:\n' + dirty + '\n' );
		}
	} catch {
		// Not a git checkout; nothing to warn about.
	}

	const acfChanges = liveAcfChanges();
	if ( acfChanges.length && ! process.argv.includes( '--force' ) ) {
		throw new Error( `ACF field groups were changed on live and would be overwritten: ${ acfChanges.join( ', ' ) }\nRun \`npm run pull\` to bring them into acf-json/, or deploy with \`npm run deploy -- --force\` to discard them.` );
	}

	const entries = readdirSync( themeDir ).filter( ( name ) => ! SKIP.has( name ) );
	console.log( `Deploying ${ THEME } to ${ HOST }:${ THEMES_DIR } ...` );

	const ssh = spawn( 'ssh', [ HOST, remoteScript() ], { stdio: [ 'pipe', 'inherit', 'inherit' ] } );
	const sshDone = new Promise( ( resolve, reject ) => {
		ssh.on( 'error', reject );
		ssh.on( 'close', ( code ) => ( code === 0 ? resolve() : reject( new Error( `ssh exited with ${ code }` ) ) ) );
	} );

	await run( 'tar', [ '-czf', '-', '--exclude=.DS_Store', '--exclude=Thumbs.db', ...entries ], {
		cwd: themeDir,
		stdio: [ 'ignore', 'pipe', 'inherit' ],
		onSpawn: ( tar ) => tar.stdout.pipe( ssh.stdin ),
	} );
	await sshDone;

	console.log( 'If acf-json/ changed, open WP Admin → ACF → Field Groups and click "Sync available".' );
}

main().catch( ( error ) => {
	console.error( error.message );
	process.exit( 1 );
} );
