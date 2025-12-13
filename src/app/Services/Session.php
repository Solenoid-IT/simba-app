<?php



namespace App\Services;



use \Solenoid\MySQL\DateTime;

use \App\Entity;

use \App\Models\Session as SessionModel;
use \App\Models\Activity as ActivityModel;
use \App\Models\SessionLoginView as SessionLoginViewModel;

use \App\Services\Trigger as TriggerService;
use \App\Services\Client as ClientService;



class Session extends Entity
{
    public function __construct ()
    {
        // (Calling the function)
        parent::__construct( SessionModel::class );
    }



    public function get_data (string $id) : array|false
    {
        // (Getting the value)
        $data = SessionModel::fetch()->where( [ [ 'id', $id ], [ '`datetime.expiration` > CURRENT_TIMESTAMP' ] ] )->find( [ 'data' ] )->data;

        if ( !$data ) return false;



        // Returning the value
        return json_decode( $data, true );
    }



    public function list (array $fields = []) : array
    {
        // Returning the value
        return entity( SessionLoginViewModel::class )->list( $fields );
    }

    public function delete () : array
    {
        // (Setting the value)
        $errors = [];

        foreach ( $this->element_ids as $id )
        {// Processing each entry
            if ( $id === $this->user->session->id ) continue;


            if ( !SessionModel::fetch()->where( [ [ 'user', $this->user->id ], [ 'id', $id ] ] )->delete() )
            {// (Unable to delete the record)
                // (Getting the value)
                $errors[ $id ] = error( 6304, "Resource '{$this->model_name}' :: Unable to delete the record" );
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
                'action'               => (string) action(),
                'description'          => "{$this->model_name} has been deleted",
                'session'              => $this->user->session->id,
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
                'resource.id'          => null,
                'resource.key'         => null,
                'datetime.insert'      => DateTime::fetch()
            ]
            ;

            if ( !model( ActivityModel::class )->insert( [ $record ] ) )
            {// (Unable to insert the record)
                // Throwing the exception
                throw error( 6302, "Resource 'Activity' :: Unable to insert the record" );
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
                    'ELEMENT_ID'        => $id,
                ]
            )
            ;
        }



        // Returning the value
        return $errors;
    }



    public function list_index (string $current_session_id, array $fields = []) : array
    {
        // (Setting the value)
        $elements = [];

        foreach ( $this->list( $fields ) as $element )
        {// Processing each entry
            // (Getting the value)
            $element->current = $element->id === $current_session_id;



            // (Getting the value)
            $elements[ $element->id ] = $element;
        }



        // Returning the value
        return $elements;
    }
}



?>