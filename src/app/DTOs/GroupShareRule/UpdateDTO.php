<?php



namespace App\DTOs\GroupShareRule;



use \Solenoid\X\Data\DTO;

use \Solenoid\X\Data\Types\IntValue;



class UpdateDTO extends DTO
{
    public function __construct
    (
        #[ IntValue( '', true, 'ID of the record', 1 ) ]
        public int $id,

        #[ IntValue( '', true, 'ID of the group', 1 ) ]
        public int $group,

        #[ IntValue( '', true, 'ID of the share_rule', 1 ) ]
        public int $share_rule
    )
    {}
}



?>