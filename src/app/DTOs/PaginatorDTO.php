<?php



namespace App\DTOs;



use \Solenoid\X\Data\DTO;

use \Solenoid\X\Data\Types\IntValue;
use \Solenoid\X\Data\Types\StringValue;
use \Solenoid\X\Data\Types\AnyValue;
use \Solenoid\X\Data\Types\EnumValue;

use \App\DTOs\CursorDTO;



class PaginatorDTO extends DTO
{
    public function __construct
    (
        #[ IntValue( 'length', true, 'Number of records to fetch', 1 ) ]
        public int $length,

        public CursorDTO $cursor,

        #[ StringValue( 'globalSearch', false, 'Global search value' ) ]
        public string $globalSearch = '',

        #[ AnyValue( 'localSearch', false, 'Local search values' ) ]
        public mixed $localSearch = null,

        #[ EnumValue( 'sort', false, 'Sorting directions', [ 'ASC', 'DESC' ] ) ]
        public string $sort = 'ASC',

        #[ StringValue( 'sortField', false, 'Sorting field' ) ]
        public ?string $sortField = null
    )
    {}
}



?>