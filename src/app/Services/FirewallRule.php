<?php



namespace App\Services;



use \App\Entity;

use \App\Models\FirewallRule as FirewallRuleModel;
use \App\Models\FirewallRuleView as FirewallRuleViewModel;



class FirewallRule extends Entity
{
    public function __construct ()
    {
        // (Calling the function)
        parent::__construct( FirewallRuleModel::class );
    }



    public function list (array $fields = []) : array
    {
        // (Getting the value)
        $entity = entity( FirewallRuleViewModel::class );

        if ( $this->paginator )
        {// Value found
            // (Composing the entity)
            $entity->paginate( $this->paginator );
        }



        // Returning the value
        return $entity->list( $fields );
    }
}



?>