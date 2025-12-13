<?php



namespace App\Tasks\OnDemand;



use \Solenoid\KeyGen\UUIDv4;

use \Solenoid\MySQL\DateTime;

use \App\Models\Tenant as TenantModel;
use \App\Models\User as UserModel;
use \App\Models\Activity as ActivityModel;

use \App\Services\Client as ClientService;
use \App\Services\Trigger as TriggerService;



class User
{
    public function insert (string $email, string $password, string $user = 'admin', string $tenant = 'simba', int $hierarchy = 1)
    {
        if ( UserModel::fetch()->where( 'email', $email )->exists() )
        {// (Record found)
            // (Setting the value)
            $message = "\nEmail '$email' is not available\n";

            // (Printing the value)
            echo "$message\n";

            // Returning the value
            return;
        }



        // (Getting the value)
        $tenant_record = TenantModel::fetch()->where( 'name', $tenant )->find();

        if ( $tenant_record )
        {// (Record found)
            if ( UserModel::fetch()->where( [ [ 'tenant', $tenant_record->id ], [ 'name', $user ] ] )->exists() )
            {// (Record found)
                // (Setting the value)
                $message = "\nUser '$user' is not available into tenant '$tenant_record->name'\n";

                // (Printing the value)
                echo "$message\n";

                // Returning the value
                return;
            }



            // (Getting the value)
            $tenant_id = $tenant_record->id;
        }
        else
        {// (Record not found)
            // (Getting the value)
            $record =
            [
                'name'            => $tenant,
                'datetime.insert' => DateTime::fetch()
            ]
            ;

            if ( !TenantModel::fetch()->insert( [ $record ] ) )
            {// (Unable to insert the record)
                // (Setting the value)
                $message = "\nUnable to create tenant '$tenant'\n";

                // (Printing the value)
                echo "$message\n";

                // Returning the value
                return;
            }



            // (Getting the value)
            $tenant_id = TenantModel::fetch()->last_id();
        }



        // (Getting the value)
        $time = time();



        // (Getting the value)
        $record =
        [
            'tenant'                  => $tenant_id,
            'owner'                   => null,
            'hierarchy'               => $hierarchy,
            'name'                    => $user,
            'email'                   => $email,
            'security.password'       => password_hash( $password, PASSWORD_BCRYPT ),
            'uuid'                    => UUIDv4::generate(),
            'datetime.insert'         => DateTime::fetch( $time ),
            'datetime.email_verified' => DateTime::fetch( $time )
        ]
        ;

        if ( !UserModel::fetch()->insert( [ $record ] ) )
        {// (Unable to insert the record)
            // (Setting the value)
            $message = "\nUnable to create user '$user'\n";

            // (Printing the value)
            echo "$message\n";

            // Returning the value
            return;
        }



        // (Getting the value)
        $record_id = UserModel::fetch()->last_id();



        // (Getting the values)
        $ip = file_get_contents( 'https://api.ipify.org' );
        $ua = 'Simba Shell/1.0.0';



        // (Getting the value)
        $client = ClientService::detect( $ip, $ua );

        if ( !$client )
        {// (Unable to detect the client)
            // Throwing the exception
            throw error( 6402, 'Unable to detect the client' );
        }



        // (Getting the value)
        $record =
        [
            'tenant'               => $tenant_id,
            'user'                 => $record_id,
            'action'               => 'User.insert',
            'description'          => "User <b>{$user}</b> has been created via <b>shell</b>",
            'session'              => null,
            'ip'                   => $ip,
            'user_agent'           => $ua,
            'ip_info.country.code' => $client['ip']['country']['code'],
            'ip_info.country.name' => $client['ip']['country']['name'],
            'ip_info.isp'          => $client['ip']['isp'],
            'ua_info.browser'      => $client['ua']['browser'],
            'ua_info.os'           => $client['ua']['os'],
            'ua_info.hw'           => $client['ua']['hw'],
            'resource.type'        => 'user',
            'resource.action'      => 'insert',
            'resource.id'          => $record_id,
            'resource.key'         => $record['name'],
            'datetime.insert'      => DateTime::fetch()
        ]
        ;

        if ( !ActivityModel::fetch()->insert( [ $record ] ) )
        {// (Unable to insert the record)
            // Throwing the exception
            throw error( 6302, "Resource 'Activity' :: Unable to insert the record" );
        }



        // (Running triggers)
        ( new TriggerService() )->run_async
        (
            $tenant_id,

            [
                'EVENT_TYPE'        => 'User.insert_via_shell',
                'EVENT_DESCRIPTION' => $record['description'],
                'EVENT_SOURCE'      => class_basename( __CLASS__ ),

                'TENANT_ID'         => $tenant_id,
                'USER_ID'           => $record_id,
                'RESOURCE_ID'       => null,
                'ELEMENT_ID'        => $record_id,
            ]
        )
        ;



        // (Printing the value)
        echo "\nUser has been created :\n\nLogin at https://localhost:8091/login with the user '{$user}@{$tenant}'\n\n";
    }

    public function set_email (string $id, string $email)
    {
        // (Getting the value)
        $record = UserModel::fetch()->where( 'id', $id )->find();

        if ( !$record )
        {// (Record not found)
            // (Setting the value)
            $message = "\nUser with id '$id' not found\n";

            // (Printing the value)
            echo "$message\n";

            // Returning the value
            return;
        }



        // (Getting the value)
        $values =
        [
            'email' => $email
        ]
        ;

        if ( !UserModel::fetch()->where( 'id', $id )->update( $values ) )
        {// (Unable to update the record)
            // (Setting the value)
            $message = "\nUnable to update user with id '$id'\n";

            // (Printing the value)
            echo "$message\n";

            // Returning the value
            return;
        }



        // Printing the value
        echo "\nUser email has been updated\n\n";
    }
}



?>