<?php



namespace App\Middlewares;



use \App\Models\PersonalToken as PersonalTokenModel;



class Token
{
    public function handle ($input, $next)
    {
        // (Getting the value)
        $token = request()->get_header( 'Personal-Token' );

        if ( !$token )
        {// Value not found
            // Throwing the exception
            throw error( 6100 );
        }



        // (Getting the value)
        $personal_token = PersonalTokenModel::fetch()->where( 'token', hash( 'sha256', $token ) )->find( [ 'tenant', 'owner' ] );

        if ( !$personal_token )
        {// (Record not found)
            // Throwing the exception
            throw error( 6100 );
        }



        // (Getting the value)
        $user = user( $personal_token->owner );

        if ( !$user )
        {// (Record not found)
            // Throwing the exception
            throw error( 6100 );
        }



        // (Setting the value)
        store()->set( 'user', $user );



        // Returning the value
        return $next( $input );
    }
}



?>