<?php



namespace App\Services;



use \App\Entity;

use \App\Models\Activity as ActivityModel;
use \App\Models\ActivityView as ActivityViewModel;
use \App\Models\Tenant as TenantModel;



class Activity extends Entity
{
    public function __construct ()
    {
        // (Calling the function)
        parent::__construct( ActivityModel::class );
    }



    public function list (array $fields = []) : array
    {
        // (Getting the value)
        $entity = entity( ActivityViewModel::class );



        // (Composing the entity)
        $entity->user( $this->user );

        if ( $this->paginator )
        {// Value found
            // (Composing the entity)
            $entity->paginate( $this->paginator );
        }



        // Returning the value
        return $entity->list( $fields );
    }

    public function delete (?string $tenant = null) : array
    {
        // (Getting the value)
        $model = model( ActivityModel::class );

        if ( $tenant )
        {// Value found
            // (Getting the value)
            $tenant_id = model( TenantModel::class )->where( 'name', $tenant )->find( [ 'id' ] )->id;

            if ( !$tenant_id )
            {// (Record not found)
                // Throwing the exception
                throw error( 6300, "Entity 'Tenant' :: Record not found" );
            }



            // (Getting the value)
            $model = $model->where( 'tenant', $tenant_id );
        }



        if ( !$model->delete() )
        {// (Unable to delete the records)
            // Throwing the exception
            throw error( 6304, "Entity '{$this->model_name}' :: Unable to delete the records" );
        }



        // Returning the value
        return [];
    }
}



?>