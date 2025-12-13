<?php



namespace App\Endpoints\Token;



use \Solenoid\X\Data\Input;
use \Solenoid\X\Data\ArrayList;
use \Solenoid\X\Data\Types\IntValue;

use \App\DTOs\GroupShareRule\UpdateDTO;
use \App\DTOs\GroupShareRule\InsertDTO;

use \App\Services\GroupShareRule as GroupShareRuleService;



class GroupShareRule
{
    public function __construct (private GroupShareRuleService $service) {}



    #[ Input( new IntValue( 'id', true, 'ID of the record', 1 ) ) ]
    public function find (int $id)
    {
        // Returning the value
        return $this->service->find( $id, [ 'id', 'group', 'share_rule' ] );
    }



    #[ Input( UpdateDTO::class ) ]
    public function update (UpdateDTO $input)
    {
        // Returning the value
        return $this->service->upsert( $input );
    }

    #[ Input( InsertDTO::class ) ]
    public function insert (InsertDTO $input)
    {
        // Returning the value
        return $this->service->upsert( $input );
    }

    #[ Input( new ArrayList( new IntValue( 'id', true, 'ID of the record', 1 ) ) ) ]
    public function delete (array $ids)
    {
        // Returning the value
        return $this->service->delete( $ids );
    }
}



?>