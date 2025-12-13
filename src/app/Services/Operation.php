<?php



namespace App\Services;



use \Solenoid\MySQL\DateTime;
use \Solenoid\MySQL\Record;

use \Solenoid\KeyGen\Token;

use \App\Models\Operation as Entity;

use \App\Services\Client as ClientService;
use \App\Services\Mail as MailService;



class Operation
{
    public function find (string $id) : Record|false
    {
        // (Getting the value)
        $record = Entity::fetch()->where( 'id', $id )->find();

        if ( !$record )
        {// (Record not found)
            // Returning the value
            return false;
        }

        if ( time() >= strtotime( $record->get( 'datetime.expiration' ) ) )
        {// (Record expired)
            // Returning the value
            return false;
        }



        // Returning the value
        return $record;
    }

    public function update (string $opid, array $values) : void
    {
        if ( Entity::fetch()->where( 'id', $opid )->update( $values ) === false )
        {// (Unable to update the record)
            // Throwing the exception
            throw error( 6303, "Entity 'Operation' :: Unable to update the record" );
        }
    }

    public function insert (string $name, ?string $task = null, ?string $data = null, ?string $display = null, ?string $login = null, ?string $callback_url = null, int $duration = 180) : string
    {
        // (Getting the value)
        $time = time();



        // (Getting the value)
        $record =
        [
            'id'                  => Token::generate( 128 ),
            'name'                => $name,
            'task'                => $task,
            'data'                => $data,
            'display'             => $display,
            'login'               => $login,
            'callback_url'        => $callback_url,
            'datetime.insert'     => DateTime::fetch( $time ),
            'datetime.expiration' => DateTime::fetch( $time + $duration )
        ]
        ;

        if ( !Entity::fetch()->insert( [ $record ] ) )
        {// (Unable to insert the record)
            // Throwing the exception
            throw error( 6302, "Entity 'Operation' :: Unable to insert the record" );
        }



        // Returning the value
        return $record['id'];
    }

    public function notify (string $email, string $opid, ?string $ip = null, ?string $ua = null) : void
    {
        // (Getting the value)
        $client = ClientService::detect( $ip, $ua );

        if ( !$client )
        {// (Request failed)
            // Throwing the exception
            throw error( 6402, 'Unable to detect the client' );
        }



        // (Getting the value)
        $operation = Entity::fetch()->where( 'id', $opid )->find( [ 'name' ] );

        if ( !$operation )
        {// (Record not found)
            // Throwing the exception
            throw error( 6300, "Entity 'Operation' :: Record not found" );
        }



        // (Sending the mail)
        $result = ( new MailService() )->send
        (
            $email,
            'Authorization Required',
            'components/mail/authorization.blade.php',
            [
                'operation'    => $operation->name,
                'client'       => $client,
                'endpoint_url' => 'https://' . env( 'OP_HOST' ) . "/op/$opid"
            ]
        )
        ;

        if ( !$result )
        {// (Unable to send the mail)
            // Throwing the exception
            throw error( 6000, 'Unable to send the mail' );
        }
    }
}



?>