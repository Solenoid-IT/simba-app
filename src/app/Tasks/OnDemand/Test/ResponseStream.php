<?php



namespace App\Tasks\OnDemand\Test;



use \Solenoid\X\HTTP\Client\Sender;
use \Solenoid\X\HTTP\Request;



class ResponseStream
{
    public function run ()
    {
        // (Creating a Sender)
        $sender = new Sender();



        // (Listening for the event)
        $sender->on( 'hop', function ($hop) {
            // Printing the value
            echo 'hop: ' . json_encode( $hop ) . "\n";
        });

        // (Listening for the event)
        $sender->on( 'data', function ($data) {
            // Printing the value
            echo 'data: ' . json_encode( $data ) . "\n";
        });



        // (Sending the request)
        $result = $sender->send( new Request( 'GET', '/test/event' ), 'http://' . env('APP_FQDN'), true );



        // Printing the value
        #echo json_encode( $result, JSON_PRETTY_PRINT ) . "\n";
    }
}



?>