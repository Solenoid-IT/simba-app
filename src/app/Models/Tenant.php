<?php



namespace App\Models;



use \Solenoid\MySQL\Model;

use \App\Scopes\WriteScope;



#[ WriteScope( [ 'tenant' => 'id' ], 1 ) ]
class Tenant extends Model
{
    public string $connection_id = 'local';
    public string $database      = 'simba_app';
    public string $table         = 'tenant';
}



?>