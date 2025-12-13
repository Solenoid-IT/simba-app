<?php



namespace App\DTOs;



use \Solenoid\X\Data\DTO;
use \Solenoid\X\Data\Types\StringValue;



class SubDTO extends DTO
{
    public function __construct
    (
        #[ StringValue( '', true, 'Name of the record' ) ]
        public string $name,

        #[ StringValue( '', true, 'Surname of the record' ) ]
        public string $surname
    )
    {}
}



?>