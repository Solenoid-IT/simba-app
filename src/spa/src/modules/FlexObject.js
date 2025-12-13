// (Setting the value)
const SEPARATOR = '.';



// Returns [void]
function _parseDotNotation (str, val, obj, separator)
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



// Returns [object]
export function expand (object)
{
    for ( const key in object )
    {// Processing each entry
        if ( key.indexOf( SEPARATOR ) !== -1 )
        {// Match OK
            // (Parsing the dot notation)
            _parseDotNotation( key, object[key], object, SEPARATOR );
        }            
    }



    // Returning the value
    return object;
}

// Returns [object]
export function compress (object)
{
    // (Setting the value)
    let output = {};

    for ( const i in object )
    {// Processing each entry
        if ( !object.hasOwnProperty(i) )
        {// Match failed
            // Continuing the iteration
            continue;
        }



        if ( ( typeof object[i] ) === 'object' && object[i] !== null && !( object[i] instanceof Array ) )
        {// Match OK
            // (Calling the function)
            let currentObject = compress( object[i], SEPARATOR );

            for ( const x in currentObject )
            {// Processing each entry
                if ( !currentObject.hasOwnProperty(x) )
                {// Match failed
                    // Continuing the iteration
                    continue;
                }



                // (Getting the value)
                output[ i + SEPARATOR + x ] = currentObject[x];
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