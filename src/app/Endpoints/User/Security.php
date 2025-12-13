<?php



namespace App\Endpoints\User;



use \Solenoid\X\HTTP\Session;

use \App\Services\Session as SessionService;
use \App\Services\TrustedDevice as TrustedDeviceService;
use \App\Services\PersonalToken as PersonalTokenService;



class Security
{
    private object  $user;
    private Session $session;



    public function __construct ()
    {
        // (Getting the value)
        $this->user = store()->get( 'user' );



        // (Getting the value)
        $this->session = session();
    }



    public function fetch ()
    {
        if ( !$this->session->start() )
        {// (Unable to start the session)
            // Throwing the exception
            throw error( 6000, 'Unable to start the session' );
        }



        // (Getting the value)
        $session_id = $this->session->id;

        if ( !$this->session->close() )
        {// (Unable to close the session)
            // Throwing the exception
            throw error( 6000, 'Unable to close the session' );
        }



        // (Getting the value)
        $data['sessions'] = ( new SessionService() )->list_index( $session_id );



        // (Getting the value)
        $data['trusted_devices'] = ( new TrustedDeviceService() )->list_index( user(), [ 'id', 'name', 'ua_info.browser', 'ua_info.os', 'ua_info.hw', 'datetime.insert' ] );



        // (Getting the value)
        $data['personal_tokens'] = ( new PersonalTokenService() )->list_index( [ 'id', 'name', 'token', 'datetime.insert', 'datetime.update' ] );



        // Returning the value
        return response()->json( 200, $data );
    }
}



?>