<?php



namespace App\Tasks\OnDemand;



class Deploy
{
    const APP_FQDN = 'domain.tld';
    const VH_PATH  = '/var/www/siteman/' . self::APP_FQDN;



    public function run ()
    {
        // Printing the value
        echo "Deploying the app...\n";



        // (Getting the value)
        $basedir = app()->basedir;



        // (Define your logic here ...)
    }
}



?>