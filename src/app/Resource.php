<?php



namespace App;



use \Attribute;



#[ Attribute( Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE ) ]
class Resource
{
    public function __construct (public int $id) {}



    public static function find (string $model) : self|null
    {
        foreach ( ( new \ReflectionClass( $model ) )->getAttributes( self::class ) as $attribute )
        {// Processing each entry
            // Returning the value
            return $attribute->newInstance();
        }



        // Returning the value
        return null;
    }
}



?>