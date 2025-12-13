<?php



namespace App\Models;



use \Solenoid\MySQL\Model;
use \Solenoid\MySQL\Key;
use \Solenoid\MySQL\Relation;

use \App\Scopes\ReadScope;
use \App\Scopes\WriteScope;

use \App\Models\GroupUser;



#[ Key( 'name', [ 'tenant', 'name' ] ) ]
#[ Relation( 'users', GroupUser::class, Relation::HAS_MANY, foreign_key: 'group' ) ]
#[ ReadScope( [ 'tenant' ] ) ]
#[ WriteScope( [ 'tenant' ], 1 ) ]
class Group extends Model
{
    public string $connection_id = 'local';
    public string $database      = 'simba_app';
    public string $table         = 'group';
}



?>