<?php



namespace App\Controllers;



use \Solenoid\X\HTTP\Request;

use \Solenoid\MySQL\DateTime;

use \App\Services\Operation as OperationService;
use \App\Services\User as UserService;



class Operation
{
    public function run (string $id, OperationService $operation_service, UserService $user_service)
    {
        // (Getting the value)
        $operation = $operation_service->find( $id );

        if ( !$operation )
        {// (Record not found)
            // Returning the value
            return response()->text( 200, 'Operation has been processed' );
        }



        if ( $operation->get( 'datetime.authorization' ) )
        {// (Operation has been authorized)
            // Returning the value
            return response()->text( 200, 'Operation has been processed' );
        }



        // (Updating the record)
        $operation_service->update( $id, [ 'datetime.authorization' => DateTime::fetch() ] );



        /*

        if ( $operation['request'] )
        {// Value found
            // (Getting the value)
            $request = Request::parse( $operation['request'] );


            // (Sending the request)
            $result = ( new Request( $request->method, $request->path, $request->protocol, array_merge( $request->headers, [ 'X-Forwarded-For: ' . ip(), 'User-Agent: ' . ua() ] ), $request->body ) )->send( 'https://' . env( 'OP_HOST' ) );

            if ( $result === false )
            {// (Unable to send the request)
                // Throwing the exception
                throw error( 6000, 'Unable to send the request' );
            }
        }

        */



        if ( $operation->task )
        {// (Value found)
            // (Running the task)
            $result = run( $operation->task, [], $operation->data )->output;
        }



        if ( $operation->login )
        {// Value found
            // (Getting the value)
            $user = user( (int) $result );



            // (Getting the value)
            $session = session();

            if ( !$session->start() )
            {// (Unable to start the session)
                // Throwing the exception
                throw error( 6000, 'Unable to start the session' );
            }

            if ( !$session->regenerate_id() )
            {// (Unable to regenerate the session ID)
                // Throwing the exception
                throw error( 6000, 'Unable to regenerate the session ID' );
            }

            if ( !$session->set_duration() )
            {// (Unable to set the session duration)
                // Throwing the exception
                throw error( 6000, 'Unable to set the session duration' );
            }



            // (Setting the value)
            $session->data = [];



            // (Getting the value)
            $session->data['user'] = $user->id;



            // (Setting the user)
            $user_service->user( $user );



            // (Listening for the event)
            $session->on('update', function ($session) use ($user_service) {
                // (Reporting the access)
                $user_service->report_access( $session->id, 'web_invite' );
            });
        }
        else
        if ( $operation->display )
        {// Value found
            // (Setting the header)
            header( 'Content-Type: text/html' );

            // (Printing the value)
            echo $operation->display;



            // Returning the value
            return null;
        }



        if ( $operation->callback_url )
        {// (Value found)
            // Returning the value
            return response()->redirect( 303, $operation->callback_url );
        }



        // Returning the value
        return response()->text( 200, 'Operation has been processed' );
    }
}



?>