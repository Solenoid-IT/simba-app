<?php



namespace App\DTOs\TrustedDevice;



use \Solenoid\X\Data\DTO;
use \Solenoid\X\Data\WriteFieldset;

use \Solenoid\X\Data\Types\StringValue;



#[ WriteFieldset( [ 'name', 'datetime.update' ] ) ]
class UpdateDTO extends DTO
{
    public function __construct
    (
        #[ StringValue( '', true, 'ID of the record' ) ]
        public string $id,

        #[ StringValue( '', true, 'Name of the record' ) ]
        public string $name
    )
    {}
}



?>