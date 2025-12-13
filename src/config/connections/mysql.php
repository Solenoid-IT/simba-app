<?php



// Returning the value
return
[
    'local'    =>
    [
        'host' => env( 'MYSQL_HOST' ),
        'port' => null,
        'user' => env( 'MYSQL_USER' ),
        'pass' => env( 'MYSQL_PASS' )
    ]
]
;



?>