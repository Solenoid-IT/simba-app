<?php



namespace App\Services;



use \Solenoid\X\Error;
use \Solenoid\X\Data\DTO;

use \Solenoid\KeyGen\Token;

use \App\Entity;


use \App\Models\PersonalToken as PersonalTokenModel;

use \App\DTOs\PersonalToken\InsertDTO;



class PersonalToken extends Entity
{
    public function __construct ()
    {
        // (Calling the function)
        parent::__construct( PersonalTokenModel::class );
    }



    public function insert (DTO $input, ?array &$meta = null) : int
    {
        if ( !( $input instanceof InsertDTO ) ) throw error( 6000, 'Invalid input' );



        // (Getting the value)
        $token = Token::generate( 128 );



        // (Setting the value)
        $input->set( 'token', hash( 'sha256', $token ) );



        // (Inserting the element)
        $id = parent::insert( $input );



        // (Getting the value)
        $meta =
        [
            'token' => $token
        ]
        ;



        // Returning the value
        return $id;
    }



    public function list_index (array $fields = []) : array
    {
        // (Setting the value)
        $elements = [];

        foreach ( parent::list( $fields ) as $element )
        {// Processing each entry
            // (Getting the value)
            $elements[ $element->id ] = $element;
        }



        // Returning the value
        return $elements;
    }
}



?>