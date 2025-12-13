<?php



namespace App\Middlewares;



use \Solenoid\X\HTTP\Request;

use \App\Services\Test as TestService;



class Test
{
    public function handle (Request $request, $next, TestService $test)
    {
        // (Getting the value)
        $error = $test->verify_api_token();

        // Returning the value
        return $error ?? $next( $request );
    }
}



?>