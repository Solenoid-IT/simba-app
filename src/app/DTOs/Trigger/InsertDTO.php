<?php



namespace App\DTOs\Trigger;



use \Solenoid\X\Data\DTO;
use \Solenoid\X\Data\WriteFieldset;

use \Solenoid\X\Data\Types\IntValue;
use \Solenoid\X\Data\Types\StringValue;
use \Solenoid\X\Data\Types\BoolValue;

use \App\DTOs\Trigger\RequestDTO;



#[ WriteFieldset( [ 'name', 'description', 'events', 'request.method', 'request.url', 'request.content', 'response_timeout', 'datetime.insert', 'enabled' ] ) ]
class InsertDTO extends DTO
{
    public function __construct
    (
        #[ StringValue( '', true, 'Name of the trigger' ) ]
        public string $name,

        #[ StringValue( '', false, 'Description of the trigger' ) ]
        public ?string $description = null,

        #[ StringValue( '', false, 'Events associated with the trigger' ) ]
        public ?string $events = null,

        public RequestDTO $request,

        #[ IntValue( '', true, 'Response timeout in seconds', 0 ) ]
        public int $response_timeout = 0,

        #[ BoolValue( '', true, 'Whether the trigger is active' ) ]
        public bool $enabled = true
    )
    {}
}



?>