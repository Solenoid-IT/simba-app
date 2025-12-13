<?php



namespace App\Models;



use \Solenoid\MySQL\Model;

use \App\Scopes\ReadScope;



#[ ReadScope( [ 'owner' => 'session.user' ] ) ]
class SessionLoginView extends Model
{
    public string $connection_id = 'local';
    public string $database      = 'simba_app';
    public string $table         = 'view::session_login::active_session';
}



?>