<?php



use \Solenoid\X\CLI\Command;
use \Solenoid\X\CLI\Task;



function command () : Command
{
    // Returning the value
    return container()->make( 'command' );
}



function task_pid (Task $task) : int|null
{
    // (Getting the value)
    $pid = redis_get( 'task_lock:' . md5( $task ) );



    // Returning the value
    return $pid ? (int) $pid : null;
}



function mutex_lock (Task $task, int $pid) : void
{
    if ( !redis_set( 'task_lock:' . md5( $task ), $pid ) )
    {// (Unable to set the key)
        // Throwing the exception
        throw new \Exception( "Unable to set the lock for task '$task'" );
    }
}

function mutex_unlock (Task $task) : void
{
    if ( !redis_del( 'task_lock:' . md5( $task ) ) )
    {// (Unable to delete the key)
        // Throwing the exception
        throw new \Exception( "Unable to delete the lock for task '$task'");
    }
}



?>