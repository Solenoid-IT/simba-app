<?php



namespace App\Tasks\OnDemand;



use \Solenoid\X\Data\EndpointReader;



class ApiDoc
{
    public function run ()
    {
        // (Getting the value)
        $stats = json_decode( storage()->read( '/apidoc_stats.json' ) ?? [], true );



        // (Setting the value)
        $endpoints =
        [
            'api/token' => '/app/Endpoints/Token'
        ]
        ;



        // (Setting the value)
        $data = [];

        foreach ( $endpoints as $http_path => $fs_path )
        {// Processing each entry
            // (Setting the value)
            $data['endpoints'][ $http_path ] = [];



            // (Getting the value)
            $prefix = str_replace( '/', '\\', ucfirst( substr( $fs_path, 1 ) ) );



            // (Getting the value)
            $data['endpoints'][ $http_path ]['tree'] = ( new EndpointReader( app()->basedir . $fs_path, $prefix ) )->read();



            // (Getting the values)
            $old_content = $stats[ $http_path ]['content'];
            $new_content = md5( json_encode( $data['endpoints'][ $http_path ]['tree'] ) );

            if ( $new_content !== $old_content )
            {// Match failed
                // (Getting the value)
                $data['endpoints'][ $http_path ]['last_change_timestamp'] = $stats[ $http_path ]['timestamp'] ?? null;
            }



            // (Getting the value)
            $stats[ $http_path ] =
            [
                'content'   => $new_content,
                'timestamp' => date( 'c' )
            ]
            ;
        }



        # (Writing to the file)
        storage()->write('/apidoc.json', json_encode( $data, JSON_PRETTY_PRINT ) );



        // (Writing to the file)
        storage()->write( '/apidoc_stats.json', json_encode( $stats, JSON_PRETTY_PRINT ) );



        // Printing the value
        echo view()->html( 'apidoc/view.blade.php', $data );
    }
}