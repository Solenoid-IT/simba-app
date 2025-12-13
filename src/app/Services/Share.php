<?php



namespace App\Services;



use \App\Models\UserShareRule as UserShareRuleModel;
use \App\Models\GroupShareRule as GroupShareRuleModel;
use \App\Models\GroupUser as GroupUserModel;



class Share
{
    public function get_rule (int $tenant_id, int $user_id, int $resource_id, int $element_id)
    {
        // (Getting the value)
        $user_share_rule = UserShareRuleModel::fetch()->where( [ [ 'tenant', $tenant_id ], [ 'user', $user_id ], [ 'resource', $resource_id ], [ 'element', $element_id ] ] )->find( [ 'share_rule' ] );

        if ( $user_share_rule )
        {// (Record found)
            // Returning the value
            return $user_share_rule;
        }
        else
        {// (Record not found)
            foreach ( GroupUserModel::fetch()->where( [ [ 'tenant', $tenant_id ], [ 'user', $user_id ] ] )->list( [ 'group' ] ) as $record )
            {// Processing each entry
                // (Getting the value)
                $group_id = $record->group;



                // (Getting the value)
                $group_share_rule = GroupShareRuleModel::fetch()->where( [ [ 'tenant', $tenant_id ], [ 'group', $group_id ], [ 'resource', $resource_id ], [ 'element', $element_id ] ] )->order( [ 'id' => 'ASC' ] )->find( [ 'group', 'share_rule' ] );

                if ( $group_share_rule )
                {// (Record found)
                    // Returning the value
                    return $group_share_rule;
                }
            }
        }



        // Returning the value
        return false;
    }
}



?>