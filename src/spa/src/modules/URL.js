// Returns [bool]
export const hasParam = function (key)
{
    // (Getting the value)
    const urlParams = new URLSearchParams( window.location.search );

    // (Returning the value)
    return urlParams.has( key );
}

// Returns [object]
export const getParam = function (key, type)
{
    // (Getting the value)
    const urlParams = new URLSearchParams( window.location.search );



    // (Getting the value)
    const value = urlParams.get( key );

    if ( value === null )
    {// Value not found
        // (Returning the value)
        return null;
    }



    switch ( type )
    {
        case 'int':
            // Returning the value
            return parseInt( value );
        break;

        case 'boolean':
            // Returning the value
            return value === '1' || value === 'true';
        break;
    }



    // (Returning the value)
    return value;
}



// Returns [object]
export const getParams = function ()
{
    // (Getting the value)
    const urlParams = new URLSearchParams( window.location.search );

    // (Returning the value)
    return Object.fromEntries( urlParams );
}

// Returns [void]
export const setParams = function (params)
{
    // (Getting the value)
    const urlParams = new URLSearchParams( window.location.search );

    for ( const k in params )
    {// Processing each entry
        // (Setting the param)
        urlParams.set( k, params[k] );
    }

    // (Pushing the state)
    history.pushState( {}, null, urlParams.size === 0 ? window.location.pathname : '?' + urlParams );
}

// Returns [void]
export const removeParams = function (keys)
{
    // (Getting the value)
    const urlParams = new URLSearchParams( window.location.search );

    for ( const k of keys )
    {// Processing each entry
        // (Deleting the param)
        urlParams.delete( k );
    }

    // (Pushing the state)
    history.pushState( {}, null, urlParams.size === 0 ? window.location.pathname : '?' + urlParams );
}