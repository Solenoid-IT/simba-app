<?php



namespace App\Tasks\API;



use \Solenoid\KeyGen\UUIDv4;

use \App\Client;

use \App\DTOs\User\InsertDTO;
use \App\DTOs\User\BirthDataDTO;

use \App\Services\Operation as OperationService;
use \App\Services\User as UserService;



class User
{
    public function insert ()
    {
        // (Getting the value)
        $input = json_decode( command()->buffer() );

        if ( $input->last_step )
        {// Value is true
            // (Getting the value)
            $dto = new InsertDTO
            (
                $input->record->hierarchy,
                $input->record->name,
                $input->record->email,
                new BirthDataDTO
                (
                    $input->record->birth->name,
                    $input->record->birth->surname
                )
            )
            ;

            // (Inserting the record)
            $id = ( new UserService() )->user( user( $input->user ) )->client( new Client( $input->ip, $input->ua ) )->input( [ 'uuid' => UUIDv4::generate() ] )->insert( $dto );



            // Returning the value
            return $id;
        }
        else
        {// Value is false
            // (Getting the value)
            $operation_service = new OperationService();



            // (Getting the value)
            $data =
            [
                'user'      => $input->user,
                'email'     => $input->email,

                'record'    => $input->record,

                'ip'        => $input->ip,
                'ua'        => $input->ua,

                'last_step' => true
            ]
            ;



            // (Getting the value)
            $opid = $operation_service->insert
            (
                name:         'User creation',
                task:         'API/User.insert',
                data:         json_encode( $data ),
                display:      null,
                login:        'invite',
                callback_url: '/',
                duration:     (int) env( 'BASE_OP_DURATION' ) );



            // (Notifying the operation)
            $operation_service->notify( $input->email, $opid, $data['ip'], $data['ua'] );
        }
    }

    public function set_email ()
    {
        // (Getting the value)
        $input = json_decode( command()->buffer() );

        if ( $input->last_step )
        {// Value is true
            // (Setting the field)
            ( new UserService() )->user( user( $input->user ) )->client( new Client( $input->ip, $input->ua ) )->element( $input->user )->set_field( 'email', $input->email );
        }
        else
        {// Value is false
            // (Getting the value)
            $operation_service = new OperationService();



            // (Getting the value)
            $data =
            [
                'user'      => $input->user,
                'email'     => $input->email,

                'ip'        => $input->ip,
                'ua'        => $input->ua,

                'last_step' => true
            ]
            ;

            // (Getting the value)
            $opid = $operation_service->insert
            (
                name:         'Email update',
                task:         'API/User.set_email',
                data:         json_encode( $data ),
                display:      "Email has been updated to <b>{$input->email}</b>",
                login:        null,
                callback_url: null,
                duration:     (int) env( 'BASE_OP_DURATION' ) );



            // (Notifying the operation)
            $operation_service->notify( $input->email, $opid, $data['ip'], $data['ua'] );
        }
    }
}



?>