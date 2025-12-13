<?php



namespace App\Services;



class Login
{
    public function extract_location () : string
    {
        // Returning the value
        return cookie_value( 'fwd_route' ) ?? '/';
    }
}



?>