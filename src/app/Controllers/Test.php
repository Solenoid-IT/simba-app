<?php



namespace App\Controllers;



use \Solenoid\X\HTTP\Request;
use \Solenoid\X\Process;

use \Solenoid\X\Stream\ReadableStream;

use \Solenoid\MySQL\Condition;

use \App\User;

use \App\Models\User as UserModel;
use \App\Models\Hierarchy as HierarchyModel;
use \App\Models\Group as GroupModel;

use \App\DTOs\Group\UpdateDTO;

use \App\Services\Test as TestService;
use \App\Services\User as UserService;



class Test
{
    public function concat (string $a, string $b, TestService $test) : string
    {
        // Returning the value
        return $test->concat( $a, $b );
    }



    public function request_stream ()
    {
        // (Getting the value)
        $stream = request()->body;



        // (Opening the stream)
        $stream->open();

        while ( !$stream->ended() )
        {
            // (Getting the value)
            $buffer = $stream->read( 1 );

            if ( $buffer === false || $buffer === '' ) break;



            // (Appending the value)
            $chars[] = $buffer;
        }

        // (Closing the stream)
        $stream->close();



        // Returning the value
        return response()->json( 200, $chars );
    }

    public function response_stream ()
    {
        // Returning the value
        return response()->stream
        (
            200,
            [
                'Transfer-Encoding: chunked',
            ],
            function ()
            {
                ob_implicit_flush(1);
                ob_end_flush();

                sleep(1);
                echo '1';

                sleep(2);
                echo '2';

                sleep(3);
                echo '3';
            }
        )
        ;
    }



    public function file_downloader ()
    {
        // (Getting the value)
        $file_size = 1024 * 1024 * 1;# 1 MB



        // (Getting the value)
        $download_speed = $file_size / 10;# 100 KB/s



        // (Getting the value)
        $stream = ( new ReadableStream( ReadableStream::TYPE_STRING ) )->set_content( str_repeat( '1', $file_size ) );



        // Returning the value
        return response()->stream
        (
            200,
            [
                'Content-Type: application/octet-stream',
                "Content-Length: {$file_size}",
                'Content-Disposition: attachment; filename="file.ext"'
            ],
            function () use ($download_speed, $stream)
            {
                // (Opening the stream)
                $stream->open();



                while ( !$stream->ended() )
                {// Processing each chunk
                    // (Getting the value)
                    $buffer = $stream->read( $download_speed );

                    if ( $buffer === false || $buffer === '' ) break;



                    // Printing the value
                    echo $buffer;

                    // (Flushing the content)
                    ob_flush();
                    flush();



                    // (Waiting for the time)
                    sleep( 1 );
                }



                // (Closing the stream)
                $stream->close();
            }
        )
        ;
    }



    public function async_http_sender ()
    {
        // (Getting the value)
        $input =
        [
            'request' => new Request( 'RPC', '/test/push', 'HTTP/2.0', [ 'X-Custom-Header: abcde' ], 'Hello World' ),
            'url'     => url()->host
        ]
        ;



        // (Spawning the process)
        $process = Process::spawn( 'php x task OnDemand/Test/RequestPush', app()->basedir, json_encode( $input ) );

        if ( !$process )
        {// (Unable to spawn the process)
            // Returning the value
            return response()->text( 500, 'Unable to spawn the process' );
        }



        // Returning the value
        return response();
    }

    public function push ()
    {
        // (Writing to the storage)
        storage()->write( '/tests/push', request() );
    }



    public function event ()
    {
        // Returning the value
        return response()->stream
        (
            200,
            [
                'Content-Type: text/event-stream',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
            ],
            function ()
            {
                while ( true )
                {// Processing each clock
                    if ( connection_aborted() ) break;



                    // (Setting the value)
                    $event_type = 'timeupdate';



                    // (Getting the value)
                    $timestamp = date( 'c' );



                    // Printing the value
                    echo "event: {$event_type}\ndata: {$timestamp}\n\n";

                    // (Flushing the content)
                    ob_flush();
                    flush();



                    // (Waiting for the time)
                    sleep( 1 );
                }
            }
        )
        ;
    }



    public function model_cursor ()
    {
        // Printing the value
        echo '<pre>';



        // (Getting the value)
        $cursor = model( 'Hierarchy' )->cursor();

        while ( $record = $cursor->read() )
        {// Processing each entry
            // Printing the value
            echo json_encode( $record ) . "<br>";
        }



        // (Closing the cursor)
        $cursor->close();



        // Printing the value
        echo '</pre>';
    }

    public function model_rel ()
    {
        // Returning the value
        return
            model( 'User' )
                ->where( 'id', '<=', 100 )
                ->and()
                ->rel( model( 'Hierarchy' ), fn (Condition $c) => $c->where( 'name', 'admin' ) )
                ->link( [ [ model( 'Hierarchy' ), [ 'id', 'name' ] ] ] )
                ->list( [ 'id', 'name' ] )
        ;
    }

    public function model_find ()
    {
        return model( 'User' )->where( 'id', 1 )->link( [ [ model( 'Hierarchy' ), [ 'name', 'color' ] ] ] )->find( [ 'id', 'name' ] );
    }



    public function entity_find ()
    {
        return entity( 'User' )->element( 1 )->find( [ 'id', 'name' ], [ [ model( 'Hierarchy' ), [ 'id', 'name', 'color' ] ] ] );
    }

    public function entity_list ()
    {
        return
            entity( UserModel::class )
                ->filter( function (Condition $c) { $c->where( 'id', '<', 2 ); } )
                ->link( [ [ HierarchyModel::class, [ 'id', 'name', 'color' ] ] ] )
                ->list( [ 'id', 'name' ] )
        ;
    }

    public function entity_update ()
    {
        // (Updating the entity)
        entity( GroupModel::class )
            ->user( new User( 1, 1, 1, 'admin', 'email@domain.tld' ) )
            ->element( 12345 )
            ->update( new UpdateDTO( 1, 'test', [ 1, 2 ] ) )
        ;
    }



    public function service (UserService $user)
    {
        return $user->model_name;
    }
}



?>