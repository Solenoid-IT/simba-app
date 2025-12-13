<?php



namespace App\DTOs\FirewallRule;



use \Solenoid\X\Data\DTO;

use \Solenoid\X\Data\Types\IntValue;
use \Solenoid\X\Data\Types\StringValue;
use \Solenoid\X\Data\Types\BoolValue;

use \Solenoid\X\Data\WriteFieldset;



#[ WriteFieldset( [ 'range', 'description', 'datetime.update', 'allowed' ] ) ]
class UpdateDTO extends DTO
{
    public function __construct
    (
        #[ IntValue( '', true, 'ID of the record', 1 ) ]
        public int $id,

        #[ StringValue( '', true, 'Range of the record' ) ]
        public string $range,

        #[ StringValue( '', false, 'Description of the record' ) ]
        public string $description,

        #[ BoolValue( '', true, 'Client IP is allowed ?' ) ]
        public bool $allowed
    )
    {}
}



?>