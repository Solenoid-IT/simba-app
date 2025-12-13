<?php



namespace App\Endpoints\User;



use \Solenoid\X\HTTP\Session;

use \Solenoid\X\Data\Input;
use \Solenoid\X\Data\Types\IntValue;
use \Solenoid\X\Data\Types\StringValue;
use \Solenoid\X\Data\Types\BoolValue;
use \Solenoid\X\Data\ArrayList;
use \Solenoid\X\Middleware;

use \App\User as AppUser;

use \App\Middlewares\RequestToken as RequestTokenMiddleware;

use \App\Models\Tenant as TenantModel;
use \App\Models\User as UserModel;
use \App\Models\Activity as ActivityModel;

use \App\DTOs\PaginatorDTO;
use \App\DTOs\User\SetEmailDTO;
use \App\DTOs\User\BirthDataDTO;
use \App\DTOs\User\SetIdkDTO;
use \App\DTOs\User\UpdateDTO;
use \App\DTOs\User\InsertDTO;

use \App\Services\User as UserService;
use \App\Services\Group as GroupService;
use \App\Services\Hierarchy as HierarchyService;
use \App\Services\Session as SessionService;
use \App\Services\TrustedDevice as TrustedDeviceService;
use \App\Services\PersonalToken as PersonalTokenService;
use \App\Services\ShareRule as ShareRuleService;



class User
{
    private AppUser     $user;
    private Session     $session;
    private UserService $service;



    public function __construct (UserService $service)
    {
        // (Getting the value)
        $this->user = store()->get( 'user' );



        // (Getting the value)
        $this->session = session();



        // (Getting the value)
        $this->service = $service;
    }



    #[ Input( new IntValue( 'ID', true, 'ID of the user' ) ) ]
    public function find (int $id)
    {
        // (Getting the value)
        $element = $this->service->element( $id )->find( [ 'id', 'name', 'hierarchy', 'email', 'birth.name', 'birth.surname' ] );

        if ( !$element )
        {// (Record not found)
            // Throwing the exception
            throw error( 6300, "Entity '{$this->service->model_name}':: Record not found" );
        }



        // Returning the value
        return $element;
    }

    #[ Input( PaginatorDTO::class ) ]
    public function list (PaginatorDTO $paginator)
    {
        // Returning the value
        return $this->service->paginate( $paginator )->list( [ 'id', 'name', 'hierarchy', 'email', 'birth.name', 'birth.surname', 'datetime.insert', 'datetime.update', 'ref.hierarchy.name' ] );
    }



    #[ Middleware( RequestTokenMiddleware::class ) ]
    #[ Input( UpdateDTO::class ) ]
    public function update (UpdateDTO $input)
    {
        // (Updating the element)
        $this->service->element( $input->id )->update( $input );
    }

    #[ Input( InsertDTO::class ) ]
    public function insert (InsertDTO $input)
    {
        // (Inserting the element via authorization)
        $this->service->insert_via_authorization( $input );
    }

    #[ Middleware( RequestTokenMiddleware::class ) ]
    #[ Input( new ArrayList( new IntValue( 'id', true, 'ID of the user', 1 ) ) ) ]
    public function delete (array $ids)
    {
        // (Deleting the elements)
        $this->service->elements( $ids )->delete();
    }



    #[ Middleware( RequestTokenMiddleware::class ) ]
    #[ Input( SetEmailDTO::class ) ]
    public function set_email (SetEmailDTO $input)
    {
        // (Setting the email via authorization)
        $this->service->user( $this->user )->element( $input->id )->set_email_via_authorization( $input->email );
    }



    public function fetch (UserService $user_service, GroupService $group_service, HierarchyService $hierarchy)
    {
        // (Getting the value)
        $user = model( UserModel::class )->where( 'id', $this->session->data['user'] )->find();

        if ( !$user )
        {// (Record not found)
            // Throwing the exception
            throw error( 6300, 'Session user not found' );
        }



        // (Getting the value)
        $tenant = model( TenantModel::class )->where( 'id', $user->tenant )->find();

        if ( !$tenant )
        {// (Record not found)
            // Throwing the exception
            throw error( 6300, 'Session user tenant not found' );
        }



        // (Getting the value)
        $data =
        [
            'id'            => $user->id,

            'name'          => $user->name,

            'email'         => $user->email,

            'hierarchy'     => $user->hierarchy,

            'birth'         =>
            [
                'name'      => $user->get( 'birth.name' ),
                'surname'   => $user->get( 'birth.surname' )
            ],

            'tenant'            =>
            [
                'name'          => $tenant->name
            ],

            'password_set'      => $user->get( 'security.password' ) !== null,

            'mfa'               => $user->get( 'security.mfa' ) === 1,

            'idk'               => $user->get( 'security.idk.authentication' ) === 1,

            'idk_forced'        => $user->get( 'security.idk.forced' ) === 1,

            'trusted_device'    => $user->get( 'security.trusted_device' ) === 1,

            'ppk'               => $user->get( 'security.ppk.datetime.update' ) ? true : false,

            'personal_key'      => $user->get( 'security.personal_key.public_key' ) ? true : false,

            'public_key'        => $user->get( 'security.personal_key.public_key' ),

            'uuid'              => $user->uuid
        ]
        ;



        // (Setting the value)
        $data['alerts'] = [];
        
        foreach ( model( ActivityModel::class )->where( [ ( $user->hierarchy === 1 ? [ 'tenant', $user->tenant ] : [ 'user', $user->id ] ), [ 'alert_severity', 'IS NOT', null ], [ 'datetime.alert.read', 'IS', null ] ] )->list() as $record )
        {// Processing each entry
            // (Getting the value)
            $data['alerts'][ $record->id ] = $record->values;
        }



        // (Setting the value)
        $data['alert_severities'] = [];

        foreach ( alert_severities() as $severity )
        {// Processing each entry
            // (Getting the value)
            $data['alert_severities'][ $severity['level'] ] = $severity;
        }



        // (Getting the value)
        $data['hierarchies'] = $hierarchy->list_index();



        // (Getting the value)
        $data['users'] = $user_service->list_index();



        // (Getting the value)
        $data['groups'] = $group_service->list_index();



        if ( !$this->session->start() )
        {// (Unable to start the session)
            // Throwing the exception
            throw error( 6000, 'Unable to start the session' );
        }



        // (Getting the value)
        $request_token = request_token()->employ();



        if ( !$this->session->write() )
        {// (Unable to write to the session)
            // Throwing the exception
            throw error( 6000, 'Unable to write to the session' );
        }



        // (Getting the value)
        $data['sessions'] = ( new SessionService() )->list_index( $this->session->id );



        // (Getting the value)
        $data['trusted_devices'] = ( new TrustedDeviceService() )->list_index( $user, [ 'id', 'name', 'ua_info.browser', 'ua_info.os', 'ua_info.hw', 'datetime.insert' ] );


        // (Getting the value)
        $data['personal_tokens'] = ( new PersonalTokenService() )->list_index( [ 'id', 'name', 'token', 'datetime.insert', 'datetime.update' ] );



        // (Storing the request token)
        $data['request_token'] = $request_token;



        // (Getting the value)
        $data['add_trusted_device'] = $this->session->data['add_trusted_device'] ?? false;



        // (Removing the element)
        unset( $this->session->data['add_trusted_device'] );



        // (Getting the value)
        $data['share_rules'] = ( new ShareRuleService() )->list_index();



        // Returning the value
        return response()->json( 200, $data );
    }



    #[ Middleware( RequestTokenMiddleware::class ) ]
    #[ Input( new StringValue( 'name', true, 'Name of the user' ) ) ]
    public function set_own_name (string $name)
    {
        // (Setting the field)
        $this->service->user( $this->user )->element( $this->user->id )->set_field( 'name', $name );
    }

    #[ Middleware( RequestTokenMiddleware::class ) ]
    #[ Input( new StringValue( 'email', true, 'Email of the user', '/^[^\@]+\@[^\@]+$/' ) ) ]
    public function set_own_email (string $email)
    {
        // (Setting the email via authorization)
        $this->service->user( $this->user )->element( $this->user->id )->set_email_via_authorization( $email );
    }

    #[ Middleware( RequestTokenMiddleware::class ) ]
    #[ Input( BirthDataDTO::class ) ]
    public function set_own_birth_data (BirthDataDTO $input)
    {
        // (Setting the birth data)
        $this->service->element( $this->user->id )->set_birth_data( $input );
    }



    #[ Middleware( RequestTokenMiddleware::class ) ]
    #[ Input( new StringValue( 'password', true, 'Password of the user' ) ) ]
    public function set_own_password (string $password)
    {
        // (Setting the password)
        $this->service->user( $this->user )->element( $this->user->id )->set_password( 'security.password', $password );
    }



    #[ Middleware( RequestTokenMiddleware::class ) ]
    #[ Input( new BoolValue( 'mfa', true, 'Enable or disable MFA' ) ) ]
    public function set_own_mfa (bool $enabled)
    {
        // (Setting the mfa)
        $this->service->user( $this->user )->element( $this->user->id )->set_mfa( 'security.mfa', $enabled );
    }

    #[ Middleware( RequestTokenMiddleware::class ) ]
    #[ Input( new BoolValue( 'trusted_device', true, 'Enable or disable MFA trusted device' ) ) ]
    public function set_own_mfa_trusted_device (bool $enabled)
    {
        // (Setting the mfa trusted device)
        $this->service->user( $this->user )->element( $this->user->id )->set_mfa_trusted_device( 'security.trusted_device', $enabled );
    }



    #[ Middleware( RequestTokenMiddleware::class ) ]
    #[ Input( SetIdkDTO::class ) ]
    public function set_own_idk (SetIdkDTO $input)
    {
        // (Setting the idk)
        $this->service->user( $this->user )->element( $this->user->id )->set_idk( $input );
    }

    #[ Middleware( RequestTokenMiddleware::class ) ]
    #[ Input( new BoolValue( 'idk_forced', true, 'Enable or disable IDK forced authentication' ) ) ]
    public function set_own_idk_forced (bool $enabled)
    {
        // (Setting the idk forced)
        $this->service->user( $this->user )->element( $this->user->id )->set_idk_forced( 'security.idk.forced', $enabled );
    }



    public function logout ()
    {
        // (Doing the logout)
        $this->service->user( $this->user )->logout();
    }



    #[ Middleware( RequestTokenMiddleware::class ) ]
    #[ Input( new IntValue( 'user_id', true, 'ID of the user', 1 ) ) ]
    public function reset_account (int $user_id)
    {
        // (Resetting the account)
        $this->service->user( $this->user )->element( $user_id )->reset_account();
    }

    #[ Middleware( RequestTokenMiddleware::class ) ]
    #[ Input( new ArrayList( new IntValue( 'activity_ids', true, 'ID of the activity', 1 ) ) ) ]
    public function mark_alert_as_read (array $activity_ids)
    {
        // (Marking the alert as read)
        $this->service->mark_alert_as_read( $this->user, $activity_ids );
    }



    public function list_index ()
    {
        // Returning the value
        return $this->service->list_index();
    }
}



?>