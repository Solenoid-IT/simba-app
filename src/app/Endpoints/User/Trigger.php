<?php



namespace App\Endpoints\User;



use \Solenoid\X\Data\Input;
use \Solenoid\X\Data\ArrayList;
use \Solenoid\X\Data\Types\IntValue;

use \Solenoid\X\Middleware;

use \App\User;

use \App\Middlewares\RequestToken as RequestTokenMiddleware;

use \App\DTOs\PaginatorDTO;
use \App\DTOs\Trigger\SetEnabledDTO;

use \App\DTOs\Trigger\UpdateDTO;
use \App\DTOs\Trigger\InsertDTO;

use \App\Services\Trigger as TriggerService;



class Trigger
{
    private User           $user;
    private TriggerService $service;



    public function __construct (TriggerService $service)
    {
        // (Getting the value)
        $this->user = store()->get( 'user' );



        // (Getting the value)
        $this->service = $service;
    }



    #[ Input( new IntValue( 'id', true, 'ID of the record', 1 ) ) ]
    public function find (int $id)
    {
        // (Getting the value)
        $element = $this->service->element( $id )->find( [ 'id', 'name', 'description', 'events', 'request.method', 'request.url', 'request.content', 'response_timeout', 'enabled' ] );

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
        return $this->service->paginate( $paginator)->list( [ 'id', 'owner', 'name', 'description', 'datetime.insert', 'datetime.update', 'enabled', 'ref.user.name' ] );
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



    #[ Middleware( RequestTokenMiddleware::class ) ]
    #[ Input( SetEnabledDTO::class ) ]
    public function set_enabled (SetEnabledDTO $dto)
    {
        // (Setting the field)
        $this->service->element( $dto->id )->set_field( 'enabled', $dto->enabled );
    }
}



?>