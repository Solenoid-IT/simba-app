// © Solenoid Team



if (typeof Solenoid === 'undefined') Solenoid = {};

Solenoid.Object = {};



// (Setting the value)
Solenoid.Object.SEPARATOR = '.';



// Returns [void]
Solenoid.Object._parseDotNotation = function (str, val, obj, separator)
{
    let currentObj = obj,
        keys = str.split(separator),
        i, l = Math.max(1, keys.length - 1),
        key;

    for (i = 0; i < l; ++i) {
        key = keys[i];
        currentObj[key] = currentObj[key] || {};
        currentObj = currentObj[key];
    }

    currentObj[keys[i]] = val;
    delete obj[str];
}
;



// Returns [object]
Solenoid.Object.expand = function (object, separator)
{
    if ( typeof separator === 'undefined' ) separator = Solenoid.Object.SEPARATOR;



    for ( const key in object )
    {// Processing each entry
        if ( key.indexOf( separator ) !== -1 )
        {// Match OK
            // (Parsing the dot notation)
            Solenoid.Object._parseDotNotation( key, object[key], object, separator );
        }            
    }



    // Returning the value
    return object;
}
;

// Returns [object]
Solenoid.Object.compress = function (object, separator)
{
    if ( typeof separator === 'undefined' ) separator = Solenoid.Object.SEPARATOR;



    // (Setting the value)
    let output = {};

    for (const i in object)
    {// Processing each entry
        if (!object.hasOwnProperty(i))
        {// Match failed
            // Continuing the iteration
            continue;
        }



        if ( ( typeof object[i] ) === 'object' && object[i] !== null && !( object[i] instanceof Array ) )
        {// Match OK
            // (Calling the function)
            let currentObject = Solenoid.Object.compress( object[i], separator );

            for ( const x in currentObject )
            {// Processing each entry
                if (!currentObject.hasOwnProperty(x))
                {// Match failed
                    // Continuing the iteration
                    continue;
                }



                // (Getting the value)
                output[i+separator+x] = currentObject[x];
            }
        }
        else
        {// Match failed
            // (Getting the value)
            output[i] = object[i];
        }
    }



    // Returning the value
    return output;
}