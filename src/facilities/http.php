<?php



use \Solenoid\X\GlobalStore;
use \Solenoid\X\HTTP\Route;
use \Solenoid\X\HTTP\Request;
use \Solenoid\X\HTTP\Response;
use \Solenoid\X\URL;

use \Solenoid\X\HTTP\Cookie;
use \Solenoid\X\HTTP\Session;

use \Solenoid\X\FileSystem\Directory;

use \Solenoid\X\Security\RateLimiter;

use \App\Services\RequestToken;



function route () : Route|false
{
    // Returning the value
    return GlobalStore::get( 'route' );
}



function request () : Request
{
    // Returning the value
    return container()->make( 'request' );
}

function response () : Response
{
    // Returning the value
    return container()->make( 'response' );
}



function url () : URL
{
    // Returning the value
    return GlobalStore::get( 'url' );
}

function cookie (string $name) : Cookie|null
{
    // (Getting the value)
    $cookie_map = container()->make( 'cookie_map' );

    if ( !isset( $cookie_map[ $name ] ) ) return null;



    // Returning the value
    return new Cookie( ...$cookie_map[ $name ] );
}

function session () : Session
{
    // Returning the value
    return \App\Session::fetch();
}

function request_token () : RequestToken
{
    // (Getting the value)
    $path = str_replace( '/', '\\', 'RequestToken' );



    // Returning the value
    return "\\App\\Services\\{$path}"::fetch();
}



function spa_routes () : array
{
    // (Setting the value)
    $routes = [];

    foreach ( ( new Directory( app()->basedir . '/spa/src/routes' ) )->list() as $path )
    {// Processing each entry
        if ( str_ends_with( $path, '/' ) ) continue;



        if ( basename( $path ) === '+page.svelte' )
        {// Processing the entry
            // (Getting the value)
            $route = preg_replace( '/^\/\(app\)/', '', dirname( $path ) );

            // (Appending the value)
            $routes[] = $route === '' ? '/' : $route;
        }
    }



    // Returning the value
    return $routes;
}



function ip () : string
{
    // Returning the value
    return in_prod() ? request()->get_header( 'X-Forwarded-For' ) : $_SERVER['REMOTE_ADDR'];
}

function ua () : string
{
    // Returning the value
    return $_SERVER['HTTP_USER_AGENT'];
}



function request_rate_limit (string $key, int $max_rate = 50, int $time_limit = 60) : void
{
    // (Getting the value)
    $redis_client = redis_client();



    // (Getting the value)
    $rate_limiter = new RateLimiter( $redis_client, $max_rate, $time_limit, '' );

    if ( !$rate_limiter->pass( $key ) )
    {// (Limit exceeded)
        // (Logging the message)
        push_log( "Key '$key' exceeded the rate limit of $max_rate requests per $time_limit seconds", file_path: '/app/http/rate_limiter.log' );

        // Throwing the exception
        throw error( 6401, 'Too Many Requests' );
    }



    // (Closing the connection)
    $redis_client->disconnect();
}



function cookie_value (string $name) : string|null
{
    // Returning the value
    return $_COOKIE[ $name ] ?? null;
}



?>