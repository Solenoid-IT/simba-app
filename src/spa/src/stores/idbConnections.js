import * as IDB from '../modules/IDB.js';



import { writable } from 'svelte/store';

export const idbConnections = writable
(
    {
        'request': new IDB.Connection( 'db', 'request' )
    }
)
;