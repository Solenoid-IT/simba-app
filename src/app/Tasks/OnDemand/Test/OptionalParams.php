<?php



namespace App\Tasks\OnDemand\Test;



use \App\Services\Test as TestService;



class OptionalParams
{
    public function run (string $name = 'Guest', int $age = 0, TestService $test)
    {
        // (Printing the value)
        echo "Name: $name, Age: $age\n";
    }
}



?>