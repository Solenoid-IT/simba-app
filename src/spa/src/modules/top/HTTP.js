import * as HTTP from '@/modules/HTTP.js';
import * as IDB from '@/modules/IDB.js';
import * as FlexObject from '@/modules/FlexObject.js';

import { envs } from '@/envs.js';



export class Response
{
    constructor( code, headers, body )
    {
        // (Getting the values)
        this.code    = code;
        this.headers = headers;
        this.body    = body;
    }
}



// Returns [Promise:Response|false]
export const sendRequest = async function (route, input, offlineMode = false, requestToken, onDownloadProgress)
{
    // (Getting the value)
    const targetUrl = `${ route }`;



    // (Setting the value)
    const supportedTypes =
    {
        'undefined': 'text/plain',
        'string':    'text/plain',
        'number':    'application/json',
        'object':    'application/json'
    }
    ;



    // (Getting the value)
    const contentType = supportedTypes[ typeof input ];

    if ( !contentType )
    {// Match failed
        // Throwing an error
        throw new Error( `Unsupported input type '${ typeof input }'` );
    }



    // (Getting the value)
    const request = new HTTP.Request
    (
        'RUN',
        {
            'Content-Type': contentType
        },
        contentType === 'application/json' ? JSON.stringify( typeof input === 'object' ? FlexObject.expand( input ) : input ) : input
    )
    ;

    if ( requestToken )
    {// Value is true
        // (Getting the value)
        request.headers['Request-Token'] = requestToken;
    }



    if ( offlineMode )
    {// Value is true
        if ( !navigator.onLine )
        {// (Client is offline)
            if ( confirm( "You are offline now\nDo you want to enqueue the request to process it later ?" ) )
            {// (Confirmation is ok)
                // (Getting the value)
                const id = new Date().valueOf();

                // (Inserting the record)
                await ( new IDB.Connection( envs.IDB_DATABASE, 'request' ) ).push
                (
                    {
                        'id':      id,

                        'method':  request.method,
                        'headers': request.headers,
                        'body':    request.body,
    
                        'url':     targetUrl
                    },
                    id
                )
                ;
    
    
    
                // (Alerting the value)
                alert( 'Request has been enqueued into local memory' );
    
                // Returning the value
                return false;
            }
        }
    }



    // (Sending the message)
    window.postMessage
    (
        {
            'type': 'request-start',
            'data':
            {
                'method':  request.method,
                'headers': request.headers,
                'body':    request.body,
                'url':     targetUrl
            }
        },

        window.location.origin
    )
    ;



    // (Sending the request)
    const response = await request.send( targetUrl );



    // (Setting the value)
    let body = null;

    switch ( response.headers.get( 'content-type' ).split( ';' )[0] )
    {
        case 'text/plain' :
            // (Getting the value)
            body = await response.fetchBody( 'text' );
        break;

        case 'application/json' :
            // (Getting the value)
            body = await response.fetchBody( 'json' );
        break;

        default:
            // (Getting the value)
            body = await response.fetchBody( 'arrayBuffer' );
    }



    // (Getting the value)
    //const bodyText = await response.fetchBody( 'text' );// ahcid improve for ArrayBuffer option



    // (Sending the message)
    window.postMessage
    (
        {
            'type': 'request-end',
            'data':
            {
                'method':  request.method,
                'headers': request.headers,
                'body':    request.body,
                'url':     targetUrl
            }
        },

        window.location.origin
    )
    ;



    // (Getting the value)
    //const body = bodyText.length === 0 ? null : ( response.headers.get( 'content-type' ) === 'application/json' ? JSON.parse( bodyText ) : bodyText );

    if ( response.code !== 200 )
    {// (Request failed)
        if ( body )
        {// Value found
            if ( body['error'] )
            {// Value found
                // (Alerting the value)
                alert( body['error']['message'] );
            }
            else
            {// Value not found
                // (Alerting the value)
                alert( `Error ${ body['code'] }\n\n${ body['name'] }\n\n${ body['description'] }` );
            }
        }
    }



    // Returning the value
    return new Response( response.code, response.headers, body );
}