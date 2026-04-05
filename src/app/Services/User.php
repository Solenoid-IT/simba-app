<?php



namespace App\Services;



use \Solenoid\X\HTTP\Session;
use \Solenoid\X\Stream\ReadableStream;

use \Solenoid\MySQL\DateTime;

use \Solenoid\Encryption\RSA;

use \App\User as AppUser;
use \App\Entity;

use \App\Models\User as UserModel;
use \App\Models\UserView as UserViewModel;
use \App\Models\Tenant as TenantModel;
use \App\Models\SessionLogin as SessionLoginModel;
use \App\Models\Session as SessionModel;
use \App\Models\Activity as ActivityModel;
use \App\Models\TrustedDevice as TrustedDeviceModel;

use \App\DTOs\User\BirthDataDTO;
use \App\DTOs\User\SetIdkDTO;
use \App\DTOs\User\UpdateDTO;
use \App\DTOs\User\InsertDTO;

use \App\Services\Client as ClientService;
use \App\Services\Trigger as TriggerService;
use \App\Services\Login as LoginService;
use \App\Services\Operation as OperationService;



class User extends Entity
{
    public function __construct ()
    {
        // (Calling the function)
        parent::__construct( UserModel::class );
    }



    public function list (array $fields = []) : array
    {
        // (Getting the value)
        $entity = entity( UserViewModel::class );

        if ( $this->paginator )
        {// Value found
            // (Composing the entity)
            $entity->paginate( $this->paginator );
        }



        // Returning the value
        return $entity->list( $fields );
    }



    public function insert_via_authorization (InsertDTO $input) : void
    {
        if ( model( UserModel::class )->where( [ [ 'tenant', $this->user->tenant ], [ 'name', $input->name ] ] )->exists() )
        {// (Record found)
            // Throwing the exception
            throw error( 6301, "Entity '{$this->model_name}' :: Record key ['tenant','name'] already exists" );
        }

        if ( model( UserModel::class )->where( 'email', $input->email )->exists() )
        {// (Record found)
            // Throwing the exception
            throw error( 6301, "Entity '{$this->model_name}' :: Record key ['email'] already exists" );
        }



        // (Getting the value)
        $operation_service = new OperationService();



        // (Getting the value)
        $data =
        [
            'user'      => $this->user->id,
            'email'     => $input->email,

            'record'    => $input,

            'ip'        => ip(),
            'ua'        => ua(),

            'last_step' => false
        ]
        ;



        // (Getting the value)
        $opid = $operation_service->insert
        (
            name:         'User creation',
            task:         'API/User.insert',
            data:         json_encode( $data ),
            display:      "User <b>{$this->user->name}</b> has been invited via email <b>{$input->email}</b>",
            login:        null,
            callback_url: null,
            duration:     (int) env( 'BASE_OP_DURATION' )
        )
        ;



        // (Notifying the user)
        $operation_service->notify( $this->user->email, $opid, $data['ip'], $data['ua'] );
    }



    public function set_email_via_authorization (string $email) : void
    {
        if ( model( UserModel::class )->where( 'email', $email )->exists() )
        {// (Record found)
            // Throwing the exception
            throw error( 6301, "Entity '{$this->model_name}' :: Record key ['email'] already exists" );
        }



        // (Getting the value)
        $operation_service = new OperationService();



        // (Getting the value)
        $data =
        [
            'user'      => $this->element_id,
            'email'     => $email,

            'ip'        => ip(),
            'ua'        => ua(),

            'last_step' => false
        ]
        ;



        // (Getting the value)
        $opid = $operation_service->insert
        (
            name:         'Email update',
            task:         'API/User.set_email',
            data:         json_encode( $data ),
            display:      "Confirm operation by email <b>{$email}</b>",
            login:        null,
            callback_url: null,
            duration:     (int) env( 'BASE_OP_DURATION' )
        )
        ;



        // (Getting the value)
        $operation_service->notify( $this->user->email, $opid, $data['ip'], $data['ua'] );
    }

    public function set_birth_data (BirthDataDTO $input) : void
    {
        // (Updating the element)
        parent::log( "User <b>{$this->user->name}</b> has been updated :: Set birth fullname to <b>{$input->name} {$input->surname}</b>" )->update( $input );
    }



    public function set_password (string $field, mixed $value) : void
    {
        // (Setting the activity description)
        $this->log( "User <b>{$this->user->name}</b> has changed his password" );



        // (Setting the field)
        parent::set_field( $field, password_hash( $value, PASSWORD_BCRYPT ) );
    }



    public function set_mfa (string $field, bool $enabled) : void
    {
        // (Setting the activity description)
        $this->log( "User <b>{$this->user->name}</b> has " . ( $enabled ? 'enabled' : 'disabled' ) . ' MFA' );


        // (Setting the field)
        parent::set_field( $field, $enabled ? 1 : 0 );
    }

    public function set_mfa_trusted_device (string $field, bool $enabled) : void
    {
        // (Setting the activity description)
        $this->log( "User <b>{$this->user->name}</b> has " . ( $enabled ? 'enabled' : 'disabled' ) . ' trusted device policy for MFA' );



        // (Setting the field)
        parent::set_field( $field, $enabled ? 1 : 0 );
    }



    public function set_idk (SetIdkDTO $input) : void
    {
        // (Setting the activity description)
        $this->log( "User <b>{$this->user->name}</b> has " . ( $input->get( 'authentication' ) ? 'enabled' : 'disabled' ) . ' IDK authentication' );



        // (Updating the element)
        parent::update( $input );
    }

    public function set_idk_forced (string $field, bool $enabled) : void
    {
        // (Setting the activity description)
        $this->log( "User <b>{$this->user->name}</b> has " . ( $enabled ? 'enabled' : 'disabled' ) . ' forced IDK authentication' );



        // (Setting the field)
        parent::set_field( $field, $enabled ? 1 : 0 );
    }



    public function logout () : void
    {
        // (Setting the value)
        $this->user->session->data = [];



        if ( !$this->user->session->destroy() )
        {// (Unable to destroy the session)
            // Throwing the exception
            throw error( 6000, 'Unable to destroy the session' );
        }



        // (Getting the values)
        $ip = ip();
        $ua = ua();



        // (Getting the value)
        $client = ClientService::detect( $ip, $ua );

        if ( !$client )
        {// (Unable to detect the client)
            // Throwing the exception
            throw error( 6402, 'Unable to detect the client' );
        }



        // (Getting the value)
        $class_method = __FUNCTION__;



        // (Getting the value)
        $action = "{$this->model_name}.$class_method";



        // (Getting the value)
        $record =
        [
            'tenant'               => $this->user->tenant,
            'user'                 => $this->user->id,
            'action'               => $action,
            'description'          => "{$this->model_name} <b>{$this->user->name}</b> has logged out",
            'session'              => null,
            'ip'                   => $ip,
            'user_agent'           => $ua,
            'ip_info.country.code' => $client['ip']['country']['code'],
            'ip_info.country.name' => $client['ip']['country']['name'],
            'ip_info.isp'          => $client['ip']['isp'],
            'ua_info.browser'      => $client['ua']['browser'],
            'ua_info.os'           => $client['ua']['os'],
            'ua_info.hw'           => $client['ua']['hw'],
            'resource.type'        => $this->model_name,
            'resource.action'      => $class_method,
            'datetime.insert'      => DateTime::fetch()
        ]
        ;

        if ( !model( ActivityModel::class )->insert( [ $record ] ) )
        {// (Unable to insert the record)
            // Throwing the exception
            throw error( 6302, "Entity 'Activity' :: Unable to insert the record" );
        }



        // (Running triggers)
        ( new TriggerService() )->run_async
        (
            $this->user->tenant,

            [
                'EVENT_TYPE'        => $action,
                'EVENT_DESCRIPTION' => $record['description'],
                'EVENT_SOURCE'      => $this->model_name,
                'ELEMENT_ID'        => $this->user->id,
                'TENANT_ID'         => $this->user->tenant,
                'USER_ID'           => $this->user->id
            ]
        )
        ;
    }

    public function reset_account () : void
    {
        if ( $this->user->hierarchy !== 1 )
        {// (User is not an admin)
            // Throwing the exception
            throw error( 6101, 'User not authorized' );
        }



        // (Getting the value)
        $target_user = model( UserModel::class )->where( [ [ 'tenant', $this->user->tenant ], [ 'id', $this->element_id ] ] )->find( [ 'id', 'tenant', 'name' ] );

        if ( !$target_user )
        {// (Record not found)
            // Throwing the exception
            throw error( 6300, "Entity 'User' :: Record not found" );
        }



        if ( $target_user->tenant !== $this->user->tenant )
        {// (User does not belong to the tenant)
            // Throwing the exception
            throw error( 6101, 'User not authorized' );
        }



        if ( !model( SessionModel::class )->where( 'user', $target_user->id )->delete() )
        {// (Unable to delete the records)
            // Throwing the exception
            throw error( 6304, "Entity 'Session' :: Unable to delete the records" );
        }



        // (Getting the value)
        $record =
        [
            'security.password'           => null,
            'security.mfa'                => false,
            'security.idk.authentication' => false,
            'security.idk.public_key'     => null,
            'security.idk.enc_nonce'      => null,
            'datetime.update'             => DateTime::fetch()
        ]
        ;



        // (Getting the value)
        $object = new \stdClass();

        if ( !model( UserModel::class )->where( [ [ 'tenant', $this->user->tenant ], [ 'id', $target_user->id ] ] )->bind( $object, [ 'name' ] )->update( $record ) )
        {// (Unable to update the record)
            // Throwing the exception
            throw error( 6302, "Entity '{$this->model_name}' :: Unable to update the record" );
        }



        // (Getting the values)
        $ip = ip();
        $ua = ua();



        // (Getting the value)
        $client = ClientService::detect( $ip, $ua );

        if ( !$client )
        {// (Unable to detect the client)
            // Throwing the exception
            throw error( 6402, 'Unable to detect the client' );
        }



        // (Getting the value)
        $class_method = __FUNCTION__;



        // (Getting the value)
        $action = "{$this->model_name}.$class_method";



        // (Getting the value)
        $record =
        [
            'tenant'               => $this->user->tenant,
            'user'                 => $this->user->id,
            'action'               => $action,
            'description'          => "{$this->model_name} <b>{$this->user->name}</b> has reset the account for user <b>{$target_user->name}</b>",
            'session'              => null,
            'ip'                   => $ip,
            'user_agent'           => $ua,
            'ip_info.country.code' => $client['ip']['country']['code'],
            'ip_info.country.name' => $client['ip']['country']['name'],
            'ip_info.isp'          => $client['ip']['isp'],
            'ua_info.browser'      => $client['ua']['browser'],
            'ua_info.os'           => $client['ua']['os'],
            'ua_info.hw'           => $client['ua']['hw'],
            'resource.type'        => $this->model_name,
            'resource.action'      => $class_method,
            'resource.id'          => $target_user->id,
            'resource.key'         => $record['name'],
            'datetime.insert'      => DateTime::fetch()
        ]
        ;

        if ( !model( ActivityModel::class )->insert( [ $record ] ) )
        {// (Unable to insert the record)
            // Throwing the exception
            throw error( 6302, "Entity 'Activity' :: Unable to insert the record" );
        }



        // (Running triggers)
        ( new TriggerService() )->run_async
        (
            $this->user->tenant,

            [
                'EVENT_TYPE'        => $action,
                'EVENT_DESCRIPTION' => $record['description'],
                'EVENT_SOURCE'      => $this->model_name,
                'TENANT_ID'         => $this->user->tenant,
                'USER_ID'           => $this->user->id,
                'RESOURCE_ID'       => null,
                'ELEMENT_ID'        => $target_user->id
            ]
        )
        ;
    }

    public function mark_alert_as_read (object $user, array $ids) : void
    {
        // (Getting the value)
        $time = time();



        foreach ( $ids as $id )
        {// Processing each entry
            // (Getting the value)
            $record =
            [
                'datetime.alert.read' => DateTime::fetch( $time )
            ]
            ;

            if ( !model( ActivityModel::class )->where( [ ( $user->hierarchy === 1 ? [ 'tenant', $user->tenant ] : [ 'user', $user->id ] ), [ 'id', $id ] ] )->update( $record ) )
            {// (Unable to update the record)
                // Throwing the exception
                throw error( 6303, "Resource 'Activity' :: Unable to update the record" );
            }
        }
    }



    public function report_access (string $session_id, string $auth_method) : void
    {
        // (Getting the values)
        $ip = ip();
        $ua = ua();



        // (Getting the value)
        $client = ClientService::detect( $ip, $ua );

        if ( !$client )
        {// (Unable to detect the client)
            // Throwing the exception
            throw error( 6402, 'Unable to detect the client' );
        }



        // (Setting the value)
        $auth_map =
        [
            'basic'        => 'BASIC',
            'mfa'          => 'MFA',
            'mfa_trusted'  => 'MFA WITH TRUSTED DEVICE',
            'idk'          => 'IDK',
            'shell_invite' => 'SHELL INVITE',
            'web_invite'   => 'WEB INVITE',
            'quick_access' => 'QUICK ACCESS'
        ]
        ;



        // (Getting the value)
        $auth_method_s = $auth_map[ $auth_method ] ?? null;
    
        if ( !$auth_method_s )
        {// (Invalid authentication method)
            // Throwing the exception
            throw error( 6200, 'Invalid authentication method' );
        }



        // (Getting the value)
        $record =
        [
            'session'              => $session_id,
            'description'          => "Login via <b>$auth_method_s</b>",
            'ip'                   => $ip,
            'user_agent'           => $ua,
            'ip_info.country.code' => $client['ip']['country']['code'],
            'ip_info.country.name' => $client['ip']['country']['name'],
            'ip_info.isp'          => $client['ip']['isp'],
            'ua_info.browser'      => $client['ua']['browser'],
            'ua_info.os'           => $client['ua']['os'],
            'ua_info.hw'           => $client['ua']['hw'],
            'datetime.insert'      => DateTime::fetch()
        ]
        ;

        if ( !model( SessionLoginModel::class )->insert( [ $record ] ) )
        {// (Unable to insert the record)
            // Throwing the exception
            throw error( 6302, "Entity 'SessionLogin' :: Unable to insert the record" );
        }



        // (Getting the value)
        $record =
        [
            'tenant'               => $this->user->tenant,
            'user'                 => $this->user->id,
            'action'               => "User.login_via_$auth_method",
            'description'          => "User <b>{$this->user->name}</b> has logged in via <b>$auth_method_s</b>",
            'session'              => $session_id,
            'ip'                   => $ip,
            'user_agent'           => $ua,
            'ip_info.country.code' => $client['ip']['country']['code'],
            'ip_info.country.name' => $client['ip']['country']['name'],
            'ip_info.isp'          => $client['ip']['isp'],
            'ua_info.browser'      => $client['ua']['browser'],
            'ua_info.os'           => $client['ua']['os'],
            'ua_info.hw'           => $client['ua']['hw'],
            'datetime.insert'      => DateTime::fetch()
        ]
        ;

        if ( !model( ActivityModel::class )->insert( [ $record ] ) )
        {// (Unable to insert the record)
            // Throwing the exception
            throw error( 6302, "Entity 'Activity' :: Unable to insert the record" );
        }



        // (Running triggers)
        ( new TriggerService() )->run_async
        (
            $this->user->tenant,

            [
                'EVENT_TYPE'        => (string) action(),
                'EVENT_DESCRIPTION' => $record['description'],
                'EVENT_SOURCE'      => $this->model_name,
                'TENANT_ID'         => $this->user->tenant,
                'USER_ID'           => $this->user->id,
                'RESOURCE_ID'       => null,
                'ELEMENT_ID'        => $this->user->id
            ]
        )
        ;
    }

    public function prepare_auth (string $operation_name, string $auth_method, Session $session) : void
    {
        // (Getting the value)
        $operation_service = new OperationService();



        // (Getting the value)
        $opid = $operation_service->insert
        (
            name:         $operation_name,
            task:         null,
            data:         null,
            display:      null,
            login:        null,
            callback_url: null,
            duration:     (int) env( 'LVA_OP_DURATION' )
        )
        ;



        // (Getting the value)
        $operation_service->notify( $this->user->email, $opid );



        // (Getting the value)
        $session->data['auth'] =
        [
            'opid'   => $opid,
            'user'   => $this->user->id,
            'method' => $auth_method
        ]
        ;
    }



    public function login (array $input, Session $session)
    {
        // (Getting the values)
        [ $user, $tenant ] = explode( '@', $input['login'], 2 );
        $password          = $input['password'];



        // (Getting the value)
        $tenant = model( TenantModel::class )->where( 'name', $tenant )->find();

        if ( !$tenant )
        {// (Record not found)
            // Throwing the exception
            throw error( 6100, 'Client not authorized' );
        }



        // (Getting the value)
        $user = model( UserModel::class )->where( [ [ 'tenant', $tenant->id ], [ 'name', $user ] ] )->find();

        if ( !$user )
        {// (Record not found)
            // Throwing the exception
            throw error( 6100, 'Client not authorized' );
        }



        // (Setting the user)
        $this->user( new AppUser( $user->id, $user->tenant, $user->hierarchy, $user->name, $user->email  ) );



        if ( $user->get( 'security.idk.authentication' ) && $user->get( 'security.idk.forced' ) )
        {// (User has IDK authentication forced)
            // Throwing the exception
            throw error( 6100, 'Client not authorized' );
        }



        // (Getting the value)
        $class_method = __FUNCTION__;



        // (Getting the value)
        $action = "{$this->model_name}.$class_method";



        if ( $user->get( 'security.password' ) === null )
        {// Value not found
            // Throwing the exception
            throw error( 6100, 'Client not authorized' );
        }

        if ( !password_verify( $password, $user->get( 'security.password' ) ) )
        {// Match failed
            // (Getting the values)
            $ip = ip();
            $ua = ua();



            // (Getting the value)
            $client = ClientService::detect( $ip, $ua );

            if ( !$client )
            {// (Unable to detect the client)
                // Throwing the exception
                throw error( 6402, 'Unable to detect the client' );
            }



            // (Getting the value)
            $record =
            [
                'tenant'               => $this->user->tenant,
                'user'                 => $this->user->id,
                'action'               => $action,
                'description'          => "{$this->model_name} <b>{$this->user->name}</b> has failed login (wrong password)",
                'session'              => $session->id,
                'ip'                   => $ip,
                'user_agent'           => $ua,
                'ip_info.country.code' => $client['ip']['country']['code'],
                'ip_info.country.name' => $client['ip']['country']['name'],
                'ip_info.isp'          => $client['ip']['isp'],
                'ua_info.browser'      => $client['ua']['browser'],
                'ua_info.os'           => $client['ua']['os'],
                'ua_info.hw'           => $client['ua']['hw'],
                'resource.type'        => $this->model_name,
                'resource.action'      => $class_method,
                'resource.id'          => $user->id,
                'resource.key'         => null,
                'alert_severity'       => 3,
                'datetime.insert'      => DateTime::fetch()
            ]
            ;

            if ( !model( ActivityModel::class )->insert( [ $record ] ) )
            {// (Unable to insert the record)
                // Throwing the exception
                throw error( 6302, "Entity 'Activity' :: Unable to insert the record" );
            }



            // (Getting the values)
            $record['id']                        = model( ActivityModel::class )->last_id();
            $record['datetime']['alert']['read'] = null;



            // (Getting the values)
            $target = message_target( $this->user->tenant, [ $this->user->id ], [ 1 ] );
            $event  = message_event( 'activity', $record );

            // (Sending the message)
            message_send( message( $target, $event ) );



            // Throwing the exception
            throw error( 6100, 'Client not authorized' );
        }



        if ( !$session->start() || !$session->regenerate_id() || !$session->set_duration() )
        {// (Unable to set the session)
            // Throwing the exception
            throw error( 6000, 'Unable to set the session' );
        }



        // (Setting the value)
        $session->data = [];



        if ( $user->get( 'security.mfa' ) )
        {// (Login method is 'MFA')
            // (Setting the value)
            $mfa_check_required = true;

            if ( $user->get( 'security.trusted_device' ) )
            {// (Trusted device policy is enabled on this user)
                // (Setting the value)
                $add_trusted_device = false;



                // (Getting the value)
                $device = cookie_value( 'device' );

                if ( $device )
                {// Value found
                    if ( model( TrustedDeviceModel::class )->where( [ [ 'tenant', $user->tenant ], [ 'owner', $user->id ], [ 'id', $device ] ] )->exists() )
                    {// (Record found)
                        // (Setting the value)
                        $mfa_check_required = false;
                    }
                    else
                    {// (Record not found)
                        // (Setting the value)
                        $add_trusted_device = true;
                    }
                }
                else
                {// Value not found
                    // (Setting the value)
                    $add_trusted_device = true;
                }



                if ( $add_trusted_device )
                {// Value is true
                    // (Setting the value)
                    $session->data['add_trusted_device'] = true;
                }
            }



            if ( $mfa_check_required )
            {// (MFA check required)
                // (Preparing the authentication)
                $this->prepare_auth( 'Login via MFA', 'mfa', $session );
            }
            else
            {// (MFA check not required)
                // (Getting the value)
                $session->data['user'] = $user->id;



                // (Listening for the event)
                $session->on('update', function ($session) {
                    // (Reporting the access)
                    $this->report_access( $session->id, 'mfa_trusted' );
                });



                // Returning the value
                return [ 'location' => ( new LoginService() )->extract_location() ];
            }
        }
        else
        {// (Login method is 'BASIC')
            // (Getting the value)
            $session->data['user'] = $user->id;



            // (Listening for the event)
            $session->on('update', function ($session) {
                // (Reporting the access)
                $this->report_access( $session->id, 'basic' );
            });



            // Returning the value
            return [ 'location' => ( new LoginService() )->extract_location() ];
        }
    }

    public function login_via_idk (ReadableStream $input, Session $session)
    {
        // (Getting the values)
        [ $user_uuid, $private_key ] = explode( "\n\n", $input, 2 );



        // (Getting the value)
        $user = model( UserModel::class )->where( 'uuid', $user_uuid )->find();

        if ( !$user )
        {// (Record not found)
            // Throwing the exception
            throw error( 6100, 'Client not authorized' );
        }



        if ( $user->get( 'security.idk.authentication' ) !== 1 )
        {// Value is false
            // Throwing the exception
            throw error( 6100, 'Client not authorized' );
        }



        // (Getting the value)
        $class_method = __FUNCTION__;



        // (Getting the value)
        $action = "{$this->model_name}.$class_method";



        if ( RSA::select( base64_decode( $user->get( 'security.idk.enc_nonce' ) ) )->decrypt( $private_key ) === false )
        {// (Key is not valid)
            // (Getting the values)
            $ip = ip();
            $ua = ua();



            // (Getting the value)
            $client = ClientService::detect( $ip, $ua );

            if ( !$client )
            {// (Unable to detect the client)
                // Throwing the exception
                throw error( 6402, 'Unable to detect the client' );
            }



            // (Getting the value)
            $record =
            [
                'tenant'               => $this->user->tenant,
                'user'                 => $this->user->id,
                'action'               => $action,
                'description'          => "{$this->model_name} <b>{$this->user->name}</b> has failed login (wrong IDK key)",
                'session'              => null,
                'ip'                   => $ip,
                'user_agent'           => $ua,
                'ip_info.country.code' => $client['ip']['country']['code'],
                'ip_info.country.name' => $client['ip']['country']['name'],
                'ip_info.isp'          => $client['ip']['isp'],
                'ua_info.browser'      => $client['ua']['browser'],
                'ua_info.os'           => $client['ua']['os'],
                'ua_info.hw'           => $client['ua']['hw'],
                'resource.type'        => $this->model_name,
                'resource.action'      => $class_method,
                'resource.id'          => $this->user->id,
                'resource.key'         => null,
                'alert_severity'       => 3,
                'datetime.insert'      => DateTime::fetch()
            ]
            ;

            if ( !model( ActivityModel::class )->insert( [ $record ] ) )
            {// (Unable to insert the record)
                // Throwing the exception
                throw error( 6302, "Entity 'Activity' :: Unable to insert the record" );
            }



            // (Getting the values)
            $record['id']                        = model( ActivityModel::class )->last_id();
            $record['datetime']['alert']['read'] = null;



            // (Getting the values)
            $target = message_target( $this->user->tenant, [ $this->user->id ], [ 1 ] );
            $event  = message_event( 'activity', $record );

            // (Sending the message)
            message_send( message( $target, $event ) );



            // Throwing the exception
            throw error( 6100, 'Client not authorized' );
        }



        if ( !$session->start() || !$session->regenerate_id() || !$session->set_duration() )
        {// (Unable to set the session)
            // Throwing the exception
            throw error( 6000, 'Unable to set the session' );
        }



        // (Setting the value)
        $session->data = [];



        // (Getting the value)
        $session->data['user'] = $user->id;



        // (Setting the user)
        $this->user( new AppUser( $user->id, $user->tenant, $user->hierarchy, $user->name, $user->email  ) );



        // (Listening for the event)
        $session->on('update', function ($session) {
            // (Reporting the access)
            $this->report_access( $session->id, 'idk' );
        });



        // (Getting the value)
        $location = ( new LoginService() )->extract_location();



        // (Sending the cookie)
        cookie( 'fwd_route' )->unset()->send();



        // Returning the value
        return [ 'location' => $location ];
    }

    public function login_via_authorization (Session $session, int $timeout = 180)
    {
        if ( !$session->start() )
        {// (Unable to start the session)
            // Throwing the exception
            throw error( 6000, 'Unable to start the session' );
        }



        // (Getting the value)
        $auth = $session->data['auth'];

        if ( !$auth )
        {// Value not found
            if ( !$session->destroy() )
            {// (Unable to destroy the session)
                // Throwing the exception
                throw error( 6000, 'Unable to destroy the session' );
            }



            // Throwing the exception
            throw error( 6100, 'Client not authorized' );
        }



        // (Getting the value)
        $operation_service = new OperationService();



        // (Getting the value)
        $login = new LoginService();



        // (Setting the time limit)
        set_time_limit( $timeout );



        // (Getting the value)
        $time = time();



        while ( true )
        {// Looping
            if ( time() - $time >= $timeout )
            {// (Time exceeded)
                // (Closing the session)
                $session->close();

                // Throwing the exception
                throw error( 6400 );
            }



            // (Getting the value)
            $operation = $operation_service->find( $auth['opid'] );

            if ( !$operation )
            {// (Record not found)
                // (Closing the session)
                $session->close();

                // Throwing the exception
                throw error( 6300, "Entity 'Operation' :: Record not found" );
            }

            if ( $operation->get( 'datetime.authorization' ) )
            {// (Operation has been authorized)
                // (Getting the value)
                $add_trusted_device = $session->data['add_trusted_device'] ?? false;



                // (Setting the value)
                $session->data = [];



                // (Getting the value)
                $session->data['user'] = $auth['user'];



                // (Setting the user)
                $this->user( user( $auth['user'] ) );



                if ( $add_trusted_device )
                {// Value is true
                    // (Getting the value)
                    $session->data['add_trusted_device'] = true;
                }



                // (Listening for the event)
                $session->on('update', function ($session) use ($auth) {
                    // (Reporting the access)
                    $this->report_access( $session->id, $auth['method'] );
                });



                // (Getting the value)
                $location = $login->extract_location();



                // (Sending the cookie)
                cookie( 'fwd_route' )->unset()->send();



                // Returning the value
                return [ 'location' => $location ];
            }



            // (Waiting for the time)
            sleep( 2 );
        }



        // Throwing the exception
        throw error( 6400 );
    }

    public function request_quick_access (string $email, Session $session)
    {
        // (Getting the value)
        $user = model( UserModel::class )->where( 'email', $email )->find( [ 'id', 'email' ] );

        if ( !$user )
        {// (Record not found)
            // Returning the value
            return null;
        }



        // (Setting the user)
        $this->user( user( $user->id ) );



        if ( !$session->start() )
        {// (Unable to start the session)
            // Throwing the exception
            throw error( 6000, 'Unable to start the session' );
        }

        if ( !$session->regenerate_id() )
        {// (Unable to regenerate the session ID)
            // Throwing the exception
            throw error( 6000, 'Unable to regenerate the session ID' );
        }

        if ( !$session->set_duration() )
        {// (Unable to set the session duration)
            // Throwing the exception
            throw error( 6000, 'Unable to set the session duration' );
        }



        // (Setting the value)
        $session->data = [];



        // (Preparing the authentication)
        $this->prepare_auth( 'Login via Quick Access', 'quick_access', $session );
    }



    public function list_index () : array
    {
        // (Setting the value)
        $elements = [];

        foreach ( $this->list( [ 'id', 'name', 'hierarchy', 'birth.name', 'birth.surname' ] ) as $element )
        {// Processing each entry
            // (Getting the value)
            $elements[ $element->id ] = $element;
        }



        // Returning the value
        return $elements;
    }
}



?>