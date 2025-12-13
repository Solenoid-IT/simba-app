<?php



namespace App\Endpoints\User;



use \Solenoid\X\Data\Input;

use \App\DTOs\ResourceShareFindDTO;

use \App\Services\Resource as ResourceService;



class Resource
{
    public function __construct (private ResourceService $service) {}



    #[ Input( ResourceShareFindDTO::class ) ]
    public function get_share_info (ResourceShareFindDTO $dto)
    {
        // Returning the value
        return $this->service->get_share_info( user(), $dto );
    }
}



?>