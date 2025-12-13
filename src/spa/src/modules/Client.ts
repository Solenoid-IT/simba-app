// © Solenoid-IT



import { Endpoint, Response } from '@/modules/sRPC.ts';
import { Entity } from '@/modules/Entity.ts';



const isProd = !import.meta.env.DEV;



export class Client
{
    public endpoint : Endpoint;



    constructor (endpoint_path? : string)
    {
        // (Getting the value)
        const endpoint = new Endpoint( endpoint_path ?? '/api/user' ).setNative( isProd );



        // (Listening for the event)
        endpoint.addEventListener('request', function (data : any) {
            // (Sending the message)
            window.postMessage
            (
                {
                    'type': 'request-start',
                    'data': data
                },

                window.location.origin
            )
            ;
        });

        // (Listening for the event)
        endpoint.addEventListener('response', function (data : any) {
            // (Sending the message)
            window.postMessage
            (
                {
                    'type': 'request-end',
                    'data': data
                },

                window.location.origin
            )
            ;
        });



        // (Getting the value)
        this.endpoint = endpoint;
    }



    public async run (action : string, input? : any, headers? : Headers) : Promise<Response>
    {
        // (Getting the value)
        const response = await this.endpoint.run( action, input, headers );

        if ( response.code !== 200 )
        {// (Request failed)
            if ( response.body )
            {// Value found
                if ( typeof response.body === 'string' )
                {// Match OK
                    // (Alerting the value)
                    alert( response.body );
                }
                else
                {// Match failed
                    if ( response.body['error'] )
                    {// Value found
                        // (Alerting the value)
                        alert( response.body['error']['message'] );
                    }
                    else
                    {// Value not found
                        const errorValues = Object.values( response.body );

                        // (Alerting the value)
                        alert( `Error ${ errorValues.join( "\n\n" ) }` );
                    }
                }
            }
        }



        // Returning the value
        return response;
    }



    public entity (name : string) : Entity
    {
        // Returning the value
        return new Entity( name, this );
    }
}