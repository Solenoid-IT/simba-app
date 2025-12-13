<?php



namespace Solenoid\Color;



use \App\Libraries\Solenoid\Color\Hex;



class HSL
{
    public int $h;
    public int $s;
    public int $l;

    public int $a;



    public function __construct (int $h, int $s = 100, int $l = 50, int $a = 1)
    {
        // (Getting the values)
        $this->h = $h;
        $this->s = $s;
        $this->l = $l;

        $this->a = $a;
    }

    public static function create (int $h, int $s = 100, int $l = 50, int $a = 1) : HSL
    {
        // Returning the value
        return new HSL( $h, $s, $l, $a );
    }



    public function to_hex () : Hex
    {
        // (Getting the values)
        $l = $this->l / 100;
        $a = $this->s * min( [ $l, 1 - $l ] ) / 100;
        $f = function ($n) use ($l, $a)
        {
            // (Getting the values)
            $k = ( $n + $this->h / 30 ) % 12;
            $c = $l - $a * max( [ min( [ $k - 3, 9 - $k, 1 ] ), -1 ] );



            // Returning the value
            return str_pad( dechex( round( 255 * $c ) ), 2, '0', STR_PAD_LEFT );
        }
        ;



        // Returning the value
        return Hex::create( $f( 0 ), $f( 8 ), $f( 4 ) );
    }



    public function __toString () : string
    {
        // Returning the value
        return 'hsla(' . $this->h . ', ' . $this->s . '%, ' . $this->l . '%, ' . $this->a . ')';
    }
}



?>