<?php



namespace App\DTOs\UserShareRule;



use \Solenoid\X\Data\DTO;

use \Solenoid\X\Data\Types\IntValue;



class InsertDTO extends DTO
{
    public function __construct
    (
        #[ IntValue( '', true, 'ID of the user', 1 ) ]
        public int $user,

        #[ IntValue( '', true, 'Type of the resource', 1 ) ]
        public int $resource,

        #[ IntValue( '', true, 'ID of the resource', 1 ) ]
        public int $element,

        #[ IntValue( '', true, 'ID of the share_rule', 1 ) ]
        public int $share_rule
    )
    {}
}



?>