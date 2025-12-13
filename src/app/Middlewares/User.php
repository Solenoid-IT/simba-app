<?php



namespace App\Middlewares;



use \Solenoid\Network\IPv4\Firewall;

use \App\Models\FirewallRule as FirewallRuleModel;



class User
{
    public function handle ($input, $next)
    {
        // (Getting the value)
        $client_ip = ip();



        // (Getting the value)
        $session = session();

        if ( !$session->start() )
        {// (Unable to start the session)
            // Throwing the exception
            throw error( 6000, 'Unable to start the session' );
        }

        if ( !$session->data['user'] )
        {// Value not found
            // Throwing the exception
            throw error( 6101, 'Client not authorized' );
        }



        // (Getting the value)
        $user = user( $session->data['user'] );

        // (Setting the session)
        $user->set_session( $session );



        // (Getting the value)
        $firewall = new Firewall();

        foreach ( FirewallRuleModel::fetch()->where( [ [ 'tenant', $user->tenant ] ] )->list( [ 'range', 'allowed' ] ) as $record )
        {// Processing each entry
            if ( $record->allowed )
            {// Value is true
                // (Allowing the range)
                $firewall->allow( $record->range );
            }
            else
            {// Value is false
                // (Denying the range)
                $firewall->deny( $record->range );
            }
        }



        if ( !$firewall->pass( $client_ip ) )
        {// (IP is not allowed)
            // Throwing the exception
            throw error( 6101, 'Client not authorized' );
        }



        // (Setting the value)
        store()->set( 'user', $user );



        // Returning the value
        return $next( $input );
    }
}



?>