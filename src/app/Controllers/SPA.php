<?php



namespace App\Controllers;



use \App\Services\Svelte as SvelteService;



class SPA
{
    public function handle_request (SvelteService $svelte)
    {
        // (Rendering the content)
        $svelte->render();
    }
}



?>