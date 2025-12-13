<?php



namespace App;



use \Solenoid\MySQL\Model;
use \Solenoid\MySQL\DateTime;
use \Solenoid\MySQL\Command;

use \Solenoid\X\Data\DTO;

use \Solenoid\X\CSV\CSV;
use \Solenoid\X\CSV\Stream;

use \Solenoid\X\Stream\ReadableStream;

use \App\Scopes\ReadScope;
use \App\Scopes\WriteScope;

use \App\DTOs\PaginatorDTO;

use \App\Models\Activity;
use \App\Models\UserShareRule;
use \App\Models\GroupShareRule;

use \App\Services\Client as ClientService;
use \App\Services\Trigger as TriggerService;
use \App\Services\Share as ShareService;



class Entity
{
    public Model  $model;
    public string $model_name;

    public User   $user;
    public Client $client;

    public int    $element_id;
    public array  $element_ids = [];

    public array  $input_values = [];

    public ?string $activity_description = null;

    public ?PaginatorDTO $paginator = null;

    public array $values = [];



    public function __construct (string|Model $model)
    {
        if ( is_string( $model ) )
        {// (Value is a string)
            $model = model( $model );
        }



        if ( !is_subclass_of( $model, Model::class ) )
        {// (Model does not extend the base class)
            // Throwing the exception
            throw error( 6000, 'Model must extend the base Model class' );
        }



        // (Getting the values)
        $this->model = $model;



        // (Getting the value)
        $class_path = get_class( $this->model );

        // (Getting the value)
        $this->model_name = str_replace( '\\', '/', substr( $class_path, strlen( 'App\\Models\\' ) ) );



        // (Getting the value)
        $user = user();

        if ( $user )
        {// Value found
            // (Getting the value)
            $this->user = $user;
        }
    }



    private function check_input_hierarchy (int $hierarchy) : void
    {
        if ( $this->element_id === $this->user->id )
        {// Match OK
            if ( $hierarchy < $this->user->hierarchy )
            {// Match OK
                // Throwing the exception
                throw error( 6500, "User does not have the required permission 'update' on the entity '{$this->model_name}'" );
            }
        }
    }

    private function adapt_model_to_resource () : void
    {
        // (Executing the command)
        $this->model->connection->execute( new Command( "SET SESSION sql_mode = '';" ) );



        // (Composing the model)
        $this->model->group( [ 'id' ] )->having( "`priority` = MIN( `priority` )" );
    }



    /**
     * Retrieves a single element based on a custom filter within the current user scope
     * @param array $fields List of fields to retrieve (optional, default is to all fields)
     * @return Element|null The found element or null if not found
     * @throws Error if ReadScope is not found for the model
     */
    public function find (array $fields = []) : Element|null
    {
        // (Getting the value)
        $scope = ReadScope::find( $this->model::class, $this->user );

        if ( !$scope )
        {// Value not found
            // Throwing the exception
            throw error( 6000, "Entity '{$this->model_name}' :: ReadScope not found" );
        }



        // (Restricting the user)
        $filter = $this->user->restrict( $scope );



        // (Getting the value)
        $resource = Resource::find( $this->model::class );

        if ( $resource )
        {// (Model is a 'Resource')
            // (Getting the value)
            $rule = ( new ShareService() )->get_rule( $this->user->tenant, $this->user->id, $resource->id, $this->element_id );

            if ( !$rule )
            {// (User not authorized)
                // Throwing the exception
                throw error( 6101, 'User not authorized' );
            }



            // (Setting the value)
            store()->set( 'share_rule', $rule );
        }



        // (Getting the value)
        $record = $this->model->where( $filter )->and()->where( 'id', $this->element_id )->find( [ 'id', ...$fields ] );

        if ( !$record )
        {// (Record not found)
            // Returning the value
            return null;
        }



        // Returning the value
        return element( $record );
    }

    /**
     * Retrieves a list of elements based on a custom filter within the current user scope, with optional pagination and sorting
     * @param array $fields List of fields to retrieve (optional, default is to all fields)
     * @return array<Element> List of found elements
     * @throws Error if ReadScope is not found for the model
     */
    public function list (array $fields = []) : array
    {
        // (Getting the value)
        $scope = ReadScope::find( $this->model::class, $this->user );

        if ( !$scope )
        {// Value not found
            // Throwing the exception
            throw error( 6000, "Entity '{$this->model_name}' :: ReadScope not found" );
        }



        // (Restricting the user)
        $filter = $this->user->restrict( $scope );



        // (Composing the model)
        $this->model->where( $filter );



        if ( $this->element_ids )
        {// Value is not empty
            // (Composing the model)
            $this->model->and()->where( 'id', 'IN', $this->element_ids );
        }



        // (Getting the value)
        $resource = Resource::find( $this->model::class );

        if ( $resource )
        {// Value found
            // (Adapting the model to the resource)
            $this->adapt_model_to_resource();
        }



        if ( $this->paginator )
        {// Value found
            // (Getting the value)
            $id_field = in_array( 'id', $fields );



            if ( !empty( $this->paginator->localSearch ) )
            {// Value found
                // (Composing the model)
                $this->model->and()->filter_local( $this->paginator->localSearch, '%V%' );
            }
            else
            if ( !empty( $this->paginator->globalSearch ) )
            {// Value found
                // (Composing the model)
                $this->model->and()->filter_global( $this->paginator->globalSearch ?? '', $fields, '%V%' );
            }



            // (Getting the value)
            $length = $this->model->count( 'id' );



            // (Getting the value)
            $id_sort = $this->paginator->sortField === null || $this->paginator->sortField === 'id';



            if ( $this->paginator->cursor->lastId !== null )
            {// Value found
                // (Getting the value)
                $operator = $this->paginator->sort === null || $this->paginator->sort === 'ASC' ? '>' : '<';


                if ( $id_sort )
                {// Value is true
                    if ( !( $operator === '<' && $this->paginator->cursor->lastId === 0 ) )
                    {// Match OK
                        if ( $this->paginator->sortField === 'id' && $this->paginator->sort === 'DESC' )
                        {// Match OK
                            // (Composing the model)
                            $this->model->and()->where( 'id', $operator, $this->paginator->cursor->lastSortValue );
                        }
                        else
                        {// Match failed
                            // (Composing the model)
                            $this->model->and()->where( 'id', $operator, $this->paginator->cursor->lastId );
                        }
                    }
                }
                else
                {// Value is false
                    if ( $this->paginator->cursor->lastId === 0 )
                    {// Match OK
                        // (Doing nothing)
                    }
                    else
                    {// Match failed
                        // (Composing the model)
                        $this->model->and()->where_tuple( [ $this->paginator->sortField, 'id' ], $operator, [ $this->paginator->cursor->lastSortValue, $this->paginator->cursor->lastId ] );
                    }
                }
            }



            // (Setting the value)
            $sort_map =
            [
                'ASC'  => SORT_ASC,
                'DESC' => SORT_DESC
            ]
            ;

            // (Getting the value)
            $sort = $sort_map[ $this->paginator->sort ?? 'ASC' ];



            if ( $id_sort )
            {// Value is true
                // (Composing the model)
                $this->model->order( [ 'id' => $sort ] );
            }
            else
            {// Value is false
                // (Composing the model)
                $this->model->order( [ $this->paginator->sortField => $sort, 'id' => $sort ] );
            }



            // (Composing the model)
            $this->model->paginate( $this->paginator->length );



            // (Getting the value)
            $id_field_forced = !$id_field && !empty( $fields );

            if ( $id_field_forced )
            {// (Field not found)
                // (Prepending the value)
                array_unshift( $fields, 'id' );
            }



            if ( $resource )
            {// Value found
                // (Getting the value)
                $temp_fields = [ ...$fields, 'priority' ];
            }



            // (Setting the value)
            $records = [];

            foreach ( $this->model->list( $temp_fields ?? $fields ) as $record )
            {// Processing each entry
                // (Getting the value)
                $records[ $record->id ] = $record;
            }



            // (Setting the value)
            $results = [];

            foreach ( $records as $id => $record )
            {// Processing each entry
                if ( $id_field_forced )
                {// Value is true
                    // (Removing the value)
                    unset( $record->id );
                }



                if ( $resource )
                {// Value is true
                    // (Removing the value)
                    unset( $record->priority );
                }



                // (Appending the value)
                $results[] = $record;
            }



            // (Setting the value)
            $last_sort_value = null;

            if ( $results )
            {// Value is not empty
                // (Getting the value)
                $sort_field = $paginator->sortField ?? 'ASC';

                if ( $id_sort )
                {// Value is true
                    // (Getting the value)
                    $last_sort_value = $results[ $sort_field === 'ASC' ? 0 : ( count( $results ) - 1 ) ]->get( $sort_field );
                }
                else
                {// Value is false
                    // (Getting the value)
                    $last_sort_value = $results[ count( $results ) - 1 ]->get( $sort_field );
                }
            }



            // (Getting the value)
            $data =
            [
                'length'            => $length,
                'cursor'            =>
                [
                    'lastId'        => $records ? max( array_keys( $records ) ) : 0,
                    'lastSortValue' => $last_sort_value
                ],
                'elements'          => array_map( fn ($record) => element( $record ), $results )
            ]
            ;



            // Returning the value
            return $data;
        }



        // (Getting the value)
        $target_fields = $fields ? [ 'id', ...$fields ] : [];

        if ( $resource )
        {// Value found
            if ( $target_fields )
            {// Value is not empty
                // Appending the value
                $target_fields[] = 'priority';
            }
        }



        // (Getting the value)
        $records = $this->model->order( [ 'id' => SORT_ASC ] )->list( $target_fields );



        if ( $resource )
        {// Value found
            foreach ( $records as &$record )
            {// Processing each entry
                // (Removing the value)
                unset( $record->priority );
            }
        }



        // Returning the value
        return array_map( fn ($record) => element( $record ), $records );
    }



    /**
     * Updates an element based on the provided input data, within the current user scope
     * @param DTO $input The input data transfer object containing the update information
     * @throws Error if WriteScope is not found for the model or if the update operation fails
     */
    public function update (DTO $input) : void
    {
        // (Getting the value)
        $scope = WriteScope::find( $this->model::class, $this->user );

        if ( !$scope )
        {// Value not found
            // Throwing the exception
            throw error( 6000, "Entity '{$this->model_name}' :: WriteScope not found" );
        }



        if ( $this->model instanceof \App\Models\User )
        {// Match OK
            // (Getting the value)
            $input_hierarchy = $input->get( 'hierarchy' );

            if ( $input_hierarchy )
            {// Value found
                // (Checking the input hierarchy)
                $this->check_input_hierarchy( $input_hierarchy );
            }
        }



        // (Getting the value)
        $filter = $this->user->restrict( $scope );



        // (Getting the value)
        $input_id = isset( $this->element_id ) ? $this->element_id : $input->get( 'id' );



        // (Getting the value)
        $resource = Resource::find( $this->model::class );

        if ( $resource )
        {// Value found
            // (Getting the value)
            $rule = ( new ShareService() )->get_rule( $this->user->tenant, $this->user->id, $resource->id, $input_id );

            if ( !$rule )
            {// (Rule not found)
                // Throwing the exception
                throw error( 6502, "User does not have the required permission 'update' on the entity '{$this->model_name}'" );
            }

            if ( $rule->share_rule !== 1 )
            {// (Rule is 'read_only')
                // Throwing the exception
                throw error( 6502, "User does not have the required permission 'update' on the entity '{$this->model_name}'" );
            }
        }



        // (Getting the value)
        $model_keys = model_keys( $this->model );

        

        // (Getting the value)
        $main_key = $model_keys[0] ? $model_keys[0]->name : null;



        // (Setting the value)
        $values = [];

        foreach ( dto_fieldset( $input )->fields as $k => $field )
        {// Processing each entry
            // (Getting the value)
            $values[ $field ] = $input->get( is_string( $k ) ? $k : $field );
        }

        foreach ( $this->input_values as $k => $v )
        {// Processing each entry
            // (Getting the value)
            $values[ $k ] = $v;
        }

        // (Getting the value)
        $values[ 'datetime.update' ] = DateTime::fetch();



        // (Setting the value)
        $object = new \stdClass();

        if ( !model( $this->model::class )->where( $filter )->and()->where( 'id', $input_id )->bind( $object, [ $main_key ] )->update( $values ) )
        {// (Unable to update the record)
            // Throwing the exception
            throw error( 6303, "Entity '{$this->model_name}' :: Unable to update the record" );
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
        $value = $input->get( $main_key );



        // (Getting the value)
        $description = $this->activity_description ?? "{$this->model_name} <b>{$object->$main_key}</b> has been updated" . ( $value === $object->$main_key ? '' : " :: Set <b>$main_key</b> to <b>$value</b>" );

        /*if ( $activity )
        {// Value found
            // (Getting the value)
            $props =
            [
                'model_name' => $this->model_name,
                'main_key'     =>
                [
                    'old'      => $object->$main_key,
                    'new'      => $value
                ]
            ]
            ;

            // (Getting the value)
            $description = $activity( $props );
        }*/



        // (Getting the value)
        $record =
        [
            'tenant'               => $this->user->tenant,
            'user'                 => $this->user->id,
            'action'               => $action,
            'description'          => $description,
            'session'              => $this->user->session ? $this->user->session->id : null,
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
            'resource.id'          => $input_id,
            'resource.key'         => $object->$main_key,
            'datetime.insert'      => DateTime::fetch()
        ]
        ;

        if ( !model( Activity::class )->insert( [ $record ] ) )
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
                'RESOURCE_ID'       => $resource ? $resource->id : null,
                'ELEMENT_ID'        => $input_id
            ]
        )
        ;
    }

    /**
     * Inserts a new element based on the provided input data, within the current user scope
     * @param DTO $input The input data transfer object containing the information for the new element
     * @return int The ID of the newly created element
     * @throws Error if WriteScope is not found for the model or if the insert operation fails
     */
    public function insert (DTO $input) : int
    {
        // (Getting the value)
        $model_keys = model_keys( $this->model );

        foreach ( $model_keys as $key )
        {// Processing each entry
            // (Setting the value)
            $key_filter = [];

            foreach ( $key->fields as $field )
            {// Processing each entry
                // (Appending the value)
                $key_filter[] = [ $field, $field === 'tenant' ? $this->user->tenant : $input->$field ];                
            }



            if ( model( $this->model::class )->where( $key_filter )->exists() )
            {// (Record found)
                // Throwing the exception
                throw error( 6301, "Entity '{$this->model_name}' :: Record key " . json_encode( $key->fields ) . " already exists" );
            }
        }



        // (Getting the value)
        $main_key = $model_keys[0] ? $model_keys[0]->name : null;



        // (Setting the value)
        $record = [];

        foreach ( dto_fieldset( $input )->fields as $k => $field )
        {// Processing each entry
            // (Getting the value)
            $record[ $field ] = $input->get( is_string( $k ) ? $k : $field );
        }

        foreach ( $this->input_values as $k => $v )
        {// Processing each entry
            // (Getting the value)
            $record[ $k ] = $v;
        }

        // (Getting the values)
        $record['tenant']          = $this->user->tenant;
        $record['owner']           = $this->user->id;
        $record['datetime.insert'] = DateTime::fetch();



        if ( !model( $this->model::class )->insert( [ $record ] ) )
        {// (Unable to insert the record)
            // Throwing the exception
            throw error( 6302, "Entity '{$this->model_name}' :: Unable to insert the record" );
        }



        // (Getting the value)
        $record_id = model( $this->model::class )->last_id();



        // (Getting the value)
        $resource = Resource::find( $this->model::class );

        if ( $resource )
        {// Match OK
            // (Getting the value)
            $record =
            [
                'tenant'        => $this->user->tenant,
                'user'          => $this->user->id,
                'resource'      => $resource->id,
                'element'       => $record_id,
                'share_rule'    => 1
            ]
            ;

            if ( !model( UserShareRule::class )->insert( [ $record ] ) )
            {// (Unable to insert the record)
                // Throwing the exception
                throw error( 6302, "Entity 'UserShareRule' :: Unable to insert the record" );
            }
        }



        // (Getting the values)
        $ip = isset( $this->client ) ? $this->client->ip : ip();
        $ua = isset( $this->client ) ? $this->client->ua : ua();



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
        $description = $this->activity_description ?? "{$this->model_name} <b>{$input->$main_key}</b> has been created";

        /*if ( $activity )
        {// Value found
            // (Getting the value)
            $props =
            [
                'model_name' => $this->model_name,
                'main_key'     => $input->$main_key
            ]
            ;

            // (Getting the value)
            $description = $activity( $props );
        }*/



        // (Getting the value)
        $record =
        [
            'tenant'               => $this->user->tenant,
            'user'                 => $this->user->id,
            'action'               => $action,
            'description'          => $description,
            'session'              => $this->user->session ? $this->user->session->id : null,
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
            'resource.id'          => $record_id,
            'resource.key'         => $input->$main_key,
            'datetime.insert'      => DateTime::fetch()
        ]
        ;

        if ( !model( Activity::class )->insert( [ $record ] ) )
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
                'RESOURCE_ID'       => $resource ? $resource->id : null,
                'ELEMENT_ID'        => $record_id
            ]
        )
        ;



        // Returning the value
        return $record_id;
    }

    /**
     * Deletes an element based on the provided element ID, within the current user scope
     * @throws Error if WriteScope is not found for the model or if the delete operation fails
     */
    public function delete () : array
    {
        // (Getting the value)
        $scope = WriteScope::find( $this->model::class, $this->user );

        if ( !$scope )
        {// Value not found
            // Throwing the exception
            throw error( 6000, "Entity '{$this->model_name}' :: WriteScope not found" );
        }



        // (Getting the value)
        $filter = $this->user->restrict( $scope );



        // (Getting the value)
        $resource = Resource::find( $this->model::class );



        // (Getting the value)
        $model_keys = model_keys( $this->model );



        // (Getting the value)
        $main_key = $model_keys[0] ? $model_keys[0]->name : null;



        // (Setting the value)
        $errors = [];

        foreach ( $this->element_ids as $id )
        {// Processing each entry
            if ( $resource )
            {// Value found
                // (Getting the value)
                $rule = ( new ShareService() )->get_rule( $this->user->tenant, $this->user->id, $resource->id, $id );

                if ( !$rule )
                {// (Rule not found)
                    // (Getting the value)
                    $errors[ $id ] = error( 6502, "User does not have the required permission 'update' on the entity '{$this->model_name}' with ID '{$id}'" );

                    // Continuing the iteration
                    continue;
                }

                if ( $rule->share_rule !== 1 )
                {// (Rule is 'read_only')
                    // (Getting the value)
                    $errors[ $id ] = error( 6502, "User does not have the required permission 'update' on the entity '{$this->model_name}' with ID '{$id}'" );

                    // Continuing the iteration
                    continue;
                }



                foreach ( [ UserShareRule::class, GroupShareRule::class ] as $model_class )
                {// Processing each entry
                    if ( !model( $model_class )->where( [ [ 'resource', $resource->id ], [ 'element', $id ] ] )->delete() )
                    {// (Unable to delete the records)
                        // (Getting the value)
                        $model_name = class_basename( $model_class );

                        // Throwing the exception
                        throw error( 6304, "Entity '{$model_name}' :: Unable to delete the records" );
                    }
                }
            }



            if ( !model( $this->model::class )->where( $filter )->and()->where( 'id', $id )->exists() )
            {// (Record not found)
                // (Getting the value)
                $errors[ $id ] = error( 6300, "Entity '{$this->model_name}' :: Record with ID '{$id}' not found" );

                // Continuing the iteration
                continue;
            }



            // (Getting the value)
            $object = new \stdClass();

            if ( !model( $this->model::class )->where( $filter )->and()->where( 'id', $id )->bind( $object, [ $main_key ] )->delete() )
            {// (Unable to delete the record)
                // Throwing the exception
                throw error( 6304, "Entity '{$this->model_name}' :: Unable to delete the record" );

                // Continuing the iteration
                continue;
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

                // Continuing the iteration
                continue;
            }



            // (Getting the value)
            $class_method = __FUNCTION__;



            // (Getting the value)
            $action = "{$this->model_name}.$class_method";



            // (Getting the value)
            $description = $this->activity_description ?? "{$this->model_name} <b>{$object->$main_key}</b> has been deleted";

            /*if ( $activity )
            {// Value found
                // (Getting the value)
                $props =
                [
                    'model_name' => $this->model_name,
                    'main_key'     => $object->$main_key
                ]
                ;

                // (Getting the value)
                $description = $activity( $props );
            }*/



            // (Getting the value)
            $record =
            [
                'tenant'               => $this->user->tenant,
                'user'                 => $this->user->id,
                'action'               => $action,
                'description'          => $description,
                'session'              => $this->user->session ? $this->user->session->id : null,
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
                'resource.id'          => $id,
                'resource.key'         => $object->range,
                'datetime.insert'      => DateTime::fetch()
            ]
            ;

            if ( !model( Activity::class )->insert( [ $record ] ) )
            {// (Unable to insert the record)
                // Throwing the exception
                throw error( 6302, "Entity 'Activity' :: Unable to insert the record" );

                // Continuing the iteration
                continue;
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
                    'RESOURCE_ID'       => $resource ? $resource->id : null,
                    'ELEMENT_ID'        => $id
                ]
            )
            ;
        }



        // Returning the value
        return $errors;
    }



    /**
     * Sets a specific field of an element based on the provided field name and value, within the current user scope
     * @param string $field The name of the field to update
     * @param mixed $value The new value to set for the specified field
     * @throws Error if WriteScope is not found for the model, if the user does not have the required permission, or if the update operation fails
     */
    public function set_field (string $field, mixed $value) : void
    {
        // (Getting the value)
        $scope = WriteScope::find( $this->model::class, $this->user );

        if ( !$scope )
        {// Value not found
            // Throwing the exception
            throw error( 6000, "Entity '{$this->model_name}' :: WriteScope not found" );
        }



        if ( $this->model instanceof \App\Models\User && $field === 'hierarchy' )
        {// Match OK
            // (Checking the input hierarchy)
            $this->check_input_hierarchy( $value );
        }



        // (Getting the value)
        $filter = $this->user->restrict( $scope );



        // (Getting the value)
        $resource = Resource::find( $this->model::class );

        if ( $resource )
        {// Value found
            // (Getting the value)
            $rule = ( new ShareService() )->get_rule( $this->user->tenant, $this->user->id, $resource->id, $this->element_id );

            if ( !$rule )
            {// (Rule not found)
                // Throwing the exception
                throw error( 6502, "User does not have the required permission 'update' on the entity '{$this->model_name}'" );
            }

            if ( $rule->share_rule !== 1 )
            {// (Rule is 'read_only')
                // Throwing the exception
                throw error( 6502, "User does not have the required permission 'update' on the entity '{$this->model_name}'" );
            }
        }



        // (Getting the value)
        $model_keys = model_keys( $this->model::class );

        

        // (Getting the value)
        $main_key = $model_keys[0] ? $model_keys[0]->name : null;



        // (Setting the value)
        $values = [];

        // (Getting the value)
        $values[ $field ] = $value;

        // (Getting the value)
        $values[ 'datetime.update' ] = DateTime::fetch();



        // (Setting the value)
        $bind_fields = [];

        if ( $main_key )
        {// Value found
            // (Appending the value)
            $bind_fields[] = $main_key;
        }

        // (Appending the value)
        $bind_fields[] = $field;



        // (Setting the value)
        $object = new \stdClass();

        if ( !model( $this->model::class )->where( $filter )->and()->where( 'id', $this->element_id )->bind( $object, $bind_fields )->update( $values ) )
        {// (Unable to update the record)
            // Throwing the exception
            throw error( 6303, "Entity '{$this->model_name}' :: Unable to update the record" );
        }



        // (Getting the values)
        $ip = isset( $this->client ) ? $this->client->ip : ip();
        $ua = isset( $this->client ) ? $this->client->ua : ua();



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
        $description = $this->activity_description ?? "{$this->model_name} <b>{$object->$main_key}</b> has been updated ( field <b>{$field}</b> )" . ( $value === $object->$field ? '' : " :: Set <b>$field</b> to <b>{$value}</b>" );

        /*if ( $activity )
        {// Value found
            // (Getting the value)
            $props =
            [
                'model_name' => $this->model_name,
                'main_key'     =>
                [
                    'old'      => $object->$main_key,
                    'new'      => $value
                ],
                'field'        => $field
            ]
            ;

            // (Getting the value)
            $description = $activity( $props );
        }*/



        // (Getting the value)
        $record =
        [
            'tenant'               => $this->user->tenant,
            'user'                 => $this->user->id,
            'action'               => $action,
            'description'          => $description,
            'session'              => $this->user->session ? $this->user->session->id : null,
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
            'resource.id'          => $this->element_id,
            'resource.key'         => $object->$main_key,
            'datetime.insert'      => DateTime::fetch()
        ]
        ;

        if ( !model( Activity::class )->insert( [ $record ] ) )
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
                'RESOURCE_ID'       => $resource ? $resource->id : null,
                'ELEMENT_ID'        => $this->element_id
            ]
        )
        ;
    }



    /**
     * Exports the elements of the entity based on the provided fields and filename, allowing for dynamic composition of the export data and file generation
     * @param array $fields An array of field names to be included in the export,
     * if empty, all fields will be included in the export
     * @param string $filename The name of the file to be generated for the export,
     * if not provided, a default name 'elements.csv' will be used
     * @throws Error if the export operation fails
     */
    public function export (array $fields = [], string $filename = 'elements.csv') : void
    {
        // (Getting the value)
        $scope = ReadScope::find( $this->model::class, $this->user );

        if ( !$scope )
        {// Value not found
            // Throwing the exception
            throw error( 6000, "Entity '{$this->model_name}' :: ReadScope not found" );
        }



        // (Restricting the user)
        $filter = $this->user->restrict( $scope );



        // (Composing the model)
        $this->model->where( $filter );



        if ( $this->element_ids )
        {// Value is not empty
            // (Composing the model)
            $this->model->and()->where( 'id', 'IN', $this->element_ids );
        }



        // (Getting the value)
        $resource = Resource::find( $this->model::class );

        if ( $resource )
        {// Value found
            // (Adapting the model to the resource)
            $this->adapt_model_to_resource();
        }



        // (Getting the value)
        $stream = new Stream( new CSV(), $filename );



        // (Sending the headers)
        $stream->head();



        // (Getting the value)
        $id_field = in_array( 'id', $fields );



        // (Getting the value)
        $temp_fields = [ 'id', ...$fields ];

        if ( $resource )
        {// Value found
            // (Appending the value)
            $temp_fields[] = 'priority';
        }



        // (Getting the value)
        $cursor = $this->model->cursor( $temp_fields );

        while ( $r = $cursor->read() )
        {// Processing each entry
            // (Setting the value)
            $record = [];

            foreach ( collection( $r->values )->compress() as $k => $v )
            {// Processing each entry
                if ( !$id_field )
                {// Value is false
                    if ( $k === 'id' ) continue;
                }

                if ( $resource )
                {// Value found
                    if ( $k === 'priority' ) continue;
                }



                // (Getting the value)
                $record[ $k ] = str_replace( "\n", '\\n', $v );
            }



            if ( !$stream->length )
            {// Value is not empty
                // (Sending the line)
                $stream->send( array_keys( $record ) );
            }



            // (Sending the line)
            $stream->send( $record );
        }



        // (Closing the stream)
        $stream->close();
    }

    /**
     * Imports elements into the entity based on the provided input stream, allowing for dynamic processing of the input data and record creation
     * @param ReadableStream $input The input stream containing the data to be imported,
     * the stream should be in CSV format with the first line containing the field names and subsequent lines containing the corresponding values for each record
     * @return array An array of errors encountered during the import process, if any
     * @throws Error if the import operation fails
     */
    public function import (ReadableStream $input) : array
    {
        // (Getting the value)
        $resource = Resource::find( $this->model::class );



        // (Getting the value)
        $class_method = __FUNCTION__;



        // (Setting the value)
        $errors = [];



        // (Setting the value)
        $fields = [];



        // (Setting the value)
        $num_lines = 0;



        // (Getting the value)
        $stream = $input->open();

        while ( $line = fgetcsv( $stream->get_resource(), null, ';' ) )
        {// Processing each entry
            // (Incrementing the value)
            $num_lines += 1;



            // (Getting the value)
            $line_index = $num_lines;


            if ( $fields )
            {// (Line is a record)
                // (Getting the value)
                $record = array_combine( $fields, $line );

                foreach ( $record as $k => $v )
                {// Processing each entry
                    // (Getting the value)
                    $record[ $k ] = str_replace( '\\n', "\n", $v );
                }



                if ( model( $this->model::class )->where( [ [ 'tenant', $this->user->tenant ], [ 'name', $record['name'] ] ] )->exists() )
                {// (Record found)
                    // (Appending the value)
                    $errors[] = "Line {$line_index} -> Entity '{$this->model_name}' :: Record key ['tenant','name'] already exists";

                    // Continuing the iteration
                    continue;
                }



                // (Getting the value)
                $record =
                [
                    'tenant'                  => $this->user->tenant,
                    'owner'                   => $this->user->id,
                    'name'                    => $record['name'],
                    'description'             => $record['description'] ? $record['description'] : null,
                    'datetime.insert'         => DateTime::fetch()
                ]
                ;

                if ( !$this->model->insert( [ $record ] ) )
                {// (Unable to insert the record)
                    // (Appending the value)
                    $errors[] = "Line {$line_index} -> Entity '{$this->model_name}' :: Unable to insert the record";

                    // Continuing the iteration
                    continue;
                }



                // (Getting the value)
                $record_id = $this->model->last_id();



                if ( $resource )
                {// Value found
                    // (Getting the value)
                    $record =
                    [
                        'tenant'        => $this->user->tenant,
                        'user'          => $this->user->id,
                        'resource'      => $resource->id,
                        'element'       => $record_id,
                        'share_rule'    => 1
                    ]
                    ;

                    if ( !model( UserShareRule::class )->insert( [ $record ] ) )
                    {// (Unable to insert the record)
                        // (Appending the value)
                        $errors[] = "Line {$line_index} -> Resource 'UserShareRule' :: Unable to insert the record";

                        // Continuing the iteration
                        continue;
                    }
                }
            }
            else
            {// (Line is the header)
                // (Getting the value)
                $fields = $line;
            }
        }

        // (Closing the stream)
        $stream->close();



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
        $action = "{$this->model_name}.$class_method";



        // (Getting the value)
        $record =
        [
            'tenant'               => $this->user->tenant,
            'user'                 => $this->user->id,
            'action'               => $action,
            'description'          => "{$this->model_name} records have been imported ( " . ( $num_lines - 1 ) . " )",
            'session'              => $this->user->session ? $this->user->session->id : null,
            'ip'                   => $ip,
            'user_agent'           => $ua,
            'ip_info.country.code' => $client['ip']['country']['code'],
            'ip_info.country.name' => $client['ip']['country']['name'],
            'ip_info.isp'          => $client['ip']['isp'],
            'ua_info.browser'      => $client['ua']['browser'],
            'ua_info.os'           => $client['ua']['os'],
            'ua_info.hw'           => $client['ua']['hw'],
            'resource.type'        => $this->model_name,
            'resource.action'      => 'insert',
            'resource.id'          => null,
            'resource.key'         => null,
            'datetime.insert'      => DateTime::fetch()
        ]
        ;

        if ( !model( Activity::class )->insert( [ $record ] ) )
        {// (Unable to insert the record)
            // Throwing the exception
            throw error( 6302, "Resource 'Activity' :: Unable to insert the record" );
        }



        // Returning the value
        return [ 'errors' => $errors ];
    }



    /**
     * Setting the user to apply the scope restrictions and permissions for the operations performed on the entity
     * @param User $user The user object representing the current user
     * @return static Returns the current instance of the Entity class for method chaining
     */
    public function user (User $user) : static
    {
        // (Getting the value)
        $this->user = $user;



        // (Composing the model)
        #$this->model->where( 'tenant', $user->tenant );



        // Returning the value
        return $this;
    }

    /**
     * Setting the client information to be used for logging and activity tracking purposes
     * @param Client $client The client object representing the current client's information
     * @return static Returns the current instance of the Entity class for method chaining
     */
    public function client (Client $client) : static
    {
        // (Getting the value)
        $this->client = $client;



        // Returning the value
        return $this;
    }



    /**
     * Setting the element ID to be used for operations on a specific element
     * @param int $id The ID of the element
     * @return static Returns the current instance of the Entity class for method chaining
     */
    public function element (int $id) : static
    {
        // (Getting the value)
        $this->element_id = $id;



        // Returning the value
        return $this;
    }

    /**
     * Setting multiple element IDs to be used for operations on specific elements
     * @param array $ids An array of element IDs
     * @return static Returns the current instance of the Entity class for method chaining
     */
    public function elements (array $ids) : static
    {
        // (Getting the value)
        $this->element_ids = $ids;



        // Returning the value
        return $this;
    }



    /**
     * Setting the input values to be used for the operations performed on the entity, allowing for dynamic composition of the model's data
     * @param array $values An associative array of input values to be used for the operations, where the keys represent the field names and the values represent the corresponding values to be set
     * @return static Returns the current instance of the Entity class for method chaining
     */
    public function input (array $values) : static
    {
        // (Getting the value)
        $this->input_values = $values;



        // Returning the value
        return $this;
    }



    /**
     * Setting the activity description to be used for logging and activity tracking purposes
     * @param string $description The description of the activity to be logged
     * @return static Returns the current instance of the Entity class for method chaining
     */
    public function log (string $description) : static
    {
        // (Getting the value)
        $this->activity_description = $description;



        // Returning the value
        return $this;
    }



    /**
     * Applying a filter to the model based on the provided callback function, allowing for dynamic composition of the model's condition
     * @param callable $callback A callback function that receives the model's condition as an argument
     * @return static Returns the current instance of the Entity class for method chaining
     */
    public function filter (callable $callback) : static
    {
        // (Calling the function)
        $callback( $this->model->condition );



        // (Composing the model)
        $this->model->and();



        // Returning the value
        return $this;
    }

    /**
     * Linking the model to related models based on the provided array of links, allowing for dynamic composition of the model's relationships
     * @param array $links An array of links representing the relationships to be established between the
     * model and related models, where each link is defined as an associative array with keys 'model', 'type', and 'on' specifying the related model, the type of relationship, and the conditions for linking, respectively
     * @return static Returns the current instance of the Entity class for method chaining
     */
    public function link (array $links) : static
    {
        // (Linking the model)
        $this->model->link( $links );



        // Returning the value
        return $this;
    }

    /**
     * Applying pagination to the model based on the provided PaginatorDTO object, allowing for dynamic composition of the model's pagination parameters
     * @param PaginatorDTO $input The PaginatorDTO object containing the pagination parameters such
     * as length, cursor, sort field, and sort direction to be applied to the model
     * @return static Returns the current instance of the Entity class for method chaining
     */
    public function paginate (PaginatorDTO $input) : static
    {
        // (Getting the value)
        $this->paginator = $input;



        // Returning the value
        return $this;
    }

    /**
     * Setting the values to be used for the operations performed on the entity, allowing for dynamic composition of the model's data
     * @param array $values An associative array of values to be used for the operations, where the keys represent the field names and the values represent the corresponding values to be set
     * @return static Returns the current instance of the Entity class for method chaining
     */
    public function values (array $values) : static
    {
        // (Getting the value)
        $this->values = $values;



        // Returning the value
        return $this;
    }
}



?>