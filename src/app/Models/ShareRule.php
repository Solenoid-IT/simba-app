<?php



namespace App\Models;



use \Solenoid\MySQL\Model;



class ShareRule extends Model
{
    public string $connection_id = 'local';
    public string $database      = 'simba_app';
    public string $table         = 'share_rule';
}



?>