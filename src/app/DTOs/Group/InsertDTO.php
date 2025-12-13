<?php



namespace App\DTOs\Group;



use \Solenoid\X\Data\DTO;
use \Solenoid\X\Data\ArrayList;
use \Solenoid\X\Data\Types\IntValue;
use \Solenoid\X\Data\Types\StringValue;

use \Solenoid\X\Data\WriteFieldset;



#[ WriteFieldset( [ 'name', 'datetime.insert' ] ) ]
class InsertDTO extends DTO
{
    public function __construct
    (
        #[ StringValue( '', true, 'Name of the record' ) ]
        public string $name,

        #[ ArrayList( new IntValue( 'id', true, 'ID of the user', 1 ) ) ]
        public array $users = []
    )
    {}
}



?>