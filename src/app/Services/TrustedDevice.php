<?php



namespace App\Services;



use \Solenoid\X\Error;
use \Solenoid\X\HTTP\Session;

use \Solenoid\MySQL\DateTime;
use \Solenoid\KeyGen\Token;

use \App\Models\TrustedDevice as Entity;
use \App\Models\Activity as ActivityModel;

use \App\DTOs\TrustedDevice\UpdateDTO;

use \App\Services\Client as ClientService;
use \App\Services\Trigger as TriggerService;



class TrustedDevice
{
    private string $class_basename;



    public function __construct ()
    {
        // (Getting the value)
        $this->class_basename = class_basename( __CLASS__ );
    }



    public function list (object $user, array $fields = []) : array|Error
    {
        // (Getting the value)
        $data['records'] = Entity::fetch()->where( [ [ 'tenant', $user->tenant ], [ 'owner', $user->id ] ] )->order( [ 'id' => SORT_ASC ] )->list( $fields );



        // Returning the value
        return $data;
    }



    public function insert (object $user, string $name, ?Session $session = null) : string|Error
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



        // (Getting the value)
        $class_method = __FUNCTION__;



        // (Getting the value)
        $action = "{$this->class_basename}.$class_method";



        // (Getting the value)
        $record =
        [
            'id'                    => Token::generate( 128 ),
            'tenant'                => $user->tenant,
            'owner'                 => $user->id,
            'name'                  => $name,
            'user_agent'            => $ua,
            'ua_info.browser'       => $client['ua']['browser'],
            'ua_info.os'            => $client['ua']['os'],
            'ua_info.hw'            => $client['ua']['hw'],
            'datetime.insert'       => DateTime::fetch()
        ]
        ;

        if ( !Entity::fetch()->insert( [ $record ] ) )
        {// (Unable to insert the record)
            // Throwing the exception
            throw error( 6302, "Entity 'TrustedDevice' :: Unable to insert the record" );
        }



        // (Getting the value)
        $record_id = $record['id'];



        if ( $session )
        {// Value found
            // (Removing the element)
            unset( $this->session->data['add_trusted_device'] );
        }



        // (Sending the cookie)
        cookie( 'device' )->set( $record_id, time() + ( (int) env( 'TRUSTED_DEVICE_COOKIE_EXPIRATION' ) ) )->send();



        // (Getting the value)
        $record =
        [
            'tenant'               => $user->tenant,
            'user'                 => $user->id,
            'action'               => $action,
            'description'          => "User <b>{$user->name}</b> has set device <b>{$name}</b> as trusted",
            'session'              => $session ? $session->id : null,
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

        if ( !ActivityModel::fetch()->insert( [ $record ] ) )
        {// (Unable to insert the record)
            // Throwing the exception
            throw error( 6302, "Entity 'Activity' :: Unable to insert the record" );
        }



        // (Running triggers)
        ( new TriggerService() )->run_async
        (
            $user->tenant,

            [
                'EVENT_TYPE'        => $action,
                'EVENT_DESCRIPTION' => $record['description'],
                'EVENT_SOURCE'      => $this->class_basename,

                'TENANT_ID'         => $user->tenant,
                'USER_ID'           => $user->id,
                'RESOURCE_ID'       => null,
                'ELEMENT_ID'        => $record_id
            ]
        )
        ;



        // Returning the value
        return $record_id;
    }

    public function delete (object $user, array $ids, ?Session $session = null) : void
    {
        foreach ( $ids as $id )
        {// Processing each entry
            // (Setting the value)
            $object = new \StdClass();

            if ( !Entity::fetch()->where( [ [ 'tenant', $user->tenant ], [ 'owner', $user->id ], [ 'id', $id ] ] )->bind( $object, [ 'name' ] )->delete() )
            {// (Unable to delete the record)
                // Throwing the exception
                throw error( 6304, "Entity '{$this->class_basename}' :: Unable to delete the record" );
            }



            if ( cookie_value( 'device' ) === $id )
            {// (Cookie found)
                // (Sending the cookie)
                cookie( 'device' )->unset()->send();
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
            $action = "{$this->class_basename}.$class_method";



            // (Getting the value)
            $record =
            [
                'tenant'               => $user->tenant,
                'user'                 => $user->id,
                'action'               => $action,
                'description'          => "{$this->class_basename} <b>{$object->name}</b> has been deleted",
                'session'              => $session ? $session->id : null,
                'ip'                   => $ip,
                'user_agent'           => $ua,
                'ip_info.country.code' => $client['ip']['country']['code'],
                'ip_info.country.name' => $client['ip']['country']['name'],
                'ip_info.isp'          => $client['ip']['isp'],
                'ua_info.browser'      => $client['ua']['browser'],
                'ua_info.os'           => $client['ua']['os'],
                'ua_info.hw'           => $client['ua']['hw'],
                'resource.type'        => $this->class_basename,
                'resource.action'      => $class_method,
                'resource.id'          => null,
                'resource.key'         => $object->name,
                'datetime.insert'      => DateTime::fetch()
            ]
            ;

            if ( !ActivityModel::fetch()->insert( [ $record ] ) )
            {// (Unable to insert the record)
                // Throwing the exception
                throw error( 6302, "Entity 'Activity' :: Unable to insert the record" );
            }



            // (Running triggers)
            ( new TriggerService() )->run_async
            (
                $user->tenant,

                [
                    'EVENT_TYPE'        => $action,
                    'EVENT_DESCRIPTION' => $record['description'],
                    'EVENT_SOURCE'      => $this->class_basename,

                    'TENANT_ID'         => $user->tenant,
                    'USER_ID'           => $user->id,
                    'RESOURCE_ID'       => null,
                    'ELEMENT_ID'        => $id
                ]
            )
            ;
        }
    }



    public function set_name (object $user, UpdateDTO $input, ?Session $session = null) : string
    {
        // (Getting the value)
        $input_id = $input->get( 'id' );



        // (Setting the value)
        $object = new \StdClass();



        // (Getting the value)
        $values =
        [
            'name' => $input->get( 'name' )
        ]
        ;

        if ( !Entity::fetch()->where( [ [ 'tenant', $user->tenant ], [ 'owner', $user->id ], [ 'id', $input_id ] ] )->bind( $object, [ 'name' ] )->update( $values ) )
        {// (Unable to update the record)
            // Throwing the exception
            throw error( 6303, "Entity '{$this->class_basename}' :: Unable to update the record" );
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
        $action = "{$this->class_basename}.$class_method";



        // (Getting the value)
        $record =
        [
            'tenant'               => $user->tenant,
            'user'                 => $user->id,
            'action'               => $action,
            'description'          => "{$this->class_basename} <b>{$object->name}</b> has been updated" . ( $input->get( 'name' ) === $object->name ? '' : " :: Renamed to <b>" . $input->get( 'name' ) . '</b>' ),
            'session'              => $session ? $session->id : null,
            'ip'                   => $ip,
            'user_agent'           => $ua,
            'ip_info.country.code' => $client['ip']['country']['code'],
            'ip_info.country.name' => $client['ip']['country']['name'],
            'ip_info.isp'          => $client['ip']['isp'],
            'ua_info.browser'      => $client['ua']['browser'],
            'ua_info.os'           => $client['ua']['os'],
            'ua_info.hw'           => $client['ua']['hw'],
            'resource.type'        => $this->class_basename,
            'resource.action'      => $class_method,
            'resource.id'          => null,
            'resource.key'         => $object->name,
            'datetime.insert'      => DateTime::fetch()
        ]
        ;

        if ( !ActivityModel::fetch()->insert( [ $record ] ) )
        {// (Unable to insert the record)
            // Throwing the exception
            throw error( 6302, "Entity 'Activity' :: Unable to insert the record" );
        }



        // (Running triggers)
        ( new TriggerService() )->run_async
        (
            $user->tenant,

            [
                'EVENT_TYPE'        => $action,
                'EVENT_DESCRIPTION' => $record['description'],
                'EVENT_SOURCE'      => $this->class_basename,

                'TENANT_ID'         => $user->tenant,
                'USER_ID'           => $user->id,
                'RESOURCE_ID'       => null,
                'ELEMENT_ID'        => $input_id
            ]
        )
        ;



        // Returning the value
        return $input_id;
    }



    public function list_index (object $user, array $fields = []) : array|Error
    {
        // (Getting the value)
        $data = $this->list( $user, $fields );

        if ( $data instanceof Error )
        {// (Error found)
            // Returning the value
            return $data;
        }



        // (Setting the value)
        $records = [];

        foreach ( $data['records'] as $record )
        {// Processing each entry
            // (Getting the value)
            $records[ $record->id ] = $record->values;
        }



        // Returning the value
        return $records;
    }
}



?>