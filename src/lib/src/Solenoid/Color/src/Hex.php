<?php



namespace App\Libraries\Solenoid\Color;



class Hex
{
    public string $r;
    public string $g;
    public string $b;

    public string $a;



    public function __construct (string $r, string $g, string $b, string $a = 'ff')
    {
        // (Getting the values)
        $this->r = $r;
        $this->g = $g;
        $this->b = $b;

        $this->a = $a;
    }

    public static function create (string $r, string $g, string $b, string $a = 'ff') : self
    {
        // Returning the value
        return new self( $r, $g, $b, $a );
    }



    public static function parse (string $hex) : self
    {
        // Returning the value
        return self::create( substr( $hex, 1, 2 ), substr( $hex, 3, 2 ), substr( $hex, 5, 2 ) );
    }



    public function __toString () : string
    {
        // Returning the value
        return '#' . $this->r . $this->g . $this->b;
    }
}



?>