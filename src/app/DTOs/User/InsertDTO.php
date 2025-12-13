<?php



namespace App\DTOs\User;



use \Solenoid\X\Data\DTO;
use \Solenoid\X\Data\WriteFieldset;

use \Solenoid\X\Data\Types\IntValue;
use \Solenoid\X\Data\Types\StringValue;

use \App\DTOs\User\BirthDataDTO;



#[ WriteFieldset( [ 'tenant', 'owner', 'hierarchy', 'name', 'email', 'birth.name', 'birth.surname', 'datetime.insert' ] ) ]
class InsertDTO extends DTO
{
    public function __construct
    (
        #[ IntValue( '', true, 'Hierarchy of the user', 1, 3 ) ]
        public int $hierarchy,

        #[ StringValue( '', true, 'Name of the user' ) ]
        public string $name,

        #[ StringValue( '', true, 'Email of the user', '/^[^\@]+\@[^\@]+$/' ) ]
        public string $email,

        public BirthDataDTO $birth
    )
    {}
}



?>