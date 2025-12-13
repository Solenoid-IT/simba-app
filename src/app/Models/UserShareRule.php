<?php



namespace App\Models;



use \Solenoid\MySQL\Model;



class UserShareRule extends Model
{
    public string $connection_id = 'local';
    public string $database      = 'simba_app';
    public string $table         = 'user_share_rule';
}



?>