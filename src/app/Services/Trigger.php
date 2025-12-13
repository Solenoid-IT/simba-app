<?php



namespace App\Services;



use \Solenoid\X\HTTP\Request;

use \App\Entity;

use \App\Models\TriggerView as TriggerViewModel;
use \App\Models\Trigger as TriggerModel;



class Trigger extends Entity
{
    public function __construct ()
    {
        // (Calling the function)
        parent::__construct( TriggerModel::class );
    }



    private static function match_event (string $events) : bool
    {
        // (Setting the value)
        $matches = [];

        foreach ( explode( "\n", $events ) as $line )
        {// Processing each entry
            // (Getting the value)
            $line = trim( $line );



            if ( strpos( $line, '#' ) === 0 ) continue;

            if ( $line === '' ) continue;



            // (Getting the values)
            [ $resource, $method ] = explode( '.', $line, 2 );



            if ( $resource === '*' || $resource === action()->class )
            {// Match OK
                if ( $method === '*' || $method === action()->method )
                {// Match OK
                    // (Appending the value)
                    $matches[] = $line;
                }
            }
        }



        // Returning the value
        return !( $events !== '' && count( $matches ) === 0 );
    }

    private static function fill (string $content, array $values) : string
    {
        foreach ( $values as $k => $v )
        {// Processing each entry
            // (Replacing the value)
            $content = str_replace( '{ ' . $k . ' }', $v, $content );
        }



        // Returning the value
        return $content;
    }



    public function run_async (int $tenant_id, array $envs = []) : array
    {
        // (Getting the value)
        $envs['EVENT_TIMESTAMP'] = date( 'c' );



        // (Setting the value)
        $processes = [];

        foreach ( TriggerModel::fetch()->where( [ [ 'tenant', $tenant_id ], [ 'enabled', 'IS', true ] ] )->list() as $record )
        {// Processing each entry
            if ( !self::match_event( $record->events ?? '' ) ) continue;



            // (Getting the value)
            $request = Request::parse( "HTTP/1.1 GET /\r\n" . str_replace( "\n", "\r\n", self::fill( $record->get( 'request.content' ), $envs ) ) );



            // (Getting the value)
            $input =
            [
                'method'           => $record->get( 'request.method' ),
                'url'              => self::fill( $record->get( 'request.url' ), $envs ),
                'headers'          => $request->headers,
                'body'             => str_replace( [ "\r", "\n", "\t" ], '', (string) $request->body ),
                'response_timeout' => $record->response_timeout
            ]
            ;

            // (Starting the process)
            $process = run_async( 'OnDemand/SendRequest', [], json_encode( $input ) );



            // (Appending the value)
            $processes[] = $process;
        }



        // Returning the value
        return $processes;
    }



    public function list (array $fields = []) : array
    {
        // (Getting the value)
        $entity = entity( TriggerViewModel::class );

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