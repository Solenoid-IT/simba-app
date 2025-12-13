<?php



namespace App\Endpoints\User;



use \App\Services\Dashboard as DashboardService;



class Dashboard
{
    public function __construct (private DashboardService $service) {}



    public function calc_report ()
    {
        // Returning the value
        return $this->service->calc_report( user(), session() );
    }
}



?>