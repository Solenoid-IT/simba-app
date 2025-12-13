<?php



namespace App\Models;



use \Solenoid\MySQL\Model;
use \Solenoid\MySQL\Key;
use \Solenoid\MySQL\TypeCast;

use \App\Scopes\ReadScope;
use \App\Scopes\WriteScope;



#[ Key( 'range', [ 'tenant', 'range' ] ) ]
#[ ReadScope( [ 'tenant' ] ) ]
#[ WriteScope( [ 'tenant' ], 1 ) ]
#[ TypeCast( [ 'allowed' => 'bool' ] ) ]
class FirewallRule extends Model
{
    public string $connection_id = 'local';
    public string $database      = 'simba_app';
    public string $table         = 'firewall_rule';
}



?>