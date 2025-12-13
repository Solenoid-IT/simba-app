<?php



namespace App\Scopes;



use \App\User;



abstract class Scope
{
    public function __construct (public array $fields, public ?int $hierarchy = null) {}



    public static function find (string $model, User $user) : static|null
    {
        // (Setting the value)
        $scope = null;

        foreach ( ( new \ReflectionClass( $model ) )->getAttributes( static::class ) as $attribute )
        {// Processing each entry
            $scope = $attribute->newInstance();

            if ( $scope->hierarchy && $scope->hierarchy >= $user->hierarchy )
            {// Match OK
                // Returning the value
                return $scope;
            }
            else
            {// Match failed
                // Continuing the iteration
                continue;
            }



            // Breaking the iteration
            break;
        }



        // Returning the value
        return $scope;
    }
}



?>