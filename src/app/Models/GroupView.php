<?php



namespace App\Models;



use \Solenoid\MySQL\Model;

use \App\Scopes\ReadScope;



#[ ReadScope( [ 'tenant' ] ) ]
class GroupView extends Model
{
    public string $connection_id = 'local';
    public string $database      = 'simba_app';
    public string $table         = 'view::group::all';
}



?>