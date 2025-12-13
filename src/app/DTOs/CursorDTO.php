<?php



namespace App\DTOs;



use \Solenoid\X\Data\DTO;
use \Solenoid\X\Data\Types\IntValue;
use \Solenoid\X\Data\Types\AnyValue;



class CursorDTO extends DTO
{
    public function __construct
    (
        #[ IntValue( 'id', false, 'ID', 0 ) ]
        public int $lastId,

        #[ AnyValue( 'sortValue', false, 'Sorting value', [ 'int', 'float', 'string' ] ) ]
        public mixed $lastSortValue
    )
    {}
}



?>