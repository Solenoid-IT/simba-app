<?php



namespace App\Tasks\OnDemand\Test;



use \Solenoid\X\Stream\ReadableStream as Stream;



class ReadableStream
{
    public function run ()
    {
        // (Creating the stream)
        $stream = new Stream( storage() . '/debug/file.txt' );

        // Printing the value
        echo $stream;
    }
}



?>