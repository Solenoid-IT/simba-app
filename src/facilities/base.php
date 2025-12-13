<?php



use \Solenoid\X\GlobalStore;
use \Solenoid\X\App;
use \Solenoid\X\Container;
use \Solenoid\X\Storage;
use \Solenoid\X\View;
use \Solenoid\X\Stream\ReadableStream;
use \Solenoid\X\HTTP\Request;
use \Solenoid\X\Error;

use \Solenoid\X\HTTP\Client\Sender;
use \Solenoid\X\HTTP\Client\Result;

use \Solenoid\X\HTTP\Response;

use \Solenoid\MySQL\Connection;
use \Solenoid\MySQL\Model;

use \Solenoid\X\Process;

use \Solenoid\X\TempFile;

use \Solenoid\X\Time\Time;

use \Solenoid\X\Store;

use \Solenoid\sRPC\Action;

use \WebSocket\Client as WebSocketClient;

use \Predis\Client as RedisClient;

use \Solenoid\X\Assoc;
use \Solenoid\X\Collection;
use \Solenoid\X\Data\DTO;
use \Solenoid\X\Data\WriteFieldset;

use \Solenoid\X\Logger;

use \Solenoid\X\CLI\Task;

use \Solenoid\MySQL\Record;

use \App\Entity;
use \App\Element;

use \App\User;

use \App\Models\User as UserModel;



function app () : App
{
    // Returning the value
    return GlobalStore::get( 'app' );
}

function container () : Container
{
    // Returning the value
    return app()->get_container();
}

function storage () : Storage
{
    // Returning the value
    return GlobalStore::get( 'storage' );
}

function view () : View
{
    // Returning the value
    return GlobalStore::get( 'view' );
}

function env (string $key, mixed $default = null) : string|null
{
    // Returning the value
    return GlobalStore::get( 'env' )->get( $key ) ?? $default;
}

function action () : Action
{
    // Returning the value
    return GlobalStore::get( 'action' );
}

function error (int $code, string $message = '') : Error
{
    // Returning the value
    return app()->spawn_error( $code, $message );
}

function error_find (int $code) : array|null
{
    // (Getting the value)
    $errors = app()->get_errors();

    if ( !isset( $errors[ $code ] ) )
    {// (Error code not found)
        // Returning the value
        return null;
    }



    // (Getting the value)
    $error = $errors[ $code ];



    // (Getting the value)
    $error['code']       = (int) $error['code'];
    $error['http_code']  = (int) $error['http_code'];
    $error['exposed']    = $error['exposed'] === '1';
    $error['notifiable'] = $error['notifiable'] === '1';



    // Returning the value
    return $error;
}



function mysql_connection (string $id) : Connection|null
{
    // Returning the value
    return container()->make( 'connection_map' )->get( $id );
}



function model (string $name) : Model
{
    if ( str_starts_with( $name, 'App\\Models\\' ) ) return "\\$name"::fetch();



    // (Getting the value)
    $path = str_replace( '/', '\\', $name );



    // Returning the value
    return "\\App\\Models\\{$path}"::fetch();
}

function model_keys (string|Model $model) : array
{
    // Returning the value
    return Model::get_keys( $model );
}



function send_request (string $method, string $url, array $headers = [], string $body = '', int $conn_timeout = 60, int $exec_timeout = 60, int $max_redirs = 10) : Result|false
{
    // (Getting the value)
    $parsed = parse_url( $url );



    // (Appending the value)
    $headers[] = 'User-Agent: SimbaClient/1.0.0';



    // (Getting the value)
    return ( new Sender( $conn_timeout, $exec_timeout, $max_redirs ) )->send( new Request( $method, $parsed['path'] . ( $parsed['query'] ? '?' . $parsed['query'] : '' ), 'HTTP/1.1', $headers, $body ), $parsed['scheme'] . '://' . explode( '://', $url, 2 )[1] );
}



function run (Task|string $task, array $args = [], string $stdin = '') : Process|false
{
    // (Getting the value)
    $args = implode( ', ', array_map( function ($arg) { return escapeshellarg( $arg ); }, $args ) );



    if ( $task instanceof Task ) $task = $task->format();



    // Returning the value
    return ( new Process( "php boot.php $task $args" ) )->set_cwd( app()->basedir )->set_input( $stdin )->run();
}

function run_async (Task|string $task, array $args = [], string $stdin = '') : Process|false
{
    // (Getting the value)
    $args = implode( ', ', array_map( function ($arg) { return escapeshellarg( $arg ); }, $args ) );



    if ( $task instanceof Task ) $task = $task->format();



    // Returning the value
    return Process::spawn( "php boot.php $task $args", app()->basedir, $stdin );
}



function temp_file (?string $folder_path = null, string $prefix = 'tmp_', bool $auto_remove = true) : TempFile
{
    // (Getting the value)
    return new TempFile( $folder_path, $prefix, $auto_remove );
}



function time_conv (string $value, string $src_timezone, string $dst_timezone = 'UTC', string $format = ':mysql') : string
{
    // Returning the value
    return (string) ( new Time( $value, $src_timezone ) )->convert( $dst_timezone )->format( $format );
}



function store () : Store
{
    // Returning the value
    return container()->make( 'store' );
}



function alert_severities () : array
{
    // Returning the value
    return require_once( app()->basedir . '/config/alert_severities.php' );
}

function alert_severity (int $level) : array|false
{
    // Returning the value
    return require_once( app()->basedir . '/config/alert_severities.php' )[ $level - 1 ] ?? false;
}



function websocket_send (string $message) : bool
{
    try
    {
        // (Getting the value)
        $client = new WebSocketClient( 'ws://' . env( 'WS_HOST' ) . ':' . env( 'WS_PORT' ) );

        // (Sending the message)
        $client->send( $message );

        // (Closing the connection)
        $client->close();



        // Returning the value
        return true;
    }
    catch (\Exception $e)
    {
        // Returning the value
        return false;
    }
}



function redis_client () : RedisClient|null
{
    // Returning the value
    return new RedisClient
    (
        [
            'host' => env( 'REDIS_HOST' ),
            'port' => env( 'REDIS_PORT' )
        ]
    )
    ;
}

function redis_get (string $key) : string|null
{
    try
    {
        // (Getting the value)
        $connection = redis_client();
    }
    catch (\Exception $e)
    {
        // Returning the value
        return false;
    }



    // (Closing the connection)
    $connection->quit();



    // Returning the value
    return $connection->get( $key );
}

function redis_set (string $key, string $value, int $ttl = 3600) : bool
{
    try
    {
        // (Getting the value)
        $connection = redis_client();



        // (Setting the value with expiration)
        $connection->setex( $key, $ttl, $value );
    }
    catch (\Exception $e)
    {
        // Returning the value
        return false;
    }



    // Returning the value
    return true;
}

function redis_setex (string $key, int $ttl, string $value) : bool
{
    try
    {
        // (Getting the value)
        $connection = redis_client();



        // (Setting the value with expiration)
        $connection->setex( $key, $ttl, $value );

        // (Closing the connection)
        $connection->quit();
    }
    catch (\Exception $e)
    {
        // Returning the value
        return false;
    }



    // Returning the value
    return true;
}

function redis_del (string $key) : bool
{
    try
    {
        // (Getting the value)
        $connection = redis_client();



        // (Deleting the key)
        $result = $connection->del( $key );

        // (Closing the connection)
        $connection->quit();

        // Returning the value
        return $result > 0;
    }
    catch (\Exception $e)
    {
        // Returning the value
        return false;
    }
}

function redis_keys (string $pattern = '*') : array|false
{
    try
    {
        // (Getting the value)
        $connection = redis_client();



        // (Getting the keys)
        $result = $connection->keys( $pattern );

        // (Closing the connection)
        $connection->quit();

        // Returning the value
        return $result;
    }
    catch (\Exception $e)
    {
        // Returning the value
        return false;
    }
}



function class_basename (string $class) : string
{
    // Returning the value
    return basename( str_replace( '\\', '/', $class ) );
}



function assoc (array $value) : Assoc
{
    // Returning the value
    return new Assoc( $value );
}



function input () : ReadableStream
{
    // Returning the value
    return ( new ReadableStream( ReadableStream::TYPE_FILE ) )->set_file( 'php://' . ( app()->mode === 'http' ? 'input' : 'stdin' ) );
}



function slug (string $name) : string
{
    // Returning the value
    return strtolower( preg_replace( '/[^a-zA-Z0-9]+/', '_', trim( $name ) ) );
}



function collection ($object) : Collection
{
    // Returning the value
    return new Collection( $object );
}



function dto_fieldset (string|DTO $dto) : WriteFieldset|null
{
    // Returning the value
    return DTO::get_fieldset( $dto );
}



function user (?int $id = null) : User|false
{
    if ( !$id ) return store()->get( 'user' ) ?? false;



    // (Getting the value)
    $user = model( UserModel::class )->where( 'id', $id )->find( [ 'id', 'tenant', 'hierarchy', 'name', 'email' ] );

    if ( !$user )
    {// (Record not found)
        // Returning the value
        return false;
    }



    // Returning the value
    return new User( $user->id, $user->tenant, $user->hierarchy, $user->name, $user->email );
}



function entity (string $model) : Entity
{
    // Returning the value
    return new Entity( model( $model ) );
}

function element (Record $record) : Element
{
    // (Getting the value)
    $element = new Element();

    foreach ( $record->iterate() as [ $key, $value ] )
    {// Processing each entry
        // (Setting the value)
        $element->set( $key, $value );
    }

    foreach ( $record->relations as $name => $elements )
    {// Processing each entry
        // (Getting the value)
        $relation_type = is_array( $elements ) ? 'hasMany' : 'belongsTo';

        if ( $relation_type === 'belongsTo' )
        {// (Value is not an array)
            // (Getting the value)
            $elements = [ $elements ];
        }



        // (Setting the value)
        $rels = [];

        foreach ( $elements as $r )
        {// Processing each entry
            // (Getting the value)
            $related_element = new Element();

            foreach ( $r->iterate() as [ $key, $value ] )
            {// Processing each entry
                // (Setting the value)
                $related_element->set( $key, $value );
            }



            // (Appending the value)
            $rels[] = $related_element;
        }



        // (Appending the value)
        $element->set( $name, $relation_type === 'belongsTo' ? $rels[ 0 ] : $rels );
    }



    // Returning the value
    return $element;
}



function share_rule () : Record|null
{
    // Returning the value
    return store()->get( 'share_rule' );
}



function in_prod () : bool
{
    // Returning the value
    return getenv( 'APP_ENV' ) === 'production';
}



function error_format (Error $error, string $separator = "\n\n", bool $stack = false) : string
{
    // (Setting the value)
    $message_parts = [];



    if ( app()->mode === 'http' )
    {// Match OK
        // (Appending the values)
        $message_parts[] = "HTTP {$error->get_http_code()} on " . request()->method . ' ' . url()->get_fullpath();
        $message_parts[] = "HOST " . url()->host;
    }
    else
    if ( app()->mode === 'cli' )
    {// Match OK
        // (Appending the value)
        $message_parts[] = 'CLI on ' . command();
    }



    // (Appending the values)
    $message_parts[] = "CODE " . $error->getCode();
    $message_parts[] = $error->get_line();



    // Returning the value
    return implode( $separator, $message_parts ) . ( $stack ? $separator . $error->describe() : '' );
}

function telegram_send (string $message) : bool
{
    // Returning the value
    return send_request
    (
        'POST',
        'https://api.telegram.org/bot' . env( 'TELEGRAM_BOT_TOKEN' ) . '/sendMessage',
        [
            'Content-Type: application/json',
            'User-Agent: SimbaClient/1.0'
        ],
        json_encode
        (
            [
                'chat_id' => env( 'TELEGRAM_CHAT_ID' ),
                'text'    => $message
            ]
        )
    )
        !== false
    ;
}



function push_log (string $message, int|string $level = 'info', string $file_path = '/simba.log') : void
{
    // (Logging the message)
    ( new Logger( storage() . "/logs{$file_path}", pid: true ) )->push( $message, $level );
}



function colorize (string $text, string $color) : string
{
    $colors =
    [
        'blue'   => "\033[34m",
        'red'    => "\033[31m",
        'green'  => "\033[32m",
        'yellow' => "\033[33m",
        'cyan'   => "\033[36m",
        'reset'  => "\033[0m"
    ]
    ;



    // (Getting the value)
    $code = $colors[$color] ?? $colors['reset'];
    


    // Returning the value
    return "{$code}{$text}{$colors['reset']}";
}



function run_action (string $endpoint, string $action, array $headers = [], string $body = '', bool $debug = false) : Response
{
    // (Getting the value)
    $result = send_request( 'RUN', "http://localhost{$endpoint}?m=$action", $headers, $body );

    if ( !$result )
    {// (Unable to send the request)
        // Throwing the exception
        throw error( 6404, 'Unable to send the request' );
    }



    if ( $debug )
    {// Value is true
        // (Getting the value)
        $request_ok = $result->response->get_code() === 200;



        // (Getting the value)
        $status = $request_ok ? "\033[32m✓ OK\033[0m" : "\033[31m✗ FAILED\033[0m";



        // (Getting the value)
        $formatted_response_code = colorize( $result->response->get_code(), $request_ok ? 'green' : 'red' );



        // (Getting the value)
        $response_body = $result->response->get_body();



        // Printing the values
        echo "\n\n\033[36mRUN\033[0m $endpoint?m=\033[36m$action\033[0m -> $status\n";
        echo "\n-- Request -------------------------------------\n$body\n";
        echo "\n-- Response ({$formatted_response_code}) ------------------------------\n$response_body\n\n\n";
    }



    // Returning the value
    return $result->response;
}



?>