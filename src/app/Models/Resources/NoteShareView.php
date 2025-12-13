<?php



namespace App\Models\Resources;



use \Solenoid\MySQL\Model;

use \App\Scopes\ReadScope;
use \App\Resource;



#[ ReadScope( [ 'tenant', 'user' ] ) ]
#[ Resource(1) ]
class NoteShareView extends Model
{
    public string $connection_id = 'local';
    public string $database      = 'simba_app';
    public string $table         = 'view::note::share';
}



?>