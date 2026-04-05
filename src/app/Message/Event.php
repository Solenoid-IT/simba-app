<?php



namespace App\Message;



readonly class Event
{
    public function __construct (public string $type, public array $data) {}
}



?>