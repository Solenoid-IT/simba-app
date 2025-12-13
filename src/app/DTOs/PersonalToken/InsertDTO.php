<?php



namespace App\DTOs\PersonalToken;



use \Solenoid\X\Data\DTO;

use \Solenoid\X\Data\Types\StringValue;

use \Solenoid\X\Data\WriteFieldset;



#[ WriteFieldset( [ 'name', 'description', 'token', 'datetime.insert' ] ) ]
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