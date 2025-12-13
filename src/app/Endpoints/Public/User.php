<?php



namespace App\Endpoints\Public;



use \Solenoid\X\Data\Input;
use \Solenoid\X\Data\Types\StringValue;

use \Solenoid\X\Middleware;

use \App\Models\Tenant as TenantModel;
use \App\Models\User as UserModel;

use \App\Services\User as UserService;

use \App\Middlewares\LoginRateLimiter as LoginRateLimiterMiddleware;



class User
{
    private UserService $service;



    public function __construct (UserService $service)
    {
        // (Getting the value)
        $this->service = $service;
    }



    #[ Middleware( LoginRateLimiterMiddleware::class ) ]
    public function login ()
    {
        // Returning the value
        return $this->service->login( request()->json( true ), session() );
    }

    #[ Middleware( LoginRateLimiterMiddleware::class ) ]
    public function login_via_idk ()
    {
        // Returning the value
        return $this->service->login_via_idk( request()->body, session() );
    }

    public function login_via_authorization ()
    {
        // Returning the value
        return $this->service->login_via_authorization( session(), 180 );
    }



    #[ Input( new StringValue( 'email', true, 'Email of the user', '/^[^\@]+\@[^\@]+$/' ) ) ]
    public function request_quick_access (string $email)
    {
        // Returning the value
        return $this->service->request_quick_access( $email, session() );
    }



    public function fetch_name ()
    {
        // (Setting the value)
        $names = [];



        // (Getting the value)
        $input = request()->json( true );

        foreach ( $input as $uuid )
        {// Processing each entry
            // (Getting the value)
            $user = UserModel::fetch()->where( 'uuid', $uuid )->find();

            if ( !$user )
            {// (Record not found)
                // Throwing the exception
                throw error( 6300, 'User record not found' );
            }



            // (Getting the value)
            $tenant = TenantModel::fetch()->where( 'id', $user->tenant )->find();

            if ( !$tenant )
            {// (Record not found)
                // Throwing the exception
                throw error( 6300, 'Tenant record not found' );
            }



            // (Getting the value)
            $names[ $user->id ] =
            [
                'uuid'   => $user->uuid,

                'name'   => $user->name,
                'tenant' => $tenant->name
            ]
            ;
        }



        // Returning the value
        return response()->json( 200, $names );
    }
}



?>