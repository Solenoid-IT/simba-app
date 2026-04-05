<?php



namespace App\Message;



readonly class Message
{
    public function __construct (public Target $target, public Event $event) {}
}



?>