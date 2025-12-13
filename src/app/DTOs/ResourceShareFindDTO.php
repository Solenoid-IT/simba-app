<?php



namespace App\DTOs;



use \Solenoid\X\Data\DTO;
use \Solenoid\X\Data\Types\IntValue;



class ResourceShareFindDTO extends DTO
{
    public function __construct
    (
        #[ IntValue( 'resource', true, 'ID of the resource', 1 ) ]
        public int $resource,

        #[ IntValue( 'element', true, 'ID of the element', 1 ) ]
        public int $element
    )
    {}
}



?>