<?php



namespace App\Endpoints\User;



use \Solenoid\X\Data\Input;
use \Solenoid\X\Data\ArrayList;
use \Solenoid\X\Data\Types\IntValue;

use \Solenoid\X\Middleware;

use \App\Middlewares\RequestToken as RequestTokenMiddleware;

use \App\DTOs\FirewallRule\UpdateDTO;
use \App\DTOs\FirewallRule\InsertDTO;
use \App\DTOs\PaginatorDTO;

use \App\Services\FirewallRule as FirewallRuleService;



class FirewallRule
{
    public function __construct (private FirewallRuleService $service) {}



    #[ Input( new IntValue( 'id', true, 'ID of the record', 1 ) ) ]
    public function find (int $id)
    {
        // (Getting the value)
        $element = $this->service->element( $id )->find( [ 'id', 'range', 'description', 'allowed' ] );

        if ( !$element )
        {// (Record not found)
            // Throwing the exception
            throw error( 6300, "Entity '{$this->service->model_name}' :: Record not found");
        }



        // Returning the value
        return $element;
    }

    #[ Input( PaginatorDTO::class ) ]
    public function list (PaginatorDTO $paginator)
    {
        // Returning the value
        return $this->service->paginate( $paginator )->list( [ 'id', 'owner', 'range', 'datetime.insert', 'datetime.update', 'allowed', 'ref.user.name' ] );
    }



    #[ Middleware( RequestTokenMiddleware::class ) ]
    #[ Input( UpdateDTO::class ) ]
    public function update (UpdateDTO $dto)
    {
        // (Updating the element)
        $this->service->element( $dto->id )->update( $dto );
    }

    #[ Middleware( RequestTokenMiddleware::class ) ]
    #[ Input( InsertDTO::class ) ]
    public function insert (InsertDTO $dto)
    {
        // Returning the value
        return $this->service->insert( $dto );
    }

    #[ Middleware( RequestTokenMiddleware::class ) ]
    #[ Input( new ArrayList( new IntValue( 'id', true, 'ID of the record', 1 ) ) ) ]
    public function delete (array $ids)
    {
        // (Deleting the elements)
        $this->service->elements( $ids )->delete();
    }
}



?>