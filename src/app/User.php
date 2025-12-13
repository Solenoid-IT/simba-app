<?php



namespace App;



use \Solenoid\X\HTTP\Session;

use \App\Scopes\Scope;



class User
{
    public ?Session $session = null;



    public function __construct
    (
        public int $id,
        public int $tenant,
        public int $hierarchy,
        public string $name,
        public string $email
    )
    {}



    public function restrict (Scope $scope) : array
    {
        if ( $scope->hierarchy !== null )
        {// Value found
            if ( $this->hierarchy > $scope->hierarchy )
            {// Match failed
                // Throwing the exception
                throw error( 6500, "User does not have the required hierarchy" );

                // Returning the value
                return [ 'tenant', 0 ];
            }
        }



        // (Setting the value)
        $user_fields =
        [
            'tenant'       => 'tenant',
            'owner'        => 'id',
            'user'         => 'id',
            'id'           => 'id'
        ]
        ;



        // (Setting the value)
        $filter = [];

        foreach ( $scope->fields as $k => $field )
        {// Processing each entry
            // (Getting the value)
            $user_field = $user_fields[ is_string($k) ? $k : $field ];

            if ( !$user_field )
            {// Value not found
                // Throwing the exception
                throw error( 6000, "Invalid field '{$field}' specified in the scope" );

                // Breaking the iteration
                break;
            }



            // (Appending the value)
            $filter[] = [ $field, $this->$user_field ];
        }



        // Returning the value
        return $filter;
    }



    public function set_session (Session $session) : self
    {
        // (Getting the value)
        $this->session = $session;



        // Returning the value
        return $this;
    }
}



?>