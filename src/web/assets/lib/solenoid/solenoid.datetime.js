// © Solenoid Team



if (typeof Solenoid === 'undefined') Solenoid = {};

Solenoid.DateTime = {};



// Returns [string]
Solenoid.DateTime.secondsToDHMS = function (seconds, format)
{
    if ( format === undefined ) format = '%D days, %H:%M:%S';



    // (Getting the value)
    const components =
    {
        '%D': Math.floor( seconds / (24 * 60 * 60) ),

        '%H': Math.floor( (seconds % (24 * 60 * 60) ) / (60 * 60) ),
        '%M': Math.floor( (seconds % (60 * 60)) / (60) ),
        '%S': Math.floor( seconds % 60 )
    }
    ;

    for ( const k in components )
    {// Processing each entry
        // (Getting the value)
        let v = components[k];

        if ( k in [ '%H', '%M', '%S' ] )
        {// Match OK
            // (Getting the value)
            v = v.toString().padStart( 2, '0' );
        }



        // (Getting the value)
        format = format.replace( k, v );
    }



    // Returning the value
    return format;
}



Solenoid.DateTime.Duration = function (value)
{
    const public  = this;
    const private = {};



    public.value = value;



    // Returns [string]
    public.format = function (pattern)
    {
        if ( pattern === undefined ) pattern = '%D days, %H:%M:%S';



        // (Getting the value)
        let value = pattern;



        // (Getting the value)
        const components =
        {
            '%D': Math.floor( public.value / (24 * 60 * 60) ),

            '%H': Math.floor( (public.value % (24 * 60 * 60) ) / (60 * 60) ),
            '%M': Math.floor( (public.value % (60 * 60)) / (60) ),
            '%S': Math.floor( public.value % 60 )
        }
        ;

        for ( const k in components )
        {// Processing each entry
            // (Getting the value)
            let v = components[k];

            if ( [ '%H', '%M', '%S' ].includes( k ) )
            {// Match OK
                // (Getting the value)
                v = v.toString().padStart( 2, '0' );
            }



            // (Getting the value)
            value = value.replace( k, v );
        }



        // Returning the value
        return value;
    }
}