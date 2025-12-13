<?php



namespace App\Services;



use \Solenoid\X\Error;
use \Solenoid\X\HTTP\Session;

use \App\Models\User as UserModel;
use \App\Models\Group as GroupModel;
use \App\Models\SessionLoginView as SessionLoginViewModel;
use \App\Models\Activity as ActivityModel;



class Dashboard
{
    public function calc_report (object $user, Session $session) : array|Error
    {
        // (Setting the value)
        $current_year_logins_raw = [];
        
        foreach( ActivityModel::fetch()->where( [ [ 'user', $user->id ], [ "`action` LIKE 'User.login%'" ], [ "YEAR( `datetime.insert` ) = YEAR( CURRENT_TIMESTAMP )" ] ] )->order( [ 'id' => SORT_ASC ] )->list( [ 'id', 'datetime.insert' ] ) as $record )
        {// Processing each entry
            // (Getting the value)
            $time = strtotime( $record->datetime->insert );



            // (Getting the value)
            $record =
            [
                'id'    => $record->id,

                'year'  => date( 'Y', $time ),
                'month' => date( 'm', $time )
            ]
            ;



            // (Appending the value)
            $current_year_logins_raw[] = $record;
        }



        // (Setting the value)
        $current_year_logins = [];

        for ( $month = 1; $month <= 12; $month++ )
        {// Iterating each entry
            // (Setting the value)
            $current_year_logins[ $month ] = 0;
        }



        foreach ( $current_year_logins_raw as $login )
        {// Processing each entry
            // (Incrementing the value)
            $current_year_logins[ (int) $login['month'] ] += 1;
        }



        // (Setting the value)
        $current_month_logins_raw = [];

        foreach ( ActivityModel::fetch()->where( [ [ 'user', $user->id ], [ "`action` LIKE 'User.login%'" ], [ "YEAR( `datetime.insert` ) = YEAR( CURRENT_TIMESTAMP ) AND MONTH( `datetime.insert` ) = MONTH( CURRENT_TIMESTAMP )" ] ] )->order( [ 'id' => SORT_ASC ] )->list( [ 'id', 'datetime.insert' ] ) as $record )
        {// Processing each entry
            // (Getting the value)
            $time = strtotime( $record->datetime->insert );



            // (Getting the value)
            $record =
            [
                'id'    => $record->id,

                'year'  => date( 'Y', $time ),
                'month' => date( 'm', $time ),
                'day'   => date( 'd', $time )
            ]
            ;



            // (Appending the value)
            $current_month_logins_raw[] = $record;
        }



        // (Setting the value)
        $current_month_logins = [];

        for ( $day = 1; $day <= (int) date( 'd', strtotime( 'last day of ' . $current_month_logins_raw[0]['year'] . '-' . $current_month_logins_raw[0]['month'] ) ); $day++ )
        {// Iterating each entry
            // (Setting the value)
            $current_month_logins[ $day ] = 0;
        }



        foreach ( $current_month_logins_raw as $login )
        {// Processing each entry
            // (Incrementing the value)
            $current_month_logins[ (int) $login['day'] ] += 1;
        }



        // (Setting the value)
        $login_browsers_raw = [];

        foreach ( ActivityModel::fetch()->where( [ [ 'user', $user->id ], [ "`action` LIKE 'User.login%'" ] ] )->list( [ 'ua_info.browser' ] ) as $record )
        {// Processing each entry
            // (Appending the value)
            $login_browsers_raw[] = $record->get( 'ua_info.browser' );
        }



        // (Setting the value)
        $login_browsers = [];

        foreach ( $login_browsers_raw as $browser )
        {// Processing each entry
            // (Incrementing the value)
            $login_browsers[ preg_replace( '/ v[0-9\.]+$/', '', $browser ) ] += 1;
        }



        // (Sorting the array)
        arsort( $login_browsers );



        // (Getting the value)
        $data =
        [
            'num_users'            => UserModel::fetch()->where( 'tenant', $user->tenant )->count(),
            'num_groups'           => GroupModel::fetch()->where( 'tenant', $user->tenant )->count(),
            'num_active_sessions'  => SessionLoginViewModel::fetch()->where( 'session.user', $user->id )->count(),
            'login_uptime'         => time() - strtotime( SessionLoginViewModel::fetch()->where( 'session.id', $session->id )->find( [ 'datetime.insert' ] )->datetime->insert ),
            'current_year_logins'  => $current_year_logins,
            'current_month_logins' => $current_month_logins,
            'login_browsers'       => $login_browsers
        ]
        ;



        // Returning the value
        return $data;
    }
}



?>