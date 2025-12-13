<?php



namespace App\Tasks\OnDemand\Test;



use \Solenoid\X\HTTP\Client\Sender;



class RequestPush
{
    public function run ()
    {
        // (Getting the value)
        $input = json_decode( file_get_contents( 'php://stdin' ) );



        // (Sending the request)
        $result = ( new Sender() )->send( $input->request, 'http://' . $input->url );



        // (Pushing the message)
        storage()->log( '/tests/request_push.log', json_encode( [ $input, $result ] ) . ' -> ' . $result->response->get_code() );
    }
}



?>