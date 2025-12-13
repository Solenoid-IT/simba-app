<?php



namespace App\Models;



use \Solenoid\MySQL\Model;



class TrustedDevice extends Model
{
    public string $connection_id = 'local';
    public string $database      = 'simba_app';
    public string $table         = 'trusted_device';
}



?>