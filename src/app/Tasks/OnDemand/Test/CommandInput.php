<?php



namespace App\Tasks\OnDemand\Test;



class CommandInput
{
    public function run ()
    {
        # (USAGE: php x task OnDemand/Test/CommandInput < file.ext)



        // Printing the value
        echo input()->buffer();
    }
}



?>