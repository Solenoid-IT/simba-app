<?php



namespace App\Controllers;



use \App\Services\Svelte as SvelteService;



class Fallback
{
    public function __construct ()
    {
        // (Setting the response)
        response()->set_code( 404 );
    }



    public function handle_request (SvelteService $svelte)
    {
        // (Rendering the content)
        $svelte->render();
    }
}



?>