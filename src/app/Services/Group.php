<?php



namespace App\Services;



use \Solenoid\X\Data\DTO;

use \App\Entity;
use \App\Element;

use \App\Models\Group as GroupModel;
use \App\Models\GroupView as GroupViewModel;
use \App\Models\GroupUser as GroupUserModel;
use \App\Models\User as UserModel;

use \App\DTOs\Group\UpdateDTO;
use \App\DTOs\Group\InsertDTO;



class Group extends Entity
{
    public function __construct ()
    {
        // (Calling the function)
        parent::__construct( GroupModel::class );
    }



    public function find (array $fields = []) : Element|null
    {
        // (Setting the value)
        $users = [];

        foreach ( model( GroupUserModel::class )->where( [ [ 'tenant', $this->user->tenant ], [ 'group', $this->element_id ] ] )->list( [ 'user' ] ) as $record )
        {// Processing each entry
            // Appending the value
            $users[] = $record->user;
        }



        // (Getting the value)
        $element = parent::/*link( [ [ model( GroupUserModel::class ), [ 'user' ] ] ] )->*/find( $fields );

        if ( !$element )
        {// (Element not found)
            // Returning the value
            return null;
        }



        // (Setting the value)
        $element->set( 'users', $users );



        // Returning the value
        return $element;
    }

    public function list (array $fields = []) : array
    {
        // (Getting the value)
        $entity = entity( GroupViewModel::class );

        if ( $this->paginator )
        {// Value found
            // (Composing the entity)
            $entity->paginate( $this->paginator );
        }



        /* ahcid to deleted

        // (Setting the value)
        $users = [];

        foreach ( model( UserModel::class )->where( 'tenant', $this->user->tenant )->list( [ 'id', 'name', 'hierarchy' ] ) as $record )
        {// Processing each entry
            // (Getting the value)
            $users[ $record->id ] =
            [
                'id'              => $record->id,
                'name'            => $record->name,

                'hierarchy_color' => model( HierarchyModel::class )->where( 'id', $record->hierarchy )->find( [ 'color' ] )->color
            ]    
            ;
        }

        */



        // (Getting the value)
        $data = $entity/*->link( [ [ model( GroupUserModel::class ), [ 'user' ] ] ] )*/->list( $fields );

        /* ahcid to deleted

        foreach ( $data['elements'] as $element )
        {// Processing each entry
            // (Getting the value)
            $element->set( 'users', $users[ $element->id ] );
        }

        */



        // Returning the value
        return $data;
    }



    public function update (DTO $input) : void
    {
        if ( !$input instanceof UpdateDTO ) throw error( 6000, 'Invalid input DTO' );



        if ( !model( GroupUserModel::class )->where( [ [ 'tenant', $this->user->tenant ], [ 'group', $this->element_id ] ] )->delete() )
        {// (Unable to delete records)
            // Throwing the exception
            throw error( 6304, "Entity 'GroupUser' :: Unable to delete records" );
        }



        if ( !model( GroupModel::class )->where( [ [ 'tenant', $this->user->tenant ], [ 'id', $this->element_id ] ] )->exists() )
        {// (Record not found)
            // Throwing the exception
            throw error( 6300, "Group {$this->element_id} does not exist" );
        }



        foreach ( $input->get( 'users' ) as $user_id )
        {// Processing each entry
            if ( !model( UserModel::class )->where( [ [ 'tenant', $this->user->tenant ], [ 'id', $user_id ] ] )->exists() )
            {// (Record not found)
                // Throwing the exception
                throw error( 6300, "User {$user_id} does not exist" );
            }



            // (Getting the value)
            $record =
            [
                'tenant' => $this->user->tenant,
                'group'  => $this->element_id,
                'user'   => $user_id,
            ]
            ;

            if ( !model( GroupUserModel::class )->insert( [ $record ] ) )
            {// (Unable to insert the record)
                // Throwing the exception
                throw error( 6302, "Entity 'GroupUser' :: Unable to insert the record" );
            }
        }



        // (Updating the element)
        parent::update( $input );
    }

    public function insert (DTO $input) : int
    {
        if ( !$input instanceof InsertDTO ) throw error( 6000, 'Invalid input DTO' );



        // (Inserting the elements)
        $element_id = parent::insert( $input );

        foreach ( $input->get( 'users' ) as $user_id )
        {// Processing each entry
            // (Getting the value)
            $record =
            [
                'tenant' => $this->user->tenant,
                'group'  => $element_id,
                'user'   => $user_id,
            ]
            ;

            if ( !model( GroupUserModel::class )->insert( [ $record ] ) )
            {// (Unable to insert the record)
                // Throwing the exception
                throw error( 6302, "Entity 'GroupUser' :: Unable to insert the record" );
            }
        }



        // Returning the value
        return $element_id;
    }



    public function list_index () : array
    {
        // (Setting the value)
        $elements = [];

        foreach ( $this->list( [ 'id', 'name' ] ) as $element )
        {// Processing each entry
            // (Getting the value)
            $elements[ $element->id ] = $element;
        }



        // Returning the value
        return $elements;
    }
}



?>