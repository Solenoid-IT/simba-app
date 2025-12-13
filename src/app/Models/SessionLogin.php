<?php



namespace App\Models;



use \Solenoid\MySQL\Model;



class SessionLogin extends Model
{
    public string $connection_id = 'local';
    public string $database      = 'simba_app';
    public string $table         = 'session_login';
}



?>