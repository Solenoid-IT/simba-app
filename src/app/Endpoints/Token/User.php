<?php



namespace App\Endpoints\Token;



use \Solenoid\X\Data\Input;
use \Solenoid\X\Data\Output;
use \Solenoid\X\Data\Types\IntValue;
use \Solenoid\X\Data\ArrayList;

use \App\DTOs\User\UpdateDTO;
use \App\DTOs\User\InsertDTO;

use \App\Services\User as UserService;



class User
{
    const READ_FIELDS = [ 'id', 'name', 'hierarchy', 'email', 'birth.name', 'birth.surname', 'datetime.insert', 'datetime.update' ];



    public function __construct (private UserService $service) {}



    #[ Input( new IntValue( 'id', true, 'ID of the record', 1 ) ) ]
    public function find (int $id)
    {
        // Returning the value
        return $this->service->element( $id )->find( self::READ_FIELDS );
    }

    public function list ()
    {
        // Returning the value
        return $this->service->list( self::READ_FIELDS );
    }



    #[ Input( UpdateDTO::class ) ]
    #[ Output( new IntValue( 'id', true, 'ID of the updated record', 1 ) ) ]
    public function update (UpdateDTO $input)
    {
        // Returning the value
        return $this->service->element( $input->id )->update( $input );
    }

    #[ Input( InsertDTO::class ) ]
    #[ Output( new IntValue( 'id', true, 'ID of the inserted record', 1 ) ) ]
    public function insert (InsertDTO $input)
    {
        // Returning the value
        return $this->service->insert( $input );
    }

    #[ Input( new ArrayList( new IntValue( 'id', true, 'ID of the user', 1 ) ) ) ]
    public function delete (array $ids)
    {
        // Returning the value
        return $this->service->elements( $ids )->delete();
    }
}



?>