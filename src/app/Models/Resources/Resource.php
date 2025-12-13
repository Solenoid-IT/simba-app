<?php



namespace App\Models\Resources;



use \Solenoid\MySQL\Model;



class Resource extends Model
{
    public string $connection_id = 'local';
    public string $database      = 'simba_app';
    public string $table         = 'resource';
}



?>