<?php



namespace App;



use \Solenoid\X\HTTP\Session as HttpSession;

use \Solenoid\KeyGen\Generator;
use \Solenoid\KeyGen\Token;

use \Solenoid\MySQL\DateTime;

use \App\Models\Session as Resource;



class Session extends HttpSession
{
    private static self $instance;



    private function set_handlers () : void
    {
        // (Setting the handler)
        $this->set_handler('validate_id', function ($session, $id) {
            // Returning the value
            return preg_match( '/^[\w]+$/', $id ) === 1;
        });

        // (Setting the handler)
        $this->set_handler('generate_id', function ($session) {
            // Returning the value
            return
                Generator::start
                (
                    function ($id)
                    {
                        // Returning the value
                        return !Resource::fetch()->where( 'id', $id )->exists();
                    },
                    function ()
                    {
                        // Returning the value
                        return Token::generate( 128 );
                    }
                )
            ;
        });

        // (Setting the handler)
        $this->set_handler('find', function ($session, $id) {
            // (Getting the value)
            $resource = Resource::fetch()->where( 'id', $id )->find();

            if ( !$resource )
            {// (Record not found)
                // Returning the value
                return false;
            }
            else
            {// (Record found)
                // (Getting the value)
                $object =
                [
                    'creation_timestamp'    => strtotime( $resource->datetime->insert ),
                    'last_update_timestamp' => $resource->datetime->update ? strtotime( $resource->datetime->update ) : null,
                    'expiration_timestamp'  => $resource->datetime->expiration ? strtotime( $resource->datetime->expiration ) : null,
                    'data'                  => json_decode( $resource->data, true )
                ]
                ;
            }
            



            // Returning the value
            return $object;
        });

        // (Setting the handler)
        $this->set_handler('change_id', function ($session, $new_id) {
            // Returning the value
            return Resource::fetch()->where( 'id', $session->id )->update( [ 'id' => $new_id ] );
        });

        // (Setting the handler)
        $this->set_handler('set_duration', function ($session, $duration) {
            // (Getting the value)
            $expiration_timestamp = $duration ? time() + $duration : null;

            if ( !Resource::fetch()->where( 'id', $session->id )->update( [ 'datetime.expiration' => DateTime::fetch( $expiration_timestamp ) ] ) )
            {// (Unable to update the resource)
                // Returning the value
                return false;
            }



            // Returning the value
            return $expiration_timestamp;
        });

        // (Setting the handler)
        $this->set_handler('delete', function ($session) {
            // Returning the value
            return Resource::fetch()->where( 'id', $session->id )->delete();
        });

        // (Setting the handler)
        $this->set_handler('update', function ($session) {
            if ( Resource::fetch()->where( 'id', $session->id )->exists() )
            {// (Resource found)
                // (Getting the value)
                $values =
                [
                    'user'            => $session->data['user'],
                    'data'            => json_encode( $session->data ),

                    'datetime.update' => DateTime::fetch( $session->last_update_timestamp )
                ]
                ;

                if ( !Resource::fetch()->where( 'id', $session->id )->update( $values ) )
                {// (Unable to update the resource)
                    // Returning the value
                    return false;
                }
            }
            else
            {// (Resource not found)
                // (Getting the value)
                $user = user( $session->data['user'] );



                // (Getting the value)
                $record =
                [
                    'id'                   => $session->id,

                    'tenant'               => $user->tenant,
                    'user'                 => $user->id,

                    'data'                 => json_encode( $session->data ),

                    'datetime.insert'      => DateTime::fetch( $session->creation_timestamp ),
                    'datetime.expiration'  => DateTime::fetch( $session->expiration_timestamp )
                ]
                ;

                if ( !Resource::fetch()->insert( [ $record ] ) )
                {// (Unable to insert the resource)
                    // Returning the value
                    return false;
                }
            }



            // Returning the value
            return true;
        });
    }



    private function __construct ()
    {
        // (Calling the function)
        parent::__construct( cookie( env( 'SESSION_COOKIE' ) ), (int) env( 'SESSION_DURATION' ), true );



        // (Setting the handlers)
        $this->set_handlers();



        // (Listening for the event)
        $this->on('update', function ($session) { /* custom callback code ... */ });
    }



    public static function fetch ()
    {
        if ( !isset( self::$instance ) )
        {// Value not found
            // (Getting the value)
            self::$instance = new self();
        }



        // Returning the value
        return self::$instance;
    }
}



?>