<?php



namespace App\Tasks\OnDemand;



use \App\Services\Activity as ActivityService;



class Activity
{
    public function delete (?string $tenant = null)
    {
        // Returning the value
        return ( new ActivityService() )->delete( $tenant );
    }
}



?>