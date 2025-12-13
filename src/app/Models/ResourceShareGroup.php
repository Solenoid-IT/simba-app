<?php



namespace App\Models;



use \Solenoid\MySQL\Model;



class ResourceShareGroup extends Model
{
    public string $connection_id = 'local';
    public string $database      = 'simba_app';
    public string $table         = 'resource_share_group';
}



?>