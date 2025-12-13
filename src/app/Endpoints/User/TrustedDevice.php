<?php



namespace App\Endpoints\User;



use \Solenoid\X\HTTP\Session;

use \Solenoid\X\Data\Input;
use \Solenoid\X\Data\Types\StringValue;
use \Solenoid\X\Data\ArrayList;
use \Solenoid\X\Middleware;

use \App\Middlewares\RequestToken as RequestTokenMiddleware;

use \App\DTOs\TrustedDevice\UpdateDTO;

use \App\Services\TrustedDevice as TrustedDeviceService;



class TrustedDevice
{
    private object               $user;
    private Session              $session;
    private TrustedDeviceService $service;



    public function __construct (TrustedDeviceService $service)
    {
        // (Getting the value)
        $this->user = store()->get( 'user' );



        // (Getting the value)
        $this->session = session();



        // (Getting the value)
        $this->service = $service;
    }



    #[ Middleware( RequestTokenMiddleware::class ) ]
    #[ Input( new StringValue( 'name', true, 'Name of the trusted device' ) ) ]
    public function insert (string $name)
    {
        // Returning the value
        return $this->service->insert( $this->user, $name, $this->session );
    }

    #[ Middleware( RequestTokenMiddleware::class ) ]
    #[ Input( new ArrayList( new StringValue( 'id', true, 'ID of the trusted device' ) ) ) ]
    public function delete (array $ids)
    {
        // Returning the value
        return $this->service->delete( $this->user, $ids, $this->session );
    }



    #[ Middleware( RequestTokenMiddleware::class ) ]
    #[ Input( UpdateDTO::class ) ]
    public function set_name (UpdateDTO $dto)
    {
        // Returning the value
        return $this->service->set_name( $this->user, $dto, $this->session );
    }
}



?>