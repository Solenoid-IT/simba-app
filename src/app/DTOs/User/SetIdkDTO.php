<?php



namespace App\DTOs\User;



use \Solenoid\X\Data\DTO;
use \Solenoid\X\Data\WriteFieldset;

use \Solenoid\X\Data\Types\BoolValue;
use \Solenoid\X\Data\Types\StringValue;



#[ WriteFieldset( [ 'authentication' => 'security.idk.authentication', 'public_key' => 'security.idk.public_key', 'enc_nonce' => 'security.idk.enc_nonce', 'datetime.update' ] ) ]
class SetIdkDTO extends DTO
{
    public function __construct
    (
        #[ BoolValue( 'security.idk.authentication', true, 'Whether to enable IDK authentication' ) ]
        public bool $authentication,

        #[ StringValue( 'security.idk.public_key', false, 'Public key of the user' ) ]
        public ?string $public_key = null,

        #[ StringValue( 'security.idk.enc_nonce', false, 'Encryption nonce of the user' ) ]
        public ?string $enc_nonce = null
    )
    {}
}



?>