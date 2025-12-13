<?php



use \Solenoid\X\HTTP\Route;

use \App\Controllers\Test;
use \App\Controllers\ApiGateway;
use \App\Controllers\SPA;
use \App\Controllers\DynamicFile;
use \App\Controllers\Error;
use \App\Controllers\Operation;
use \App\Controllers\Fallback;

use \App\Middlewares\RedirectUnauthorized as RedirectUnauthorizedMiddleware;
use \App\Middlewares\User as UserMiddleware;
use \App\Middlewares\Test as TestMiddleware;
use \App\Middlewares\Micro\Alert as AlertMicroMiddleware;
use \App\Middlewares\Token as TokenMiddleware;

use \App\Services\Test as TestService;



// (Binding the routes)
Route::bind( 'RUN /api/public', [ ApiGateway::class, 'handle_request' ] );
Route::bind( 'RUN /api/user', [ ApiGateway::class, 'handle_request' ] )->via( [ UserMiddleware::class ] );
Route::bind( 'RUN /api/micro/alert', [ ApiGateway::class, 'handle_request' ] )->via( [ AlertMicroMiddleware::class ] );
Route::bind( 'RUN /api/token', [ ApiGateway::class, 'handle_request' ] )->via( [ TokenMiddleware::class ] );



// (Binding the route)
Route::bind( 'GET /op/{ id }', [ Operation::class, 'run' ] );



// (Setting the value)
$dynamic_files =
[
    '/robots.txt',
    '/sitemap.xml'
]
;

foreach ( $dynamic_files as $path )
{// Processing each entry
    // (Binding the route)
    Route::bind( "GET $path", [ DynamicFile::class, 'handle_request' ] );
}



// (Binding the route)
Route::bind( 'GET /errors', [ Error::class, 'handle_request' ] );



foreach ( spa_routes() as $path )
{// Processing each entry
    // (Binding the route)
    $route = Route::bind( "GET $path", [ SPA::class, 'handle_request' ] );

    if ( $path !== '/login' )
    {// Match OK
        // (Adding middlewares)
        $route->via( [ RedirectUnauthorizedMiddleware::class ] );
    }
}



// (Binding the route)
Route::bind
(
    'GET /apidoc',
    function ()
    {
        // (Getting the value)
        $file_content = storage()->read( '/apidoc.html' );
        
        if ( $file_content )
        {// Value found
            // Printing the value
            echo $file_content;
        }
        else
        {// Value not found
            // Returning the value
            return response()->text( 404, 'RESOURCE NOT FOUND' );
        }
    }
)
;



if ( !in_prod() )
{// (Env is 'development')
    // (Binding the route)
    Route::bind( 'GET /info', function () { phpinfo(); } );



    // (Binding the routes for testing)
    Route::bind( 'GET /test/sum/{ a }/{ b }', function (int $a, int $b, TestService $test) { return $test->sum( $a, $b ); } );
    Route::bind( 'GET' . ' ' . '/^\/test\/regex\/diff\/([0-9]+)\-([0-9]+)$/', function (string $match, int $a, int $b, TestService $test) { return $test->sum( $a, -1 * $b ); } );
    Route::bind( 'GET /test/concat/{ a }/{ b }', [ Test::class, 'concat' ] );
    Route::bind( 'GET /test/user', function () {} )->via( [ UserMiddleware::class ] );
    Route::bind( 'GET /test/exception', function () { throw new \Exception('Test'); } );
    Route::bind( 'GET /test/error', function () { throw error( 6000, 'Test' ); } );
    Route::bind( 'GET /test/private_error', function () { throw error( 7000, 'Private' ); } );
    Route::bind( 'GET /test/empty', function () {} );
    Route::bind( 'RUN /test/request', function () { return response()->text( 200, request() ); } );
    Route::bind( 'RUN /test/request_stream', [ Test::class, 'request_stream' ] );
    Route::bind( 'RUN /test/response_stream', [ Test::class, 'response_stream' ] );
    Route::bind( 'GET /test/file_downloader', [ Test::class, 'file_downloader' ] );
    Route::bind( 'RUN /test/url_params', function () { return url()->params->fetch(); } );
    Route::bind( 'RUN /test/async_http_sender', [ Test::class, 'async_http_sender' ] );
    Route::bind( 'RUN /test/push', [ Test::class, 'push' ] );
    Route::bind( 'GET /test/event', [ Test::class, 'event' ] );
    Route::bind( 'GET /test/model', function () { return model( 'Hierarchy' )->list(); } );
    Route::bind( 'GET /test/middleware', function () { return response()->text( 200, 'OK' ); } )->via( [ TestMiddleware::class ] );
    Route::bind( 'RUN /test/webhook', function () { storage()->write( '/webhook-test.json', json_encode( [ 'request' => request(), 'data' => (string) request()->body ], JSON_PRETTY_PRINT ) ); } );
    Route::bind( 'GET /test/model_cursor', [ Test::class, 'model_cursor' ] );
    Route::bind( 'GET /test/model_rel', [ Test::class, 'model_rel' ] );
    Route::bind( 'GET /test/model_find', [ Test::class, 'model_find' ] );
    Route::bind( 'GET /test/entity_find', [ Test::class, 'entity_find' ] );
    Route::bind( 'GET /test/entity_list', [ Test::class, 'entity_list' ] );
    Route::bind( 'GET /test/entity_update', [ Test::class, 'entity_update' ] );
    Route::bind( 'GET /test/service', [ Test::class, 'service' ] );
}



// (Defining the fallback)
Route::fallback( [ Fallback::class, 'handle_request' ] );



?>