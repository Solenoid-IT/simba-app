<?php



namespace App\Tasks\Tests\Endpoints\Token;



use \App\DTOs\FirewallRule\InsertDTO;
use \App\DTOs\FirewallRule\UpdateDTO;



class FirewallRule
{
    public function run ()
    {
        // (Setting the value)
        $key = '172.16.64.55';



        // (Getting the value)
        $headers = [ 'Personal-Token: ' . env( 'TEST_TOKEN' ) ];



        // (Getting the value)
        $input = new InsertDTO( $key, 'test', false );



        // (Getting the value)
        $response = run_action( '/api/token', 'FirewallRule.insert', $headers, json_encode( $input ), true );

        if ( $response->get_code() !== 200 ) return;



        // (Getting the value)
        $id = (int) $response->get_body();



        // (Getting the value)
        $response = run_action( '/api/token', 'FirewallRule.find', $headers, $id, true );

        if ( $response->get_code() !== 200 ) return;



        // (Getting the value)
        $response = run_action( '/api/token', 'FirewallRule.list', $headers, $id, true );

        if ( $response->get_code() !== 200 ) return;



        // (Getting the value)
        $input = new UpdateDTO( $id, $key, 'test (changed)', false );



        // (Getting the value)
        $response = run_action( '/api/token', 'FirewallRule.update', $headers, json_encode( $input ), true );

        if ( $response->get_code() !== 200 ) return;



        // (Getting the value)
        $response = run_action( '/api/token', 'FirewallRule.delete', $headers, json_encode( [ $id ] ), true );

        if ( $response->get_code() !== 200 ) return;
    }
}



?>