<?php



namespace App\Tasks\OnDemand;



class PrintHello
{
    public function run (string $name, string $surname)
    {
        // Printing the value
        echo "\nHello \"$name\" \"$surname\" !\n\n";
    }
}



?>