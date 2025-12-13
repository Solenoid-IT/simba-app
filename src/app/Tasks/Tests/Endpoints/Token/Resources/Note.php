<?php



namespace App\Tasks\Tests\Endpoints\Token\Resources;



use \App\DTOs\Resources\Note\InsertDTO;
use \App\DTOs\Resources\Note\UpdateDTO;
use \App\DTOs\Resources\Note\BirthDataDTO;



class Note
{
    public function run ()
    {
        // (Getting the value)
        $headers = [ 'Personal-Token: ' . env( 'TEST_TOKEN' ) ];



        // (Getting the value)
        $input = new InsertDTO( 'test', 'test' );



        // (Getting the value)
        $response = run_action( '/api/token', 'Resources/Note.insert', $headers, json_encode( $input ), true );

        if ( $response->get_code() !== 200 ) return;



        // (Getting the value)
        $id = (int) $response->get_body();



        // (Getting the value)
        $response = run_action( '/api/token', 'Resources/Note.find', $headers, $id, true );

        if ( $response->get_code() !== 200 ) return;



        // (Getting the value)
        $response = run_action( '/api/token', 'Resources/Note.list', $headers, $id, true );

        if ( $response->get_code() !== 200 ) return;



        // (Getting the value)
        $input = new UpdateDTO( $id, 'test', 'test (changed)' );



        // (Getting the value)
        $response = run_action( '/api/token', 'Resources/Note.update', $headers, json_encode( $input ), true );

        if ( $response->get_code() !== 200 ) return;



        // (Getting the value)
        $response = run_action( '/api/token', 'Resources/Note.delete', $headers, json_encode( [ $id ] ), true );

        if ( $response->get_code() !== 200 ) return;
    }
}



?>