<?php



namespace App\Models;



use \Solenoid\MySQL\Model;
use \Solenoid\MySQL\Key;
use \Solenoid\MySQL\Relation;

use \App\Models\Hierarchy;

use \App\Scopes\ReadScope;
use \App\Scopes\WriteScope;



#[ Key( 'name', [ 'tenant', 'name' ] ) ]
#[ Relation( 'hierarchy', Hierarchy::class, Relation::BELONGS_TO ) ]
#[ ReadScope( [ 'tenant' ] ) ]
#[ WriteScope( [ 'tenant' ], 1 ) ]
#[ WriteScope( [ 'tenant', 'id' ] ) ]
class User extends Model
{
    public string $connection_id = 'local';
    public string $database      = 'simba_app';
    public string $table         = 'user';
}



?>