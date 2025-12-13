<?php



namespace App\Endpoints\Token;



use \Solenoid\X\Data\Input;

use \App\DTOs\ResourceShareFindDTO;

use \App\Services\Resource as ResourceService;



class Share
{
    public function __construct (private ResourceService $service) {}



    #[ Input( ResourceShareFindDTO::class ) ]
    public function find (ResourceShareFindDTO $input)
    {
        // Returning the value
        return $this->service->get_share_info( user(), $input );
    }
}



?>