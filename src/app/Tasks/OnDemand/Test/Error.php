<?php



namespace App\Tasks\OnDemand\Test;



class Error
{
    public function run ()
    {
        // Throwing the exception
        throw error( 6000, 'test' );
    }
}



?>