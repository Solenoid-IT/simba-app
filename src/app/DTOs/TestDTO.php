<?php



namespace App\DTOs;



use \Solenoid\X\Data\DTO;
use \Solenoid\X\Data\ArrayList;

use \Solenoid\X\Data\Types\IntValue;
use \Solenoid\X\Data\Types\StringValue;

use \App\DTOs\SubDTO;



class TestDTO extends DTO
{
    public function __construct
    (
        #[ IntValue( '', true, 'ID of the record', 1 ) ]
        public int $id,

        #[ StringValue( '', true, 'Username of the record' ) ]
        public string $username,

        #[ SubDTO( '', true, 'Sub DTO' ) ]
        public SubDTO $sub,

        #[ ArrayList( new IntValue( 'id', true, 'ID of the item record', 1 ) ) ]
        public array $items
    )
    {}
}



?>