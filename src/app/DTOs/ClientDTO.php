<?php



namespace App\DTOs;



use \Solenoid\X\Data\DTO;
use \Solenoid\X\Data\Types\StringValue;



class ClientDTO extends DTO
{
    public function __construct
    (
        #[ StringValue( '', true, 'IP Address' ) ]
        public string $ip,

        #[ StringValue( '', true, 'User Agent' ) ]
        public string $ua
    )
    {}
}



?>