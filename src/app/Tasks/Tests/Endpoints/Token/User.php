<?php



namespace App\Tasks\Tests\Endpoints\Token;



use \App\DTOs\User\InsertDTO;
use \App\DTOs\User\UpdateDTO;
use \App\DTOs\User\BirthDataDTO;



class User
{
    public function run ()
    {
        // (Getting the value)
        $headers = [ 'Personal-Token: ' . env( 'TEST_TOKEN' ) ];



        // (Getting the value)
        $input = new InsertDTO( 2, 'test_manager', 'local@domain.tld', new BirthDataDTO( 'Name', 'Surname' ) );



        // (Getting the value)
        $response = run_action( '/api/token', 'User.insert', $headers, json_encode( $input ), true );

        if ( $response->get_code() !== 200 ) return;



        // (Getting the value)
        $id = (int) $response->get_body();



        // (Getting the value)
        $response = run_action( '/api/token', 'User.find', $headers, $id, true );

        if ( $response->get_code() !== 200 ) return;



        // (Getting the value)
        $response = run_action( '/api/token', 'User.list', $headers, $id, true );

        if ( $response->get_code() !== 200 ) return;



        // (Getting the value)
        $input = new UpdateDTO( $id, 2, 'test_manager (changed)', new BirthDataDTO( 'Name', 'Surname' ) );



        // (Getting the value)
        $response = run_action( '/api/token', 'User.update', $headers, json_encode( $input ), true );

        if ( $response->get_code() !== 200 ) return;



        // (Getting the value)
        $response = run_action( '/api/token', 'User.delete', $headers, json_encode( [ $id ] ), true );

        if ( $response->get_code() !== 200 ) return;
    }
}



?>