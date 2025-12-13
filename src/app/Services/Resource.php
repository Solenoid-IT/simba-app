<?php



namespace App\Services;



use \App\User;

use \App\Models\Resources\Resource as ResourceModel;
use \App\Models\Resources\Note as NoteModel;

use \App\Models\User as UserModel;

use \App\Models\UserShareRule as UserShareRuleModel;
use \App\Models\GroupShareRule as GroupShareRuleModel;

use \App\DTOs\ResourceShareFindDTO;

use \App\Services\Resource as ResourceService;
use \App\Services\Share as ShareService;



class Resource
{
    public function get_name (int $type)
    {
        // Returning the value
        return ResourceModel::fetch()->where( 'id', $type )->find( [ 'name' ] )->name;
    }

    public function get (int $tenant_id, int $resource, int $element)
    {
        // (Setting the value)
        $model = null;



        // (Setting the value)
        $path = '';



        switch ( $resource )
        {
            case 1:# 'Note'
                // (Getting the value)
                $record = NoteModel::fetch()->where( [ [ 'tenant', $tenant_id ], [ 'id', $element ] ] )->find( [ 'owner', 'name' ] );

                if ( !$record )
                {// (Record not found)
                    // Returning the value
                    return false;
                }



                // (Setting the value)
                $model = 'Note';



                // (Getting the value)
                $path = $record->name;
            break;

            default:
                // Returning the value
                return false;
        }



        // Returning the value
        return
        [
            'model'       => $model,
            'path'        => $path,

            'name'        => $record->name,
            'owner'       => $record->owner
        ]
        ;
    }



    public function get_share_info (User $user, ResourceShareFindDTO $input)
    {
        // (Getting the value)
        $user = UserModel::fetch()->where( 'id', $user->id )->find( [ 'id', 'tenant' ] );

        if ( !$user )
        {// (Record not found)
            // Throwing the exception
            throw error( 6101, 'User not found' );
        }



        // (Getting the value)
        $rule = ( new ShareService() )->get_rule( $user->tenant, $user->id, $input->resource, $input->element );

        if ( !$rule )
        {// (User is not authorized)
            // Throwing the exception
            throw error( 6101, 'User not authorized' );
        }



        // (Getting the value)
        $owner = ( new ResourceService() )->get( $user->tenant, $input->resource, $input->element )['owner'];



        // (Getting the value)
        $user_rules = UserShareRuleModel::fetch()->where( [ [ 'tenant', $user->tenant ], [ 'resource', $input->resource ], [ 'element', $input->element ] ] )->and()->where( 'user', '<>', $owner )->list( [ 'id', 'user', 'share_rule' ] );
        $user_rules = array_map( fn ($rule) => $rule->values, $user_rules );



        // (Getting the value)
        $group_rules = GroupShareRuleModel::fetch()->where( [ [ 'tenant', $user->tenant ], [ 'resource', $input->resource ], [ 'element', $input->element ] ] )->list( [ 'id', 'group', 'share_rule' ] );
        $group_rules = array_map( fn ($rule) => $rule->values, $group_rules );



        // (Getting the value)
        $data =
        [
            'owner'       => $owner,

            'user_rules'  => $user_rules,
            'group_rules' => $group_rules,

            'share_rule'  => $rule->share_rule
        ]
        ;



        // Returning the value
        return $data;
    }
}



?>