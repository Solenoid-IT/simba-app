<?php



namespace App\Endpoints\User\Resources;



use \Solenoid\X\Data\Input;
use \Solenoid\X\Data\ArrayList;
use \Solenoid\X\Data\Types\IntValue;
use \Solenoid\X\Data\ReadableStream;

use \Solenoid\X\Middleware;

use \App\Middlewares\RequestToken as RequestTokenMiddleware;

use \App\DTOs\Resources\Note\UpdateDTO;
use \App\DTOs\Resources\Note\InsertDTO;

use \App\DTOs\PaginatorDTO;

use \App\Services\Resources\Note as NoteService;



class Note
{
    const RESOURCE_TYPE = 1;



    public function __construct (private NoteService $service) {}



    #[ Input( new IntValue( 'id', true, 'ID of the record', 1 ) ) ]
    public function find (int $id)
    {
        // Returning the value
        return $this->service->element( $id )->find( [ 'id', 'name', 'description' ] );
    }

    #[ Input( PaginatorDTO::class ) ]
    public function list (PaginatorDTO $paginator)
    {
        // Returning the value
        return $this->service->paginate( $paginator )->list( [ 'id', 'owner', 'name', 'datetime.insert', 'datetime.update', 'share_rule', 'ref.user.name' ] );
    }



    #[ Middleware( RequestTokenMiddleware::class ) ]
    #[ Input( UpdateDTO::class ) ]
    public function update (UpdateDTO $dto)
    {
        // Returning the value
        return $this->service->element( $dto->id )->update( $dto );
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
        // Returning the value
        return $this->service->elements( $ids )->delete( $ids );
    }



    #[ Input( new ArrayList( new IntValue( 'id', true, 'ID of the record', 1 ) ) ) ]
    public function export (array $ids)
    {
        // (Exporting records)
        $this->service->elements( $ids )->export( [ 'name', 'description', 'datetime.insert', 'datetime.update' ], 'notes.csv' );
    }

    #[ Middleware( RequestTokenMiddleware::class ) ]
    #[ Input( new ReadableStream( 'csv_content' ) ) ]
    public function import ()
    {
        // Returning the value
        return $this->service->import( request()->body );
    }
}



?>