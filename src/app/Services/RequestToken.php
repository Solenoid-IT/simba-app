<?php



namespace App\Services;



class RequestToken
{
    private static self $instance;



    private function __construct () {}



    public static function fetch () : self
    {
        if ( !isset( self::$instance ) )
        {// Value not found
            // (Getting the value)
            self::$instance = new self();
        }



        // Returning the value
        return self::$instance;
    }



    public function employ () : string
    {
        // (Getting the value)
        $request_token = session()->data['request_token'];

        if ( !$request_token )
        {// Value not found
            // (Getting the value)
            $request_token = bin2hex( random_bytes( 128 / 2 ) );
        }



        // (Getting the value)
        session()->data['request_token'] = $request_token;



        // Returning the value
        return $request_token;
    }

    public function verify () : bool
    {
        // (Getting the value)
        $request_token = session()->data['request_token'];

        if ( !$request_token )
        {// Value not found
            // Returning the value
            return false;
        }



        // Returning the value
        return hash_equals( $request_token, request()->get_header('Request-Token') );
    }
}



?>