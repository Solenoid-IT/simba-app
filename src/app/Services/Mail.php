<?php



namespace App\Services;



use \Solenoid\SMTP\Connection;
use \Solenoid\SMTP\Mail as SmtpMail;
use \Solenoid\SMTP\MailBox;
use \Solenoid\SMTP\MailBody;
use \Solenoid\SMTP\Retry;



class Mail
{
    private Connection $connection;



    public function __construct ()
    {
        // (Getting the value)
        $this->connection = new Connection( env('SMTP_HOST'), env('SMTP_PORT'), env('SMTP_USER'), env('SMTP_PASS'), env('SMTP_ALGO') );
    }



    public function send (string $target, string $subject, string $template_file_path, array $template_data = []) : bool
    {
        // (Creating a Mail)
        $mail = new SmtpMail
        (
            new MailBox( env('SMTP_USER'), env('APP_NAME') ),

            [
                new MailBox( $target )
            ],

            [],
            [],
            [],

            env('APP_NAME') . ' - ' . $subject,
            new MailBody( '', view()->raw( $template_file_path, $template_data ) )
        )
        ;



        if ( !$this->connection->send( $mail, new Retry() ) )
        {// (Unable to send the mail)
            // Returning the value
            return false;
        }



        // Returning the value
        return true;
    }
}



?>