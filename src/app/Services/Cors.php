<?php



namespace App\Services;



class Cors
{
    public function set_headers (array $origins = [], array $methods = [], array $headers = [], bool $credentials = false)
    {
        // (Getting the value)
        $current_origin = request()->get_header( 'Origin' );

        if ( !$current_origin || ( $origins && !in_array( $current_origin, $origins ) ) )
        {// Match failed
            // Returning the value
            return;
        }



        // (Getting the values)
        $origin      = $current_origin;
        $methods     = $methods ? implode( ', ', $methods ) : '*';
        $headers     = $headers ? implode( ', ', $headers ) : '*';
        $credentials = $credentials ? 'true' : 'false';



        // (Setting the headers)
        header("Access-Control-Allow-Origin: $origin");
        header("Access-Control-Allow-Methods: $methods");
        header("Access-Control-Allow-Headers: $headers");
        header("Access-Control-Allow-Credentials: $credentials");
    }

    public function run () : bool
    {
        // (Getting the value)
        $origin = request()->get_header( 'Origin' );

        if ( $origin )
        {// Match OK
            // (Setting the headers)
            self::set_headers( [ $origin ], [ 'GET', 'RUN' ], [ 'Content-Type', 'Request-Token', 'X-Forwarded-For', 'User-Agent' ], true );

            // (Sending the header)
            header( 'Access-Control-Expose-Headers: Content-Disposition' );
        }

        if ( request()->method === 'OPTIONS' )
        {// Match OK
            // Returning the value
            return false;
        }



        // Returning the value
        return true;
    }
}



?>