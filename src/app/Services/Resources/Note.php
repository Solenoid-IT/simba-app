<?php



namespace App\Services\Resources;



use \App\Entity;
use \App\Element;

use \App\Models\Resources\Note as NoteModel;
use \App\Models\Resources\NoteShareView as NoteShareViewModel;



class Note extends Entity
{
    public function __construct ()
    {
        // (Calling the function)
        parent::__construct( NoteModel::class );
    }



    public function find (array $fields = []) : Element|null
    {
        // (Getting the value)
        $element = entity( NoteShareViewModel::class )->element( $this->element_id )->find( $fields );

        // (Setting the value)
        $element->set( 'share_rule', share_rule()->share_rule );



        // Returning the value
        return $element;
    }

    public function list (array $fields = []) : array
    {
        /* ahcid to implementt
        
        // (Getting the value)
        $extender = function () use ($user)
        {
            // Returning the value
            return
            [
                'share_rules' => ( new ShareRuleService() )->list_index(),
                'groups'      => ( new GroupService() )->list_index( $user )
            ]
            ;
        }
        ;
        
        */



        // (Getting the value)
        $entity = entity( NoteShareViewModel::class );

        if ( $this->paginator )
        {// Value found
            // (Composing the entity)
            $entity->paginate( $this->paginator );
        }



        // Returning the value
        return $entity->list( $fields );
    }



    public function export (array $fields = [], string $filename = 'notes.csv') : void
    {
        // (Exporting the records)
        entity( NoteShareViewModel::class )->elements( $this->element_ids )->export( $fields, $filename );
    }
}



?>