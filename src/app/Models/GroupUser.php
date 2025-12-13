<?php



namespace App\Models;



use \Solenoid\MySQL\Model;



class GroupUser extends Model
{
    public string $connection_id = 'local';
    public string $database      = 'simba_app';
    public string $table         = 'group_user';
}



?>