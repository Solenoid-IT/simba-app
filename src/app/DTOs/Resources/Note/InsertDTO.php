<?php



namespace App\DTOs\Resources\Note;



use \Solenoid\X\Data\DTO;
use \Solenoid\X\Data\WriteFieldset;
use \Solenoid\X\Data\Types\StringValue;



#[ WriteFieldset( [ 'name', 'description', 'datetime.insert' ] ) ]
class InsertDTO extends DTO
{
    public function __construct
    (
        #[ StringValue( '', true, 'Name of the record' ) ]
        public string $name,

        #[ StringValue( '', false, 'Description of the record' ) ]
        public ?string $description = null
    )
    {}
}



?>