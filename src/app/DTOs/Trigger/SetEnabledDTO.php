<?php



namespace App\DTOs\Trigger;



use \Solenoid\X\Data\DTO;
use \Solenoid\X\Data\WriteFieldset;

use \Solenoid\X\Data\Types\IntValue;
use \Solenoid\X\Data\Types\BoolValue;



#[ WriteFieldset( [ 'name', 'description', 'events', 'request.method', 'request.url', 'request.content', 'response_timeout', 'datetime.update', 'enabled' ] ) ]
class SetEnabledDTO extends DTO
{
    public function __construct
    (
        #[ IntValue( '', true, 'ID of the record', 1 ) ]
        public int $id,

        #[ BoolValue( '', true, 'Whether the trigger is active' ) ]
        public bool $enabled
    )
    {}
}



?>