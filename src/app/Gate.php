<?php



namespace App;



use \Solenoid\X\App;

use \Solenoid\Network\IPv4\Firewall;

use \App\Services\Cors as CorsService;



class Gate
{
    public function __construct (private App $app) {}



    public function traverse () : bool
    {
        switch ( $this->app->mode )
        {
            case 'cli':
                // Printing the value
                #echo "Command '" . command() . " traversed the gate\n";
            break;

            case 'http':
                // (Getting the value)
                $client_ip = ip();



                // (Limiting the rate)
                request_rate_limit( "rate_limit:global:$client_ip", env( 'RATE_LIMIT_GLOBAL' ) );



                // (Getting the value)
                $firewall = new Firewall();

                if ( !$firewall->pass( $client_ip ) )
                {// (IP does not pass the firewall)
                    // (Logging the message)
                    push_log( "IP $client_ip does not pass the firewall", file_path: '/app/http/firewall.log' );



                    // (Setting the response)
                    response()->text( 403, 'Forbidden' );



                    // Returning the value
                    return false;
                }



                if ( ( new CorsService() )->run() === false ) return false;
            break;
        }



        // Returning the value
        return true;
    }
}



?>