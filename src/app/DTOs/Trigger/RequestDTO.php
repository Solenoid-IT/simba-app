<?php



namespace App\DTOs\Trigger;



use \Solenoid\X\Data\DTO;

use \Solenoid\X\Data\Types\StringValue;



class RequestDTO extends DTO
{
    public function __construct
    (
        #[ StringValue( '', false, 'Method of the request', '/^[A-Z]+$/' ) ]
        public string $method,

        #[ StringValue( '', false, 'URL of the request' ) ]
        public string $url,

        #[ StringValue( '', false, 'Body of the request' ) ]
        public string $content
    )
    {}
}



?>