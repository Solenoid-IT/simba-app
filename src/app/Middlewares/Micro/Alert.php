<?php



namespace App\Middlewares\Micro;



use \App\Services\Session as SessionService;



class Alert
{
    public function handle ($input, $next, SessionService $session)
    {
        // (Getting the value)
        $session_id = request()->get_header( 'Session-Id' );

        if ( $session_id === null )
        {// Value not found
            // Returning the value
            return false;
        }



        // (Getting the value)
        $session_data = $session->get_data( $session_id );

        if ( $session_data === false )
        {// (Record not found)
            // Returning the value
            return false;
        }



        // (Setting the value)
        store()->set( 'user', user( $session_data['user'] ) );



        // Returning the value
        return $next( $input );
    }
}



?>