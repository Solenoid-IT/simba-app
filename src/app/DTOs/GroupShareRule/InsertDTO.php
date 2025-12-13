<?php



namespace App\DTOs\GroupShareRule;



use \Solenoid\X\Data\DTO;

use \Solenoid\X\Data\Types\IntValue;



class InsertDTO extends DTO
{
    public function __construct
    (
        #[ IntValue( '', true, 'ID of the group', 1 ) ]
        public int $group,

        #[ IntValue( '', true, 'Type of the resource', 1 ) ]
        public int $resource,

        #[ IntValue( '', true, 'ID of the resource', 1 ) ]
        public int $element ,

        #[ IntValue( '', true, 'ID of the share_rule', 1 ) ]
        public int $share_rule
    )
    {}
}



?>