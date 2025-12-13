<?php



namespace App\DTOs\User;



use \Solenoid\X\Data\DTO;
use \Solenoid\X\Data\Types\IntValue;
use \Solenoid\X\Data\Types\StringValue;
use \Solenoid\X\Data\WriteFieldset;



#[ WriteFieldset( [ 'email', 'datetime.update' ] ) ]
class SetEmailDTO extends DTO
{
    public function __construct
    (
        #[ IntValue( '', true, 'ID of the user', 1 ) ]
        public int $id ,

        #[ StringValue( '', true, 'Email of the user', '/^[^\@]+\@[^\@]+$/' ) ]
        public string $email
    )
    {}
}



?>