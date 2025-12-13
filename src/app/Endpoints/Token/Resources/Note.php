<?php



namespace App\Endpoints\Token\Resources;



use \Solenoid\X\Data\Input;
use \Solenoid\X\Data\ArrayList;
use \Solenoid\X\Data\Types\IntValue;
use \Solenoid\X\Data\ReadableStream;

use \App\DTOs\Resources\Note\UpdateDTO;
use \App\DTOs\Resources\Note\InsertDTO;

use \App\Services\Resources\Note as NoteService;



class Note
{
    public function __construct (private NoteService $service) {}



    #[ Input( new IntValue( 'id', true, 'ID of the record', 1 ) ) ]
    public function find (int $id)
    {
        // Returning the value
        return $this->service->element( $id )->find( [ 'id', 'name', 'description' ] );
    }

    public function list ()
    {
        // Returning the value
        return $this->service->list( [ 'id', 'owner', 'name', 'datetime.insert', 'datetime.update', 'share_rule' ] );
    }



    #[ Input( UpdateDTO::class ) ]
    public function update (UpdateDTO $input)
    {
        // Returning the value
        return $this->service->update( $input );
    }

    #[ Input( InsertDTO::class ) ]
    public function insert (InsertDTO $input)
    {
        // Returning the value
        return $this->service->insert( $input );
    }

    #[ Input( new ArrayList( new IntValue( 'id', true, 'ID of the record', 1 ) ) ) ]
    public function delete (array $ids)
    {
        // Returning the value
        return $this->service->elements( $ids )->delete();
    }



    #[ Input( new ArrayList( new IntValue( 'id', true, 'ID of the record', 1 ) ) ) ]
    public function export (array $ids)
    {
        // (Exporting records)
        $this->service->elements( $ids )->export( [ 'name', 'description', 'datetime.insert', 'datetime.update' ], 'notes.csv' );
    }

    #[ Input( new ReadableStream( 'csv_content' ) ) ]
    public function import ()
    {
        // Returning the value
        return $this->service->import( request()->body );
    }
}



?>