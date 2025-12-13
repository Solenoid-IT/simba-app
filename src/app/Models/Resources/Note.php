<?php



namespace App\Models\Resources;



use \Solenoid\MySQL\Model;
use \Solenoid\MySQL\Key;

use \App\Scopes\WriteScope;
use \App\Resource;



#[ Key( 'name', [ 'tenant', 'name' ] ) ]
#[ WriteScope( [ 'tenant' ] ) ]
#[ Resource( 1 ) ]
class Note extends Model
{
    public string $connection_id = 'local';
    public string $database      = 'simba_app';
    public string $table         = 'note';
}



?>