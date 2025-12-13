<?php



namespace App\Endpoints\Token;



use \Solenoid\X\Data\Input;
use \Solenoid\X\Data\ArrayList;
use \Solenoid\X\Data\Types\IntValue;

use \App\DTOs\FirewallRule\UpdateDTO;
use \App\DTOs\FirewallRule\InsertDTO;

use \App\Services\FirewallRule as FirewallRuleService;



class FirewallRule
{
    const READ_FIELDS = [ 'id', 'range', 'datetime.insert', 'datetime.update', 'allowed' ];



    public function __construct (private FirewallRuleService $service) {}



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
    public function update (UpdateDTO $input)
    {
        // Returning the value
        return $this->service->update( $input );
    }

    #[ Input( InsertDTO::class ) ]
    public function insert (InsertDTO $input)
    {
        // Returning the value
        return $this->service->insert($input );
    }

    #[ Input( new ArrayList( new IntValue( 'id', true, 'ID of the record', 1 ) ) ) ]
    public function delete (array $ids)
    {
        // Returning the value
        return $this->service->elements( $ids )->delete();
    }
}



?>