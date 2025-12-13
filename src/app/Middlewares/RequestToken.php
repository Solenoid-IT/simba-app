<?php



namespace App\Middlewares;



class RequestToken
{
    public function handle ($input, $next)
    {
        if ( !request_token()->verify() )
        {// (Verification failed)
            // Throwing the exception
            throw error( 6101, 'Request token verification failed' );

            // Returning the value
            return;
        }



        // Returning the value
        return $next( $input );
    }
}



?>