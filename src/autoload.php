<?php



// (Including the file)
include_once( __DIR__ . '/lib/composer/autoload.php' );



// (Registering the function)
spl_autoload_register
(
    function ($name)
    {
        // (Getting the value)
        $parts = explode( "\\",  $name );

        if ( $parts[0] !== 'App' ) return;



        // (Getting the value)
        $file_path = __DIR__ . '/app/' . implode( '/', array_slice( $parts, 1 ) ) . '.php';



        // (Including the file)
        include_once( $file_path );
    }
)
;



?>