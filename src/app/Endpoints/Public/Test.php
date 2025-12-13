<?php



namespace App\Endpoints\Public;



use \Solenoid\X\Data\Input;
use \Solenoid\X\Data\ArrayList;
use \Solenoid\X\Data\Types\IntValue;

use \Solenoid\X\Middleware;

use \App\Middlewares\Test as TestMiddleware;

use \App\Services\Test as TestService;

use \App\DTOs\TestDTO;
use \App\DTOs\PaginatorDTO;



class Test
{
    public function fetch_timestamp (TestService $test)
    {
        // Returning the value
        return $test->fetch_timestamp();
    }



    public function event_handler ()
    {
        // Returning the value
        return response()->json( 429, [ 'code' => '1101', 'message' => 'Too many requests' ] );
    }

    public function event_handler_async ()
    {
        // (Writing to the storage)
        storage()->write( '/eh_async.json', request()->body );
    }



    #[ Input( new IntValue( 'id', true, 'ID of the record', 1 ) ) ]
    public function input_value (int $id)
    {
        // Returning the value
        return $id;
    }



    #[ Input( TestDTO::class ) ]
    public function input_dto (TestDTO $dto)
    {
        // Returning the value
        return $dto;
    }



    #[ Input( new ArrayList( new IntValue( 'id', true, 'ID of the item record', 1 ) ) ) ]
    public function input_list (array $list)
    {
        // Returning the value
        return $list;
    }



    #[ Middleware( TestMiddleware::class ) ]
    #[ Input( new IntValue( 'id', true, 'ID of the record', 1 ) ) ]
    public function pipeline (int $id)
    {
        // Returning the value
        return response()->text( 200, $id );
    }



    #[ Input( PaginatorDTO::class ) ]
    public function paginate (PaginatorDTO $dto)
    {
        // Returning the value
        return model( 'Test' )->where( 'id', '>', $dto->cursor->lastId )->paginate( $dto->length )->list();
    }



    #[ Input( TestDTO::class ) ]
    public function dto_get (TestDTO $dto)
    {
        // Returning the value
        #return $dto->get( 'id' );
        return collection( $dto )->get( 'items' )[0];
    }
}



?>