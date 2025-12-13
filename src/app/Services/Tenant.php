<?php



namespace App\Services;



use \App\Entity;

use \App\Models\Tenant as TenantModel;



class Tenant extends Entity
{
    public function __construct ()
    {
        // (Calling the function)
        parent::__construct( TenantModel::class );
    }
}



?>