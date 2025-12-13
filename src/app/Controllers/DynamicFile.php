<?php



namespace App\Controllers;



class DynamicFile
{
    public function handle_request ()
    {
        switch ( route()->path )
        {
            case '/robots.txt':
                // (Sending the header)
                response()->add_header( 'Content-Type: text/plain' )->send();

                // Printing the value
                echo view()->raw( 'dynamic_files/robots.txt.blade.php', [ 'base_url' => url()->fetch_base() ] );
            break;

            case '/sitemap.xml':
                // (Sending the header)
                response()->add_header( 'Content-Type: application/xml' )->send();

                // Printing the value
                echo view()->raw( 'dynamic_files/sitemap.xml.blade.php' );
            break;
        }
    }
}



?>