<?php



namespace App\Services;



use \Solenoid\MySQL\DateTime;

use \App\User;
use \App\Element;

use \App\Models\UserShareRule as UserShareRuleModel;
use \App\Models\User as UserModel;
use \App\Models\Activity as ActivityModel;
use \App\Models\ShareRule as ShareRuleModel;

use \App\DTOs\UserShareRule\UpdateDTO;
use \App\DTOs\UserShareRule\InsertDTO;

use \App\Services\Resource as ResourceService;
use \App\Services\Client as ClientService;
use \App\Services\Share as ShareService;
use \App\Services\Trigger as TriggerService;



class UserShareRule
{
    private string $class_basename;
    private User   $user;



    public function __construct ()
    {
        // (Getting the value)
        $this->class_basename = class_basename( __CLASS__ );



        // (Getting the value)
        $this->user = user();
    }



    public function find (int $id, array $fields = []) : Element|null
    {
        // (Getting the value)
        $record = model( UserShareRuleModel::class )->where( [ [ 'tenant', $this->user->tenant ], [ 'id', $id ] ] )->find( $fields );

        if ( !$record )
        {// (Record not found)
            // Returning the value
            return null;
        }



        // Returning the value
        return element( $record );
    }



    public function upsert (UpdateDTO|InsertDTO $input) : int
    {
        if ( $this->user->hierarchy === 3 )
        {// (User is a viewer)
            // Throwing the exception
            throw error( 6101, 'User is a viewer' );
        }



        // (Getting the value)
        $resource = new ResourceService();



        if ( $input->id )
        {// Value found
            // (Getting the value)
            $user_share_rule = model( UserShareRuleModel::class)->where( [ [ 'tenant', $this->user->tenant ], [ 'id', $input->id ] ] )->find();

            if ( $user_share_rule )
            {// (Record found)
                // (Getting the values)
                $input->resource = $user_share_rule->resource;
                $input->element  = $user_share_rule->element;
            }
        }



        // (Getting the value)
        $resource_type = $resource->get_name( $input->resource );

        if ( !$resource_type )
        {// Value not found
            // Throwing the exception
            throw error( 6300, "Entity '{$this->class_basename}' :: Record not found" );
        }



        // (Getting the value)
        $target_user = UserModel::fetch()->where( [ [ 'tenant', $this->user->tenant ], [ 'id', $input->user ] ] )->find( [ 'name' ] )->name;

        if ( !$target_user )
        {// Value not found
            // Throwing the exception
            throw error( 6101, 'Target user not found' );
        }



        // (Getting the value)
        $rule = ( new ShareService() )->get_rule( $this->user->tenant, $this->user->id, $input->resource, $input->element );

        if ( !$rule || $rule->share_rule !== 1 )
        {// (User not authorized)
            // Throwing the exception
            throw error( 6101, 'User not authorized' );
        }



        // (Getting the value)
        $res = $resource->get( $this->user->tenant, $input->resource, $input->element );

        if ( !$res )
        {// (Record not found)
            // Throwing the exception
            throw error( 6300, "$resource_type record not found" );
        }



        if ( $input->user === $res['owner'] )
        {// (User is the owner of resource)
            // Throwing the exception
            throw error( 6101, 'User not authorized' );
        }



        // (Setting the value)
        $old_share_rule = null;



        // (Setting the value)
        $action = null;



        // (Setting the value)
        $record_id = null;



        // (Getting the value)
        $r = model( UserShareRuleModel::class )->where( [ [ 'tenant', $this->user->tenant ], [ 'user', $input->user ], [ 'resource', $input->resource ], [ 'element', $input->element ] ] )->find( [ 'id', 'share_rule' ] );

        if ( $r )
        {// (Record found)
            // (Getting the value)
            $values =
            [
                'share_rule' => $input->share_rule
            ]
            ;

            if ( !model( UserShareRuleModel::class )->where( 'id', $r->id )->update( $values ) )
            {// (Unable to update the record)
                // Throwing the exception
                throw error( 6303, "Entity '{$this->class_basename}' :: Unable to update the record" );
            }



            // (Setting the value)
            $action = 'update';



            // (Getting the value)
            $record_id = $r->id;



            // (Getting the value)
            $old_share_rule = $r->share_rule;
        }
        else
        {// (Record not found)
            // (Getting the value)
            $record =
            [
                'tenant'        => $this->user->tenant,
                'user'          => $input->user,
                'resource'      => $input->resource,
                'element'       => $input->element,
                'share_rule'    => $input->share_rule
            ]
            ;

            if ( !model( UserShareRuleModel::class )->insert( [ $record ] ) )
            {// (Unable to insert the record)
                // Throwing the exception
                throw error( 6302, "Entity '{$this->class_basename}' :: Unable to insert the record" );
            }



            // (Setting the value)
            $action = 'insert';



            // (Getting the value)
            $record_id = model( UserShareRuleModel::class )->last_id();
        }



        // (Getting the value)
        $resource_path = $resource->get( $this->user->tenant, $input->resource, $input->element )['path'];



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
        $old_share_rule = $old_share_rule ? ShareRuleModel::fetch()->where( 'id', $old_share_rule )->find( [ 'name' ] )->name : null;



        // (Getting the value)
        $share_rule = ShareRuleModel::fetch()->where( 'id', $input->share_rule )->find( [ 'name' ] )->name;



        switch ( $action )
        {
            case 'insert':
                // (Getting the value)
                $description = "$resource_type <b>$resource_path</b> has been shared with user <b>$target_user</b> with share rule <b>$share_rule</b>";
            break;

            case 'update':
                // (Getting the value)
                $description = "Sharing of $resource_type <b>$resource_path</b> with user <b>$target_user</b> has been updated from <b>$old_share_rule</b> to <b>$share_rule</b>";
            break;
        }



        // (Getting the value)
        $class_method = __FUNCTION__;



        // (Getting the value)
        $action = "{$this->class_basename}.$class_method";



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
            'resource.type'        => $this->class_basename,
            'resource.action'      => $action,
            'resource.id'          => $record_id,
            'resource.key'         => null,
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
                'EVENT_SOURCE'      => $this->class_basename,

                'TENANT_ID'         => $this->user->tenant,
                'USER_ID'           => $this->user->id,
                'RESOURCE_ID'       => $input->resource ?? $rule->resource,
                'ELEMENT_ID'        => $record_id
            ]
        )
        ;



        // Returning the value
        return $record_id;
    }

    public function delete (array $input) : void
    {
        if ( $this->user->hierarchy === 3 )
        {// (User is a viewer)
            // Throwing the exception
            throw error( 6101, 'User is a viewer' );
        }



        // (Getting the value)
        $resource = new ResourceService();



        // (Getting the value)
        $share = new ShareService();



        foreach ( $input as $id )
        {// Processing each entry
            // (Getting the value)
            $user_share_rule = model( UserShareRuleModel::class )->where( [ [ 'tenant', $this->user->tenant ], [ 'id', $id ] ] )->find();

            if ( !$user_share_rule )
            {// (Record not found)
                // Throwing the exception
                throw error( 6300, "Entity '{$this->class_basename}' :: Record not found" );
            }



            // (Getting the value)
            $rule = $share->get_rule( $this->user->tenant, $this->user->id, $user_share_rule->resource, $user_share_rule->element );

            if ( !$rule || $rule->share_rule !== 1 )
            {// (User not authorized)
                // Throwing the exception
                throw error( 6101, 'User not authorized' );
            }



            // (Getting the value)
            $resource_type = $resource->get_name( $user_share_rule->resource );

            if ( !$resource_type )
            {// Value not found
                // Throwing the exception
                throw error( 6300, 'EntityType record not found' );
            }



            // (Getting the value)
            $res = $resource->get( $this->user->tenant, $user_share_rule->resource, $user_share_rule->element );

            if ( !$res )
            {// (Record not found)
                // Throwing the exception
                throw error( 6300, "$resource_type record not found" );
            }



            if ( $user_share_rule->user === $res['owner'] )
            {// (User is the owner of resource)
                // Throwing the exception
                throw error( 6101, 'User not authorized' );
            }



            if ( !model( UserShareRuleModel::class )->where( 'id', $id )->delete() )
            {// (Unable to delete the record)
                // Throwing the exception
                throw error( 6304, "Entity '{$this->class_basename}' :: Unable to delete the record" );
            }



            // (Getting the value)
            $resource_path = $resource->get( $this->user->tenant, $user_share_rule->resource, $user_share_rule->element )['path'];



            // (Getting the value)
            $target_user = UserModel::fetch()->where( [ [ 'tenant', $this->user->tenant ], [ 'id', $user_share_rule->user ] ] )->find( [ 'name' ] )->name;

            if ( !$target_user )
            {// Value not found
                // Throwing the exception
                throw error( 6300, 'Target user not found' );
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
                'tenant'               => $this->user->tenant,
                'user'                 => $this->user->id,
                'action'               => (string) action(),
                'description'          => "$resource_type <b>$resource_path</b> has been unshared with user <b>$target_user</b>",
                'session'              => $this->user->session ? $this->user->session->id : null,
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
                'resource.id'          => $id,
                'resource.key'         => null,
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
                    'EVENT_SOURCE'      => $this->class_basename,

                    'TENANT_ID'         => $this->user->tenant,
                    'USER_ID'           => $this->user->id,
                    'RESOURCE_ID'       => $user_share_rule->resource,
                    'ELEMENT_ID'        => $id
                ]
            )
            ;
        }
    }
}



?>