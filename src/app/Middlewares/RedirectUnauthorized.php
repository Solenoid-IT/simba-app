<?php



namespace App\Middlewares;



class RedirectUnauthorized
{
    public function handle ($input, $next)
    {
        // (Getting the value)
        $session = session();



        // (Starting the session)
        $session->start();
        
        if ( !$session->data['user'] )
        {// Value not found
            // (Closing the session)
            $session->close();



            // (Sending the cookie)
            cookie( 'fwd_route' )->set( url()->get_fullpath() )->send();



            // (Redirecting the response)
            response()->redirect( 303, '/login' );



            // Returning the value
            return false;
        }



        // Returning the value
        return $next( $input );
    }
}



?>