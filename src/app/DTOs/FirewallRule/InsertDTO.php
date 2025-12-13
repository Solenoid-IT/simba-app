<?php



namespace App\DTOs\FirewallRule;



use \Solenoid\X\Data\DTO;

use \Solenoid\X\Data\Types\StringValue;
use \Solenoid\X\Data\Types\BoolValue;

use \Solenoid\X\Data\WriteFieldset;



#[ WriteFieldset( [ 'tenant', 'owner', 'range', 'description', 'datetime.insert', 'allowed' ] ) ]
class InsertDTO extends DTO
{
    public function __construct
    (
        #[ StringValue( '', true, 'Range of the record', '/^((25[0-5]|(2[0-4]|1\d|[1-9]|)\d)\.?\b){4}$/' ) ]
        public string $range,

        #[ StringValue( '', false, 'Description of the record' ) ]
        public ?string $description = null,

        #[ BoolValue( '', true, 'Client IP is allowed ?' ) ]
        public bool $allowed
    )
    {}
}



?>