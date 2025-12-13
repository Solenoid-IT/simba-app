<?php



// Returning the value
return
[
    [
        'name'      => 'device',
        'domain'    => '',
        'path'      => '/',
        'secure'    => true,
        'http_only' => true,
        'same_site' => 'Strict'
    ],

    [
        'name'      => 'fwd_route',
        'domain'    => '',
        'path'      => '/',
        'secure'    => true,
        'http_only' => true,
        'same_site' => 'Lax'
    ],

    [
        'name'      => 'session',
        'domain'    => '',
        'path'      => '/',
        'secure'    => true,
        'http_only' => true,
        'same_site' => 'None'
    ]
]
;



?>