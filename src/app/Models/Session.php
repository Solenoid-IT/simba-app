<?php



namespace App\Models;



use \Solenoid\MySQL\Model;

use \App\Scopes\ReadScope;



#[ ReadScope( [ 'tenant', 'user' ] ) ]
class Session extends Model
{
    public string $connection_id = 'local';
    public string $database      = 'simba_app';
    public string $table         = 'session';
}



?>