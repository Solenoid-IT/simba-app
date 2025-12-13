<?php



namespace App\Tasks\OnDemand;



class SendRequest
{
    public function run ()
    {
        // (Getting the value)
        $input = command()->json( true );



        // (Sending the request)
        send_request( $input['method'], $input['url'], $input['headers'], $input['body'], $input['response_timeout'], $input['response_timeout'] );
    }
}



?>