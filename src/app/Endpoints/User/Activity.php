<?php



namespace App\Endpoints\User;



use \Solenoid\X\Data\Input;

use \App\DTOs\PaginatorDTO;

use \App\Services\Activity as ActivityService;



class Activity
{
    public function __construct (private ActivityService $service) {}



    #[ Input( PaginatorDTO::class ) ]
    public function list (PaginatorDTO $paginator)
    {
        // Returning the value
        return $this->service->user( user() )->paginate( $paginator )->list();
    }
}



?>