<?php



namespace App\Tasks\OnDemand;



use phpseclib3\Net\SFTP;



class Release
{
    public function run ()
    {
        // (Executing the cmd)
        system( 'php x frontend:build' );



        // (Getting the values)
        $src_folder_path = app()->basedir;
        $dst_file_path   = "$src_folder_path/app.simba";



        if ( file_exists( $dst_file_path ) )
        {// (File found)
            if ( !unlink( $dst_file_path ) )
            {// (Unable to remove the file)
                // Printing the value
                echo "\nFile System :: Unable to remove the file '$dst_file_path'\n";

                // Returning the value
                return false;
            }
        }



        // (Executing the cmd)
        system( "zip -r \"$dst_file_path\" \"$src_folder_path\"" );



        // (Getting the value)
        $connection = new SFTP( env( 'SSH_HOST' ), env( 'SSH_PORT' ) );

        if ( !$connection->login( env( 'SSH_USER' ), env( 'SSH_PASS' ) ) )
        {// (Unable to login)
            // Printing the value
            echo "\nSFTP Client :: Unable to login to 'sftp://" . env( 'SSH_HOST' ) . ':' . env( 'SSH_PORT' ) . "' with user '" . env( 'SSH_USER' ) . "'\n";

            // Returning the value
            return false;
        }



        // (Getting the value)
        $remote_file_path = '/var/www/uploads/app.simba';

        if ( !$connection->put( $remote_file_path, $dst_file_path, SFTP::SOURCE_LOCAL_FILE ) )
        {// (Unable to upload the file)
            // Printing the value
            echo "\nSFTP Client :: Unable to upload the file '$dst_file_path' to '$remote_file_path'\n";

            // Returning the value
            return false;
        }



        if ( !unlink( $dst_file_path ) )
        {// (Unable to remove the file)
            // Printing the value
            echo "\nFile System :: Unable to remove the file '$dst_file_path'\n";

            // Returning the value
            return false;
        }



        // (Disconnecting the client)
        $connection->disconnect();



        // Returning the value
        return true;
    }
}



?>