<?php



namespace App\Endpoints\User;



use \Solenoid\X\Data\Input;
use \Solenoid\X\Data\ArrayList;
use \Solenoid\X\Data\Types\IntValue;

use \Solenoid\X\Middleware;

use \App\Middlewares\RequestToken as RequestTokenMiddleware;

use \App\DTOs\PersonalToken\UpdateDTO;
use \App\DTOs\PersonalToken\InsertDTO;

use \App\Services\PersonalToken as PersonalTokenService;



class PersonalToken
{
    public function __construct (private PersonalTokenService $service) {}



    #[ Input( new IntValue( 'id', false, 'ID of the record', 1 ) ) ]
    public function find (int $id)
    {
        // Returning the value
        return $this->service->element( $id )->find( [ 'id', 'name', 'description' ] );
    }

    public function list ()
    {
        // Returning the value
        return $this->service->list( [ 'id', 'name', 'datetime.insert', 'datetime.update' ] );
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
        // (Inserting the element)
        $element_id = $this->service->insert( $dto, $meta );



        // Returning the value
        return
        [
            'id'    => $element_id,
            'token' => $meta['token']
        ]
        ;
    }

    #[ Middleware( RequestTokenMiddleware::class ) ]
    #[ Input( new ArrayList( new IntValue( 'id', false, 'ID of the record', 1 ) ) ) ]
    public function delete (array $ids)
    {
        // Returning the value
        return $this->service->elements( $ids )->delete();
    }
}



?>