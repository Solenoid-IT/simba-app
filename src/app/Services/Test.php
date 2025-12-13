<?php



namespace App\Services;



use \Solenoid\X\Error;



class Test
{
    public function sum (int $a, int $b) : int
    {
        // Returning the value
        return $a + $b;
    }

    public function concat (string $a, string $b) : string
    {
        // Returning the value
        return $a . $b;
    }

    public function fetch_timestamp () : int
    {
        // Returning the value
        return time();
    }



    public function verify_auth () : bool
    {
        // (Getting the value)
        $header = request()->get_header( 'Authorization' );

        if ( $header === null )
        {// Value not found
            // Throwing the exception
            throw error( 6101, 'User not authorized' );
        }



        // (Getting the values)
        [ $auth_type, $auth_token ] = explode( ' ', $header, 2 );

        if ( $auth_type !== 'Bearer' )
        {// Match failed
            // Throwing the exception
            throw error( 6101, 'User not authorized' );
        }

        if ( !hash_equals( env( 'API_TOKEN' ), $auth_token ) )
        {// Match failed
            // Throwing the exception
            throw error( 6101, 'User not authorized' );
        }



        // Returning the value
        return true;
    }

    public function verify_api_token () : void
    {
        // (Getting the value)
        $header = request()->get_header( 'Authorization' );

        if ( !$header )
        {// (Header not found)
            // Throwing the exception
            throw error( 6100, 'User not authorized' );
        }



        // (Getting the value)
        [ $auth_type, $auth_token ] = explode( ' ', $header, 2 );

        if ( $auth_type !== 'Bearer' )
        {// Match failed
            // Throwing the exception
            throw error( 6100, 'User not authorized' );
        }



        if ( !hash_equals( env( 'API_TOKEN' ), $auth_token ) )
        {// Match failed
            // Throwing the exception
            throw error( 6100, 'User not authorized' );
        }
    }
}



?>