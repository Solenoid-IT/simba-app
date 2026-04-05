<?php



namespace App\Message;



readonly class Target
{
    public function __construct
    (
        public int $tenant,

        /**
         * @var int[] $users
         */
        public array $users = [],

        /**
         * @var int[] $hierarchies
         */
        public array $hierarchies = []
    )
    {}
}



?>