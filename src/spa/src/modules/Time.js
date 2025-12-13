// Returns [object]
export const parse  = function (value)
{
    // (Getting the value)
    const split = value.split( ' ' );



    // (Getting the values)
    const [ Y, m, d ] = split[0].split( '-' );
    const [ H, i, s ] = split[1].split( ':' );



    // (Getting the value)
    const parsed =
    {
        'Y': parseInt( Y ),
        'm': parseInt( m ),
        'd': parseInt( d ),

        'H': parseInt( H ),
        'i': parseInt( i ),
        's': parseInt( s )
    }
    ;



    // Returning the value
    return parsed;
}

// Returns [number]
export const toUnix = function (value)
{
    // Returning the value
    return Math.floor( new Date( value.replace( ' ', 'T' ) + 'Z' ).getTime() / 1000 );
}

// Returns [number]
export const now = function ()
{
    // Returning the value
    return Math.floor( Date.now() / 1000 );
}

// Returns [string]
export const toLocal = function (value)
{
    // (Getting the value)
    const d = new Date( value.replace( ' ', 'T' ) + 'Z' );



    // (Getting the value)
    const parsed =
    {
        'Y': d.getFullYear().toString(),
        'm': ( d.getMonth() + 1 ).toString().padStart( 2, '0' ),
        'd': d.getDate().toString().padStart( 2, '0' ),

        'H': d.getHours().toString().padStart( 2, '0' ),
        'i': d.getMinutes().toString().padStart( 2, '0' ),
        's': d.getSeconds().toString().padStart( 2, '0' )
    }
    ;



    // Returning the value
    return `${ parsed['Y'] }-${ parsed['m'] }-${ parsed['d'] } ${ parsed['H'] }:${ parsed['i'] }:${ parsed['s'] }`;
}



// Returns [object]
export const parseTime = function (timestamp)
{
    if ( timestamp === undefined ) timestamp = now();



    // (Getting the value)
    const dt = new Date( timestamp * 1000 );



    // (Setting the value)
    let parsed =
    {
        'Y': null,
        'm': null,
        'd': null,

        'H': null,
        'i': null,
        's': null
    }
    ;



    // (Getting the value)
    parsed['Y'] = dt.getFullYear().toString();
    parsed['m'] = ( dt.getMonth() + 1 ).toString().padStart( 2, '0' );
    parsed['d'] = ( dt.getDate() ).toString().padStart( 2, '0' );

    parsed['H'] = dt.getHours().toString().padStart( 2, '0' );
    parsed['i'] = dt.getMinutes().toString().padStart( 2, '0' );
    parsed['s'] = dt.getSeconds().toString().padStart( 2, '0' );



    // Returning the value
    return parsed;
}

// Returns [string]
export const format = function (timestamp, format)
{
    if ( format === undefined ) format = 'Y-m-d H:i:s';



    // (Getting the value)
    let formattedText = format;



    // (Getting the value)
    const parsed = parseTime( timestamp );



    for ( const k in parsed )
    {// Processing each entry
        // (Replacing the text)
        formattedText = formattedText.replace( k , parsed[k] );
    }



    // Returning the value
    return formattedText;
}



// Returns [object]
export const parseSeconds = function (seconds, pad)
{
    // (Getting the value)
    const parsed =
    {
        'D': Math.floor( seconds / (24 * 60 * 60) ),

        'H': Math.floor( (seconds % (24 * 60 * 60) ) / (60 * 60) ),
        'M': Math.floor( (seconds % (60 * 60)) / (60) ),
        'S': Math.floor( seconds % 60 )
    }
    ;



    if ( pad )
    {// Value is true
        // (Getting the values)
        parsed['D'] = parsed['D'].toString();
        parsed['H'] = parsed['H'].toString().padStart( 2, '0' );
        parsed['M'] = parsed['M'].toString().padStart( 2, '0' );
        parsed['S'] = parsed['S'].toString().padStart( 2, '0' );
    }



    // Returning the value
    return parsed;
}

// Returns [string]
export const formatSeconds = function (seconds, format)
{
    if ( !format ) format = 'D, H:M:S';



    // (Getting the value)
    const parsed = parseSeconds( seconds, true );

    for ( const k in parsed )
    {// Processing each entry
        // (Getting the value)
        format = format.replace( k, parsed[k] );
    }



    // Returning the value
    return format;
}