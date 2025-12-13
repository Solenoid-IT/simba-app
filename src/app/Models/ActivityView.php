<?php



namespace App\Models;



use \Solenoid\MySQL\Model;

use \App\Scopes\ReadScope;



#[ ReadScope( [ 'tenant' ], 1 ) ]
#[ ReadScope( [ 'tenant', 'user' ] ) ]
class ActivityView extends Model
{
    public string $connection_id = 'local';
    public string $database      = 'simba_app';
    public string $table         = 'view::activity::all';
}



?>