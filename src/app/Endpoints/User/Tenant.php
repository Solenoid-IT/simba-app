<?php



namespace App\Endpoints\User;



use \Solenoid\X\Data\Input;
use \Solenoid\X\Data\Types\StringValue;

use \Solenoid\X\Middleware;

use \App\User;

use \App\Middlewares\RequestToken as RequestTokenMiddleware;

use \App\Services\Tenant as TenantService;



class Tenant
{
    private User          $user;
    private TenantService $service;



    public function __construct (TenantService $service)
    {
        // (Getting the value)
        $this->user = store()->get( 'user' );



        // (Getting the value)
        $this->service = $service;
    }



    #[ Middleware( RequestTokenMiddleware::class ) ]
    #[ Input( new StringValue( 'name', true, 'Name of the tenant' ) ) ]
    public function set_name (string $name)
    {
        // (Setting the field)
        $this->service->user( $this->user )->element( $this->user->tenant )->set_field( 'name', $name );
    }
}



?>