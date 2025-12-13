// © Solenoid Team



if (typeof Solenoid === 'undefined') Solenoid = {};

Solenoid.Location = {};



// Returns [void]
Solenoid.Location.write = function (path, params)
{
    // (Getting the value)
    const urlParams = new URLSearchParams( window.location.search );

    for ( const key in params )
    {// Processing each entry
        // (Setting the param)
        urlParams.set( key, params[key] );
    }



    // (Pushing the state)
    history.pushState( {}, null, `${ path ? path : '' }${ params ? '?' + urlParams : '' }` );
}

// Returns [void]
Solenoid.Location.open = function (url, newTab)
{
    // (Creating an anchor-element)
    const a = document.createElement('a');

    // (Setting the properties)
    a.href   = url;
    a.target = newTab ? '_blank' : '_self';



    // (Triggering the event)
    a.click();



    // (Removing the element)
    a.remove();
}