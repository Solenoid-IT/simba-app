<?php



namespace App\Controllers;



use \Solenoid\sRPC\Action;
use \Solenoid\X\GlobalStore;



class ApiGateway
{
    public function handle_request ()
    {
        // (Setting the value)
        $map =
        [
            '/api/public'      => '/App/Endpoints/Public',
            '/api/user'        => '/App/Endpoints/User',
            '/api/micro/alert' => '/App/Endpoints/Micro/Alert',
            '/api/token'       => '/App/Endpoints/Token'
        ]
        ;



        // (Getting the value)
        $prefix = $map[ route()->path ] ?? false;

        if ( !$prefix )
        {// Value not found
            // (Setting the response)
            return response()->text( 404, 'ROUTE NOT FOUND' );
        }



        // (Getting the value)
        $action = new Action( request()->path, $prefix );

        // (Setting the value)
        GlobalStore::set( 'action', $action );



        if ( $action->error )
        {// Value found
            // (Sending the error)
            $action->error->send();

            // Returning the value
            return;
        }



        // Returning the value
        return app()->run( $action->class_path, $action->method );
    }
}



?>