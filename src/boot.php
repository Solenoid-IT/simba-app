<?php



// (Including the files)
include_once( __DIR__ . '/autoload.php' );
include_once( __DIR__ . '/facilities/base.php' );



use \Solenoid\X\GlobalStore;

use \Solenoid\X\App;
use \Solenoid\X\Container;
use \Solenoid\X\Storage;
use \Solenoid\X\Env;
use \Solenoid\X\View;
use \Solenoid\X\CLI\Command;
use \Solenoid\X\HTTP\Route;
use \Solenoid\X\HTTP\Request;
use \Solenoid\X\HTTP\Response;
use \Solenoid\X\URL;
use \Solenoid\X\Store;
use \Solenoid\X\Dispatcher;
use \Solenoid\X\Error;
use \Solenoid\X\CSV\Reader;
use \Solenoid\X\CSV\CSV;
use \Solenoid\X\Logger;

use \Solenoid\MySQL\ConnectionMap;
use \Solenoid\MySQL\Connection;
use \Solenoid\MySQL\Model;

use \App\Gate;



// (Setting the property)
error_reporting( E_ERROR );



// (Creating an App)
$app = new App( __DIR__ );

// (Setting the value)
GlobalStore::set( 'app', $app );



// (Creating a Container)
$container = new Container();

// (Setting the container)
$app->set_container( $container );



// (Creating a Storage)
$storage = new Storage( $app->basedir . '/storage' );

// (Setting the value)
GlobalStore::set( 'storage', $storage );



// (Creating an Env)
$env = new Env( $app->basedir . '/.env' );

// (Setting the value)
GlobalStore::set( 'env', $env );



// (Creating a View)
$view = new View( $app->basedir . '/views' );

// (Setting the value)
GlobalStore::set( 'view', $view );



if ( $app->mode === 'http' )
{// Match OK
    function summarize_request (int $status_code) : string
    {
        // (Getting the value)
        $headers = getallheaders();



        // Returning the value
        return ( isset( $headers['X-Forwarded-For'] ) ? $headers['X-Forwarded-For'] . ' via ' . $_SERVER['REMOTE_ADDR'] : $_SERVER['REMOTE_ADDR'] ) . " -- \"{$_SERVER['REQUEST_METHOD']} {$_SERVER['REQUEST_URI']}\" $status_code {$headers['Content-Length']} \"{$headers['Origin']}\" \"{$_SERVER['HTTP_USER_AGENT']}\"";
    }



    // (Setting the value)
    $cookie_map = [];

    foreach ( require_once( __DIR__ . '/config/cookies.php' ) as $cookie )
    {// Processing each entry
        // (Getting the value)
        $cookie_map[ $cookie['name'] ] = $cookie;
    }



    // (Binding the class)
    $container->singleton( 'cookie_map', function () use ($cookie_map) { return $cookie_map; } );
}



// (Registering errors)
$app->register_errors( ( new Reader( __DIR__ . '/config/errors.csv', new CSV( '|' ) ) )->fetch( true ) );



// (Getting the value)
$connection_map = new ConnectionMap();

foreach ( require_once( __DIR__ . '/config/connections/mysql.php' ) as $conn_name => $conn_data )
{// Processing each entry
    // (Getting the value)
    $connection = new Connection( $conn_data['host'], $conn_data['port'], $conn_data['user'], $conn_data['pass'] );



    // (Listening for the event)
    $connection->on('command', function ($data) use ($conn_name) {
        // (Writing to the storage)
        storage()->write( "/commands.$conn_name.sql", $data['command'] . "\n\n", 'a' );
    });



    // (Setting the element)
    $connection_map->set( $conn_name, $connection );
}



// (Binding the class)
$container->singleton( 'connection_map', function () use ( $connection_map ) { return $connection_map; } );



// (Setting the map)
Model::set_map( $connection_map );



// (Binding the class)
$container->singleton( 'store', function () { return new Store(); } );



// (Setting the value)
$loggers = [];

foreach ( [ 'cli', 'http' ] as $app_mode )
{// Processing each entry
    foreach ( [ 'error', 'activity' ] as $log_type )
    {// Processing each entry
        // (Getting the value)
        $logger = new Logger( storage() . "/logs/app/$app_mode/$log_type.log", pid: true );



        // (Rotating the logger)
        $logger->rotate();



        // (Getting the value)
        $loggers[ $app_mode ][ $log_type ] = $logger;
    }
}



try
{
    switch ( $app->mode )
    {
        case 'cli':
            // (Including the file)
            include_once( __DIR__ . '/facilities/cli.php' );



            // (Getting the value)
            $command = new Command( $argv );



            // (Setting the handlers)
            $command->set_handlers
            (
                [
                    'mutex-pid'    => fn() => task_pid( $command->task ),
                    'mutex-lock'   => fn() => mutex_lock( $command->task, getmypid() ),
                    'mutex-unlock' => fn() => mutex_unlock( $command->task )
                ]
            )
            ;



            // (Binding the class)
            $container->singleton( 'command', function () use ( $command ) { return $command; } );



            // (Getting the value)
            $command = $container->make( 'command' );



            if ( ( new Gate( $app ) )->traverse() )
            {// (Gate has been traversed)
                // (Running the command)
                $result = $command->run( $container );

                if ( $result !== null )
                {// Value found
                    // (Printing the value)
                    echo json_encode( $result ) . "\n";
                }
            }



            // (Logging the message)
            $loggers[ $app->mode ][ 'activity' ]->push( $command );
        break;

        case 'http':
            // (Including the file)
            include_once( __DIR__ . '/facilities/http.php' );



            // (Including the file)
            include_once( __DIR__ . '/routes.php' );



            // (Getting the value)
            $route = Route::match( $_SERVER['REQUEST_METHOD'], explode( '?', $_SERVER['REQUEST_URI'], 2 )[0] );

            // (Setting the value)
            GlobalStore::set( 'route', $route );



            // (Binding the class)
            $container->singleton( 'request', function () { return Request::fetch(); } );



            // (Getting the value)
            $request = $container->make( 'request' );



            // (Getting the values)
            [ $path, $query ] = explode( '?', $_SERVER['REQUEST_URI'], 2 );



            // (Getting the value)
            $url = new URL
            (
                $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ( isset( $_SERVER['HTTPS'] ) && strtolower( $_SERVER['HTTPS'] ) === 'on' )  ? 'https' : 'http',
                $_SERVER['PHP_AUTH_USER'],
                $_SERVER['PHP_AUTH_PW'],
                $_SERVER['SERVER_NAME'],
                $_SERVER['SERVER_PORT'],
                $path,
                $query ?? ''
            )
            ;

            // (Setting the value)
            GlobalStore::set( 'url', $url );



            // (Binding the class)
            $container->singleton( 'response', function () { return new Response(); } );



            // (Getting the value)
            $response = $container->make( 'response' );



            // (Setting the app)
            $response->set_app( $app );



            if ( ( new Gate( $app ) )->traverse() )
            {// (Gate has been traversed)
                if ( $route )
                {// Value found
                    // (Getting the value)
                    $result = ( new Dispatcher( $container ) )->dispatch
                    (
                        $request,
                        
                        $route->get_middlewares(),
                        
                        function ($request) use ($route, $container, $response)
                        {
                            // (Setting the value)
                            $result = null;

                            if ( $route->target )
                            {// Value found
                                if ( isset( $route->target->function ) )
                                {// (Target is a function)
                                    // (Getting the value)
                                    $result = $container->run_function( $route->target->function, $route->params );
                                }
                                else
                                if ( isset( $route->target->class ) && isset( $route->target->fn ) )
                                {// (Target is a class method)
                                    if ( method_exists( $route->target->class, $route->target->fn ) )
                                    {// Match OK
                                        // (Getting the value)
                                        $result = $container->run_class_fn( $route->target->class, $route->target->fn, $route->params );
                                    }
                                    else
                                    {// Match failed
                                        // (Setting the response)
                                        $response->text( 404, 'ROUTE NOT FOUND' );
                                    }
                                }
                            }



                            // Returning the value
                            return $result;
                        }
                    )
                    ;



                    // (Getting the value)
                    $app_error = $app->get_error();

                    if ( $app_error === null )
                    {// (There is not an error)
                        if ( $result !== null )
                        {// Value found
                            if ( $result !== $response )
                            {// Match OK
                                if ( $result instanceof \Solenoid\X\Error )
                                {// Match OK
                                    // (Setting the response)
                                    $response->error( $result->getCode(), $result->getMessage() );
                                }
                                else
                                {// Match failed
                                    /* ahcid to deleted

                                    if ( $result instanceof \Stringable )
                                    {// Match OK
                                        // (Getting the value)
                                        $result = (string) $result;
                                    }

                                    */



                                    // (Setting the response)
                                    $response->json( 200, $result );
                                }
                            }
                        }
                    }
                    else
                    {// (There is an error)
                        // (Setting the response)
                        $response->error( $app_error->getCode(), $app_error->getMessage() );
                    }
                }
                else
                {// Value not found
                    // (Setting the response)
                    $response->text( 404, 'ROUTE NOT FOUND' );
                }
            }



            // (Sending the response)
            $response->send();



            // (Logging the message)
            $loggers[ $app->mode ][ 'activity' ]->push( summarize_request( http_response_code() ) );
        break;
    }
}
catch (Error $error)
{
    // (Logging the message)
    $loggers[ $app->mode ][ 'error' ]->push( error_format( $error, ' - ', true ) );



    if ( $app->mode === 'http' )
    {// Match OK
        // (Setting the code)
        http_response_code( $error->get_http_code() );

        if ( $error->is_exposed() )
        {// Value is true
            // (Setting the header)
            header( 'Content-Type: application/json' );

            // Printing the value
            echo json_encode( $error->get_info() );
        }



        // (Logging the message)
        $loggers[ $app->mode ][ 'activity' ]->push( summarize_request( $error->get_http_code() ?? 500 ) );
    }
    else
    {// Match failed
        // (Logging the message)
        $loggers[ $app->mode ][ 'activity' ]->push( command() . ' :: ' . $error );
    }



    if ( $error->is_notifiable() )
    {// Value is true
        if ( env( 'TELEGRAM_BOT_ENABLED' ) === '1' )
        {// Value is true
            // (Sending the message)
            telegram_send( error_format( $error ) );
        }
    }



    if ( $app->mode === 'cli' )
    {// Match OK
        // Throwing the exception
        throw $error;
    }
}
catch (Throwable $exception)
{
    // (Logging the message)
    $loggers[ $app->mode ][ 'error' ]->push( $exception );



    if ( $app->mode === 'http' )
    {// Match OK
        // (Logging the message)
        $loggers[ $app->mode ][ 'activity' ]->push( summarize_request( 500 ) );
    }
    else
    {// Match failed
        // (Logging the message)
        $loggers[ $app->mode ][ 'activity' ]->push( command() . ' :: ' . $exception );
    }



    // Throwing the exception
    throw $exception;
}



?>