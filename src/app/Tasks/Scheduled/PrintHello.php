<?php



namespace App\Tasks\Scheduled;



use \Solenoid\X\CLI\Mutex;
use \Solenoid\X\CLI\Schedule;



class PrintHello
{
    #[ Mutex ]
    ##[ Schedule( 'EVERY 1 MINUTE' ) ]
    #[ Schedule( 'EVERY 3 MINUTE', enabled: false ) ]
    public function run ()
    {
        // Printing the value
        echo "\nWaiting for the time to print the message...\n";



        // (Waiting for the time)
        sleep( 10 );



        // Printing the value
        echo "\nHello from the scheduled task !\n\n";
    }
}



?>