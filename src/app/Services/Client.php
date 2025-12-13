<?php



namespace App\Services;



class Client
{
    const CACHE_TTL = 7 * 86400;# '7 days'



    public static $cache = [];



    private static function get_ip_info (string $ip) : array|false
    {
        // (Getting the value)
        $data =
        [
            'address'     => $ip,
            'country'     =>
            [
                'code'    => null,
                'name'    => null
            ],
            'isp'         => null
        ]
        ;



        // (Getting the value)
        $api_key = env( 'IPGEO_API_KEY' );

        if ( !$api_key )
        {// Value not found
            // Returning the value
            return $data;
        }



        // (Sending the request)
        $result = send_request( 'GET', "https://api.ipgeolocation.io/ipgeo?apiKey=$api_key&ip=$ip", [ 'User-Agent: Simba/1.0.0' ], '', 20, 20 );

        if ( $result === false )
        {// (Unable to send the request)
            // Returning the value
            return false;
        }



        // (Getting the value)
        $response_code = $result->response->get_code();

        if ( $response_code === 423 )
        {// (IP is a bogon)
            // Returning the value
            return $data;
        }
        else
        if ( $response_code !== 200 )
        {// (Request failed)
            // Returning the value
            return false;
        }



        # debug
        #storage()->write( '/ip_info-' . time() . '.json', $result->response->get_body() );



        // (Getting the value)
        $body = json_decode( $result->response->get_body(), true );



        // (Getting the values)
        $data['country']['code'] = $body['country_code2'] ?? null;
        $data['country']['name'] = $body['country_name'] ?? null;
        $data['isp']             = $body['isp'] ?? null;



        // Returning the value
        return $data;
    }

    private static function get_ua_info (string $ua) : array|false
    {
        // (Setting the value)
        $data =
        [
            'browser' => null,
            'os'      => null,
            'hw'      => null
        ]
        ;



        // (Getting the value)
        $api_key = env( 'IPGEO_API_KEY' );

        if ( !$api_key )
        {// Value not found
            // Returning the value
            return $data;
        }



        // (Sending the request)
        $result = send_request( 'GET', "https://api.ipgeolocation.io/user-agent?apiKey=$api_key", [ "User-Agent: $ua" ], '', 20, 20 );

        if ( $result === false )
        {// (Unable to send the request)
            // Returning the value
            return false;
        }



        // (Getting the value)
        $response_code = $result->response->get_code();

        if ( $response_code !== 200 )
        {// (Request failed)
            // Returning the value
            return false;
        }



        # debug
        #storage()->write('/ua_info-' . time() . '.json', $result->response->get_body() );



        // (Getting the value)
        $body = json_decode( $result->response->get_body(), true );



        // (Getting the values)
        $data['browser'] = trim( implode( ' ', array_values( array_filter( [ $body['name'], $body['version'] ? ( 'v' . $body['version'] ) : null ], function ($entry) { return $entry !== null && $entry !== ''; } ) ) ) );
        $data['os']      = trim( implode( ' ', array_values( array_filter( [ $body['operatingSystem']['name'], ( $body['operatingSystem']['version'] ? ( 'v' . $body['operatingSystem']['version'] ) : null ), ( $body['operatingSystem']['type'] ? ( '- ' . $body['operatingSystem']['type'] ) : null ) ] ) ) ) );
        $data['hw']      = trim( implode( ' ', array_values( array_filter( [ $body['device']['name'], ( $body['device']['type'] ? ( '- ' . $body['device']['type'] ) : null ) ] ) ) ) );



        // Returning the value
        return $data;
    }



    public static function detect (?string $ip = null, ?string $ua = null) : array|false
    {
        if ( env( 'IPGEO_API_KEY' ) )
        {// Value found
            // (Getting the value)
            $ip = $ip ?? ip();



            // (Getting the value)
            $ip_key = 'ip:' . $ip;



            // (Getting the value)
            $cache_ip_info = redis_get( $ip_key );



            // (Getting the value)
            $ip_info = $cache_ip_info ? json_decode( $cache_ip_info, true ) : self::get_ip_info( $ip );

            if ( !$ip_info )
            {// (Unable to get the IP info)
                // Returning the value
                return false;
            }



            if ( !$cache_ip_info )
            {// Value not found
                // (Setting the value)
                redis_set( $ip_key, json_encode( $ip_info ), self::CACHE_TTL );
            }



            // (Getting the value)
            self::$cache['ip'] = $ip_info;



            // (Getting the value)
            $ua = $ua ?? ua();



            // (Getting the value)
            $ua_id = hash( 'sha256', $ua );



            // (Getting the value)
            $ua_key = 'ua:' . $ua_id;



            // (Getting the value)
            $cache_ua_info = redis_get( $ua_key );



            // (Getting the value)
            $ua_info = $cache_ua_info ? json_decode( $cache_ua_info, true ) : self::get_ua_info( $ua );

            if ( !$ua_info )
            {// (Unable to get the UA info)
                // Returning the value
                return false;
            }



            if ( !$cache_ua_info )
            {// Value not found
                // (Setting the value)
                redis_set( $ua_key, json_encode( $ua_info ), self::CACHE_TTL );
            }



            // (Getting the value)
            self::$cache['ua'] = $ua_info;
        }
        else
        {// Value not found
            // (Getting the values)
            $ip_info = self::get_ip_info( $ip ?? ip() );
            $ua_info = self::get_ua_info( $ua ?? ua() );
        }



        // (Getting the value)
        $data =
        [
            'ip' => $ip_info,
            'ua' => $ua_info
        ]
        ;



        // Returning the value
        return $data;
    }
}



?>