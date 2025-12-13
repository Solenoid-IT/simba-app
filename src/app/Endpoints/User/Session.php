<?php



namespace App\Endpoints\User;



use \Solenoid\X\HTTP\Session as HttpSession;

use \Solenoid\X\Data\Input;
use \Solenoid\X\Data\ArrayList;
use \Solenoid\X\Data\Types\StringValue;
use \Solenoid\X\Middleware;

use \App\Middlewares\RequestToken as RequestTokenMiddleware;

use \App\Services\Session as SessionService;



class Session
{
    private object         $user;
    private SessionService $service;



    public function __construct (SessionService $service)
    {
        // (Getting the value)
        $this->user = store()->get( 'user' );



        // (Getting the value)
        $this->service = $service;
    }



    #[ Middleware( RequestTokenMiddleware::class ) ]
    #[ Input( new ArrayList( new StringValue( 'id', true, 'ID of the record' ) ) ) ]
    public function delete (array $ids)
    {
        // Returning the value
        return $this->service->user( $this->user )->elements( $ids )->delete();
    }
}



?>