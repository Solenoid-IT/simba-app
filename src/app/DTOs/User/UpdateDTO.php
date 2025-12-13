<?php



namespace App\DTOs\User;



use \Solenoid\X\Data\DTO;
use \Solenoid\X\Data\WriteFieldset;

use \Solenoid\X\Data\Types\IntValue;
use \Solenoid\X\Data\Types\StringValue;

use \App\DTOs\User\BirthDataDTO;



#[ WriteFieldset( [ 'hierarchy', 'name', 'birth.name', 'birth.surname', 'datetime.update' ] ) ]
class UpdateDTO extends DTO
{
    public function __construct
    (
        #[ IntValue( '', true, 'ID of the user', 1 ) ]
        public int $id,

        #[ IntValue( '', true, 'Hierarchy of the user', 1, 3 ) ]
        public int $hierarchy,

        #[ StringValue( '', true, 'Name of the user' ) ]
        public string $name,

        public BirthDataDTO $birth
    )
    {}
}



?>