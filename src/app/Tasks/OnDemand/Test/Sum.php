<?php



namespace App\Tasks\OnDemand\Test;



use \App\Services\Test as TestService;



class Sum
{
    public function run (int $a, int $b, TestService $test)
    {
        // (Printing the value)
        echo 'Sum: ' . $test->sum( $a, $b ) . "\n";
    }
}



?>