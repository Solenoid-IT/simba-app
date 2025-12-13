<?php



namespace App\DTOs\User;



use \Solenoid\X\Data\DTO;
use \Solenoid\X\Data\WriteFieldset;

use \Solenoid\X\Data\Types\StringValue;



#[ WriteFieldset( [ 'name' => 'birth.name', 'surname' => 'birth.surname', 'datetime.update' ] ) ]
class BirthDataDTO extends DTO
{
    public function __construct
    (
        #[ StringValue( '', false, 'Name of the record' ) ]
        public string $name,

        #[ StringValue( '', false, 'Surname of the record' ) ]
        public string $surname
    )
    {}
}



?>