import adapter from '@sveltejs/adapter-static';
import preprocess from 'svelte-preprocess';



import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath( import.meta.url );
const __dirname  = path.dirname( __filename );



import fs from 'fs';



// (Getting the value)
const simbaConfig = JSON.parse( fs.readFileSync( `${ __dirname }/config.json` ) );



/** @type {import('@sveltejs/kit').Config} */
const config = {
	'preprocess': preprocess(),
	'kit':
	{
		// adapter-auto only supports some environments, see https://kit.svelte.dev/docs/adapter-auto for a list.
		// If your environment is not supported or you settled on a specific environment, switch out the adapter.
		// See https://kit.svelte.dev/docs/adapters for more information about adapters.
		'adapter': adapter({
			'pages':       simbaConfig['build_path'],
			'assets':      simbaConfig['build_path'],

			'fallback':    'index.html',
			'precompress': false,
			'strict':      false
		}),



		'paths':
		{
			'base': '',
			'assets': '',
			'relative': true
		},



		'appDir': '_svelte',

		'prerender':
		{
			'handleHttpError': 'warn',

			//'entries': ['*'],
			//'origin': 'https://test.example.com'
		},

		'env':
		{
			'dir': '.',
			'publicPrefix': 'P_'
		}
	}
};

export default config;