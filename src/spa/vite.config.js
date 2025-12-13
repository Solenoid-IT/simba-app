import { sveltekit } from '@sveltejs/kit/vite';
import { defineConfig } from 'vite';

import fs from 'fs';
import * as dotenv from 'dotenv';



// (Getting the value)
const APP_BASEDIR = `${ __dirname }/..`;



// (Loading ENV)
dotenv.config( { 'path': `${ APP_BASEDIR }/.env`, 'quiet': true } );



export default defineConfig({
	'plugins': [ 
		sveltekit()
	],
	'server':
	{
		'host': '0.0.0.0',
		'port': 5173,

		'https':
		{
			'key':  fs.readFileSync( `${ APP_BASEDIR }/storage/cert/simba.key` ),
			'cert': fs.readFileSync( `${ APP_BASEDIR }/storage/cert/simba.crt` )
		},

		'cors': true,
		'headers': {
			'Access-Control-Allow-Origin': '*'
		},

		'proxy': {
			'/api': {
				'target':       'https://127.0.0.1',
				'changeOrigin': true,
				'secure':       false,
				'xfwd':         true,
				'configure': (proxy, _options) => {
					proxy.on('error', (err, _req, _res) => {
						console.log('Proxy Error ::', err);
					});
					proxy.on('proxyReq', (proxyReq, req, _res) => {
						// (Logging the message)
						console.log( "Proxying '/api' :: ", req.method, req.url );



						if ( req.headers['x-http-method-override'] === 'RUN' )
						{// Match OK
							// (Setting the value)
							proxyReq.method = 'RUN';

							// (Removing the headers)
							proxyReq.removeHeader( 'x-http-method-override' );
						}
					});
				},
			},

			'/assets': {
				'target':       'https://127.0.0.1',
				'changeOrigin': true,
				'secure':       false,
				'configure': (proxy, _options) => {
					proxy.on('error', (err, _req, _res) => {
						console.log('Proxy Error ::', err);
					});
					proxy.on('proxyReq', (proxyReq, req, _res) => {
						//console.log( "Proxying '/assets' :: ", req.method, req.url );
					});
				},
			},

			'/micro': {
				'target':       'http://127.0.0.1:3001',
				'changeOrigin': true,
				'secure':       false,
				'ws':		    true,
				'configure': (proxy, _options) => {
					proxy.on('error', (err, _req, _res) => {
						console.log('Proxy Error ::', err);
					});
					proxy.on('proxyReq', (proxyReq, req, _res) => {
						console.log( "Proxying '/micro' :: ", req.method, req.url );
					});
				},
			}
		}
	},

	'resolve':
	{
		'alias':
		{
			'@': `${ __dirname }/src`
		}
	},

	'base': '/',

	'build':
	{
		'emptyOutDir': true
	},

	'define':
	{
		'import.meta.env.VITE_APP_NAME': JSON.stringify( process.env.APP_NAME ),
	}
});



/*

// DEFAULT CONFIG

export default
	defineConfig
	(
		function (cmd, mode)
		{
			// (Getting the values)
			//const env = loadEnv( mode, process.cwd(), '');



			// (Getting the value)
			const object =
			{
				'plugins': [ sveltekit() ],
				//'define': { ...Object.keys( env ).reduce( function (prev, key) { prev[`process.env.${ key }`] = JSON.stringify( env[key] ); return prev; }, {} ) }
			}
			;



			// Returning the value
			return object;
		}
	)
;

*/