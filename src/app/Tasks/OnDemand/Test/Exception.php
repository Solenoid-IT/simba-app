<?php



namespace App\Tasks\OnDemand\Test;



class Exception
{
    public function run ()
    {
        // (Printing the value)
        echo 'Error: ' . ( 1 / 0 ) . "\n";
    }
}



?>