<?php



namespace App\DTOs\Resources\Note;



use \Solenoid\X\Data\DTO;
use \Solenoid\X\Data\WriteFieldset;

use \Solenoid\X\Data\Types\IntValue;
use \Solenoid\X\Data\Types\StringValue;



#[ WriteFieldset( [ 'name', 'description', 'datetime.update' ] ) ]
class UpdateDTO extends DTO
{
    public function __construct
    (
        #[ IntValue( '', true, 'ID of the record', 1 ) ] 
        public int $id,

        #[ StringValue( '', true, 'Name of the record' ) ]
        public string $name,

        #[ StringValue( '', false, 'Description of the record' ) ]
        public ?string $description = null
    )
    {}
}



?>