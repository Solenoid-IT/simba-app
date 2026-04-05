// (Getting the value)
const dotenv = require( 'dotenv' );



// (Getting the value)
const fs = require( 'fs' );



// Returns [Object|false]
function loadEnvs (filePath)
{
    if ( !fs.existsSync( filePath ) )
    {// (File not found)
        // Returning the value
        return false;
    }



    try
    {
        // (Getting the value)
        const object = dotenv.config( { 'path': filePath, 'encoding': 'utf8', 'debug': false, 'quiet': true } ).parsed;

        if ( !object )
        {// (Unable to parse file content)
            // Returning the value
            return false;
        }



        // Returning the value
        return object;

    }
    catch (error)
    {
        // Returning the value
        return false;
    }
}



// (Getting the value)
const ENV = loadEnvs( __dirname + '/../../.env' );



// (Setting the value)
const SERVER_PORT = 3001;



// (Getting the value)
const WebSocket = require( 'ws' );



// (Getting the value)
const axios = require( 'axios' );



// (Getting the value)
const server = new WebSocket.Server( { 'port': SERVER_PORT } );



// (Logging the message)
console.log( `Listening on port ${ SERVER_PORT } ...` );



// (Getting the value)
const clients = new Set();



// (Setting the value)
const users = {};



// Returns [void]
function log (message)
{
    // Returning the value
    return fs.writeFileSync( __dirname + '/activity.log', `[ ${ new Date().toISOString() } ] :: ( ${ process.pid } ) :: ${ message }\n`, { 'flag': 'a' } );
}



// Returns [Object]
function parseCookieHeader (value)
{
    // (Setting the value)
    const cookies = {};

    for ( const cookie of value.split( '; ' ) )
    {// Processing each entry
        // (Getting the values)
        const [ name, value ] = cookie.split( '=' );

        // (Getting the value)
        cookies[ name ] = value;
    }



    // Returning the value
    return cookies;
}



// Returns [Array<Object>]
function getTargetClients (target)
{
    // Returning the value
    return Object.values( users[ target.tenant ] ).filter
    (
        function (client)
        {
            if ( target.hierarchies.length === 0 && target.users.length === 0 ) return true;



            // (Getting the value)
            const match =
                ( target.hierarchies.includes( client.hierarchyId ) )
                    ||
                ( target.users.includes( client.userId ) )
            ;



            // Returning the value
            return match;
        }
    )
    ;
}



// Returns [void]
function send (message, clients)
{
    // (Iterating each entry)
    clients.forEach
    (
        function (client)
        {
            if ( client.ws.readyState === WebSocket.OPEN )
            {// (Client is reachable)
                // (Sending the message)
                client.ws.send( message );
            }
        }
    )
    ;
}



// (Listening for the event)
server.on('connection', async function (ws, request) {
    // (Setting the value)
    ws.isAlive = true;



    // (Getting the value)
    const ip = request.headers['x-forwarded-for'];



    // (Getting the value)
    const client = ip ?? 'BACKEND';



    // (Logging the message)
    log( `Client ${ client } connected` );



    if ( client === 'BACKEND' )
    {// (Client is the backend)
        // (Adding the value)
        clients.add( ws );



        // (Listening for the event)
        ws.on('message', function (messageData) {
            // (Getting the value)
            const msg = messageData.toString();



            // (Logging the message)
            log( `Received from client :: ${ msg }` );



            // (Getting the value)
            const message = JSON.parse( msg );



            // (Sending the message)
            send( JSON.stringify( message.event ), getTargetClients( message.target ) );
        });
    }
    else
    {// (Client is the frontend)
        // (Getting the value)
        const cookies = parseCookieHeader( request.headers.cookie );



        // (Sending the request)
        const response = await axios
        (
            {
                'method':  'RUN',
                'url':     `http://127.0.0.1/api/micro/alert?m=User.find`,
                'headers':
                {
                    'Session-Id': cookies['session']
                },
                'data':    ''
            }
        )
        ;



        // (Getting the value)
        const user = response.data;

        if ( user === false )
        {// (User not found)
            // Returning the value
            return;
        }



        if ( !users[ user.tenant ] )
        {// Value not found
            // (Setting the value)
            users[ user.tenant ] = {};
        }



        // (Getting the value)
        users[ user.tenant ][ user.id ] =
        {
            'tenantId':    user.tenant,
            'userId':      user.id,
            'hierarchyId': user.hierarchy,
            'ws':          ws
        }
        ;
    }



    // (Listening for the event)
    ws.on('close', function () {
        // (Logging the message)
        log( `Client ${ client } disconnected` );



        // (Deleting the value)
        clients.delete( ws );
    });



    // (Listening for the event)
    ws.on('pong', function () {
        // (Setting the value)
        ws.isAlive = true;
    });
});



// (Setting the interval)
const interval = setInterval
(
    function ()
    {
        // (Iterating each entry)
        clients.forEach
        (
            function (client)
            {
                if ( client.ws.isAlive === false )
                {// (Client is not alive)
                    // Returning the value
                    return client.ws.terminate();
                }



                // (Setting the value)
                client.ws.isAlive = false;



                // (Sending the ping)
                client.ws.ping();
            }
        )
        ;
    },
    
    30000
)
;



// (Listening for the event)
server.on('close', function () {
    // (Clearing the interval)
    clearInterval( interval );
});