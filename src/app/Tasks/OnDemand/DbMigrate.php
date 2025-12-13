<?php



namespace App\Tasks\OnDemand;



class DbMigrate
{
    private static function replace_vars (string $content, array $vars = []) : string
    {
        foreach ( $vars as $k => $v )
        {// Processing each entry
            // (Getting the value)
            $content = str_replace( '{' . $k . '}', $v, $content );
        }



        // Returning the value
        return $content;
    }



    public function run (string $project_name = 'local', string $mode = 'up', ?string $id = null)
    {
        // (Getting the value)
        $folder_path = app()->basedir . "/db_connections/$project_name";

        if ( !is_dir( $folder_path ) )
        {// (Directory not found)
            // Printing the value
            echo "\nDirectory '$folder_path' not found\n\n";

            // Closing the process
            exit;
        }



        // (Getting the value)
        $config = json_decode( file_get_contents( "$folder_path/config.json" ), true );



        // (Getting the value)
        $conn = mysql_connection( $config['connection_id'] );



        // (Getting the value)
        $vars =
        [
            'db_name' => $config['db_name'],
            'db_user' => $conn->username,
            'db_pass' => $conn->password
        ]
        ;



        // (Getting the value)
        $client_file_path = "$folder_path/client.json";



        // (Getting the value)
        $client = file_exists( $client_file_path ) ? json_decode( file_get_contents( $client_file_path ) ) : false;

        if ( $client )
        {// Value found
            // (Getting the value)
            $client_s = implode
            (
                " ",
                [
                    "--host=\"{$client->host}\"",
                    "--port=\"{$client->port}\"",
                    "-u \"{$client->user}\"",
                    "-p{$client->pass}",
                ]
            )
            ;
        }



        // (Getting the value)
        $error_folder = "$folder_path/error";

        if ( is_dir( $error_folder ) )
        {// (Directory found)
            // (Executing the cmd)
            system( "rm -rf \"$error_folder\"" );
        }



        // (Making the directory)
        mkdir( $error_folder );



        // (Getting the value)
        $head_file_path = "$folder_path/head";



        // (Getting the value)
        $head = file_get_contents( $head_file_path );



        if ( $head === '' && $mode === 'down' )
        {// Match OK
            // Printing the value
            echo "\n[ NOTHING TO MIGRATE ]\n\n";

            // Closing the process
            exit;
        }



        // Printing the value
        echo "\n";



        // (Setting the values)
        $num_steps    = null;
        $current_step = 0;



        // (Getting the value)
        $steps = $id !== null && in_array( $id[0], [ '+', '-' ] );

        if ( $steps )
        {// Value is true
            // (Getting the value)
            $num_steps = (int) substr( $id, 1 );
        }



        // (Getting the value)
        $migrations = array_values( array_filter( scandir( "$folder_path/migrations", $mode === 'up' ? 0 : 1 ), function ($migration_id) { return !in_array( $migration_id, [ '.', '..' ] ); } ) );

        if ( $id !== null && !$steps && !in_array( $id, $migrations ) )
        {// Match failed
            // Printing the value
            echo "Migration '$id' not found\n\n";

            // Closing the process
            exit;
        }



        // (Setting the value)
        $head_found = false;



        // (Setting the value)
        $num_migrations_applied = 0;

        foreach ( $migrations as $i => $migration_id )
        {// Processing each entry
            if ( $num_steps )
            {// Value found
                // (Getting the value)
                $head_found = $head === '' || $migration_id === $head;

                if ( $head_found )
                {// Value is true
                    // (Incrementing the value)
                    $current_step += 1;
                }
            }



            if ( $head )
            {// Value found
                if ( $mode === 'up' )
                {// (Mode is UP)
                    if ( $migration_id <= $head )
                    {// Value is less than or equal
                        // Continuing the iteration
                        continue;
                    }
                }
                else
                if ( $mode === 'down' )
                {// (Mode is DOWN)
                    if ( $migration_id > $head )
                    {// Value is greater than or equal
                        // Continuing the iteration
                        continue;
                    }



                    if ( $id !== null && !$steps )
                    {// Value found
                        if ( $migration_id === $id )
                        {// Match OK
                            // (Getting the value)
                            $head = $id;

                            // Breaking the iteration
                            break;
                        }
                    }
                }
            }



            // (Getting the value)
            $sql_file_content = self::replace_vars( file_get_contents( "$folder_path/migrations/$migration_id/$mode.sql" ), $vars );



            // (Getting the value)
            $sql_file_path = "$error_folder/$mode.sql";

            if ( file_put_contents( $sql_file_path, $sql_file_content ) === false )
            {// (Unable to write to the file)
                // Printing the value
                echo "\n\nProject '$project_name' :: Migration '$migration_id' :: Unable to write to the file '$sql_file_path' !\n\n\n";

                // Closing the process
                exit;
            }



            // (Executing the command)
            system( 'mysql' . ( $client ? " $client_s" : '' ) . " --verbose < \"$sql_file_path\" > \"$error_folder/output\" 2> \"$error_folder/error\"" );

            if ( file_get_contents( "$error_folder/error" ) )
            {// (There is an error)
                // Printing the value
                echo "Project '$project_name' :: Migration '$migration_id' :: ERROR\n";

                // Breaking the iteration
                break;
            }
            else
            {// (There are no errors)
                // (Getting the value)
                $head = $migration_id;

                // (Printing the value)
                echo "Project '$project_name' :: Migration '{$migration_id}' -> $mode\n";



                // (Incrementing the value)
                $num_migrations_applied += 1;



                if ( $id !== null )
                {// Value found
                    if ( $migration_id === $id )
                    {// Match OK
                        // Breaking the iteration
                        break;
                    }
                }
            }



            if ( $num_steps )
            {// Value found
                if ( $mode === 'down' )
                {// Match OK
                    // (Getting the value)
                    $head = $migrations[ $i + 1 ];
                }



                if ( $current_step === $num_steps ) break;
            }
        }



        if ( $id === null )
        {// Match failed
            if ( $mode === 'down' )
            {// Match OK
                // (Setting the value)
                $head = null;
            }
        }



        if ( file_put_contents( $head_file_path, $head ) === false )
        {// (Unable to write to the file)
            // Printing the value
            echo "\n\nProject '$project_name' :: Unable to write to the file '$head_file_path' !\n\n\n";

            // Closing the process
            exit;
        }



        if ( $num_migrations_applied > 0 )
        {// (There are migrations done)
            // Printing the value
            echo "\n[ \033[36m$num_migrations_applied\033[0m MIGRATIONS APPLIED (\033[36m$mode\033[0m) ]\n\n";
        }
        else
        {// (There are no migrations done)
            // Printing the value
            echo "[ NOTHING TO MIGRATE ]\n\n";
        }
    }
}



?>