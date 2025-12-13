<?php



namespace App\Models;



use \Solenoid\MySQL\Model;
use \Solenoid\MySQL\TypeCast;

use \App\Scopes\ReadScope;



#[ ReadScope( [ 'tenant' ] ) ]
#[ TypeCast( [ 'allowed' => 'bool' ] ) ]
class FirewallRuleView extends Model
{
    public string $connection_id = 'local';
    public string $database      = 'simba_app';
    public string $table         = 'view::firewall_rule::all';
}



?>