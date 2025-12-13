<?php



namespace App\Controllers;



class Error
{
    public function handle_request ()
    {
        // (Getting the value)
        $records = array_values( array_map( function ($error) { unset( $error['exposed'] ); unset( $error['notifiable'] ); return $error; }, array_filter( app()->get_errors(), fn ($error) => $error['exposed'] === '1' ) ) );



        // Printing the value
        echo view()->raw( 'errors/view.blade.php', [ 'records' => $records ] );
    }
}



?>