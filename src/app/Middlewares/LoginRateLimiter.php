<?php



namespace App\Middlewares;



use \Solenoid\X\HTTP\Request;



class LoginRateLimiter
{
    public function handle (Request $request, $next)
    {
        // (Limiting the rate)
        request_rate_limit( 'rate_limit:login:' . ip(), env( 'RATE_LIMIT_LOGIN' ) );



        // Returning the value
        return $next( $request );
    }
}



?>