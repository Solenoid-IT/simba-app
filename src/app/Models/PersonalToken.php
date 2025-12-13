<?php



namespace App\Models;



use \Solenoid\MySQL\Model;
use \Solenoid\MySQL\Key;

use \App\Scopes\ReadScope;
use \App\Scopes\WriteScope;



#[ Key( 'name', [ 'tenant', 'name' ] ) ]
#[ ReadScope( [ 'tenant', 'owner' ] ) ]
#[ WriteScope( [ 'tenant', 'owner' ] ) ]
class PersonalToken extends Model
{
    public string $connection_id = 'local';
    public string $database      = 'simba_app';
    public string $table         = 'personal_token';
}



?>