<?php



namespace App\DTOs\UserShareRule;



use \Solenoid\X\Data\DTO;

use \Solenoid\X\Data\Types\IntValue;



class UpdateDTO extends DTO
{
    public function __construct
    (
        #[ IntValue( '', true, 'ID of the record', 1 ) ]
        public int $id,

        #[ IntValue( '', true, 'ID of the user', 1 ) ]
        public int $user,

        #[ IntValue( '', true, 'ID of the share_rule', 1 ) ]
        public int $share_rule
    )
    {}
}



?>