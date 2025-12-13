<?php



namespace App\Endpoints\Token;



use \Solenoid\X\Data\Input;
use \Solenoid\X\Data\Output;
use \Solenoid\X\Data\ArrayList;
use \Solenoid\X\Data\Types\IntValue;

use \App\DTOs\Group\UpdateDTO;
use \App\DTOs\Group\InsertDTO;

use \App\Services\Group as GroupService;



class Group
{
    const READ_FIELDS = [ 'id', 'owner', 'name', 'datetime.insert', 'datetime.update' ];



    public function __construct (private GroupService $service) {}



    #[ Input( new IntValue( 'id', true, 'ID of the record', 1 ) ) ]
    public function find (int $id)
    {
        // (Getting the value)
        $element = $this->service->element( $id )->find( self::READ_FIELDS );

        if ( !$element )
        {// (Element not found)
            // Throwing the exception
            throw error( 6300, "Entity '{$this->service->model_name}' :: Record not found" );
        }



        // Returning the value
        return $element;
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

    #[ Input( new ArrayList( new IntValue( 'id', true, 'ID of the record', 1 ) ) ) ]
    public function delete (array $ids)
    {
        // Returning the value
        return $this->service->elements( $ids )->delete( $ids );
    }
}



?>