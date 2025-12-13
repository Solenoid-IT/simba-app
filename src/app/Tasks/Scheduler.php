<?php



namespace App\Tasks;



use \Solenoid\X\CLI\Task;



class Scheduler
{
    public function run ()
    {
        // (Getting the value)
        $time = time();



        foreach ( Task::scan( app()->basedir . '/app/Tasks/Scheduled', 'App\\Tasks\\Scheduled' ) as $task )
        {// Processing each entry
            foreach ( $task->list_schedules() as $schedule )
            {// Processing each entry
                if ( !$schedule->check( $time ) ) continue;



                // (Getting the value)
                $pid = task_pid( $task );

                if ( $pid )
                {// (Task is locked)
                    // Printing the value
                    echo "Task '$task' is locked into the process with PID {$pid} -> Skipping the execution\n";



                    // Continuing the iteration
                    continue;
                }



                // (Starting the process)
                $process = run_async( $task );

                if ( !$process )
                {// (Unable to start the process)
                    // Printing the value
                    echo "Unable to start the process for task '$task'\n";



                    // Continuing the iteration
                    continue;
                }



                // Printing the value
                echo date( 'c', $time ) . " :: Started task '$task' with PID " . $process->pid . "\n";
            }
        }
    }
}



?>