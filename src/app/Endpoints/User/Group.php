<?php



namespace App\Endpoints\User;



use \Solenoid\X\Data\Input;
use \Solenoid\X\Data\ArrayList;
use \Solenoid\X\Data\Types\IntValue;

use \Solenoid\X\Middleware;

use \App\Middlewares\RequestToken as RequestTokenMiddleware;

use \App\DTOs\Group\UpdateDTO;
use \App\DTOs\Group\InsertDTO;
use \App\DTOs\PaginatorDTO;

use \App\Services\Group as GroupService;



class Group
{
    public function __construct (private GroupService $service) {}



    #[ Input( new IntValue( 'id', true, 'ID of the record', 1 ) ) ]
    public function find (int $id)
    {
        // (Getting the value)
        $element = $this->service->element( $id )->find( [ 'id', 'name' ] );

        if ( !$element )
        {// (Record not found)
            // Throwing the exception
            throw error( 6300, "Entity '{$this->service->model_name}' :: Record not found" );
        }



        // Returning the value
        return $element;
    }

    #[ Input( PaginatorDTO::class ) ]
    public function list (PaginatorDTO $paginator)
    {
        // Returning the value
        return $this->service->paginate( $paginator )->list( [ 'id', 'owner', 'name', 'datetime.insert', 'datetime.update', 'ref.user.name' ] );
    }



    #[ Middleware( RequestTokenMiddleware::class ) ]
    #[ Input( UpdateDTO::class ) ]
    public function update (UpdateDTO $input)
    {
        // Returning the value
        return $this->service->element( $input->id )->update( $input );
    }

    #[ Middleware( RequestTokenMiddleware::class ) ]
    #[ Input( InsertDTO::class ) ]
    public function insert (InsertDTO $input)
    {
        // Returning the value
        return $this->service->insert( $input );
    }

    #[ Middleware( RequestTokenMiddleware::class ) ]
    #[ Input( new ArrayList( new IntValue( 'id', true, 'ID of the record', 1 ) ) ) ]
    public function delete (array $ids)
    {
        // (Deleting the elements)
        $this->service->elements( $ids )->delete();
    }



    public function list_index ()
    {
        // Returning the value
        return $this->service->list_index();
    }
}



?>