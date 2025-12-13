<?php



namespace App\Services;



class Svelte
{
    public function render ()
    {
        if ( route()->path !== '/login' && !( strpos( route()->path, '/extensions/' ) === 0 ) )
        {// Match OK
            // (Sending the cookie)
            cookie( 'fwd_route' )->set( request()->path ?? '/' )->send();
        }



        // (Getting the value)
        $content = view()->raw( '../web/build/index.blade.php' );



        // (Getting the value)
        $content = preg_replace( '/\.?\/_svelte/', '/build/_svelte', $content );



        // Printing the value
        echo $content;
    }
}



?>