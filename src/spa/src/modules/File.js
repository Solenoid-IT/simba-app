// Returns [void]
export const download = function (name, type, content)
{
    // (Getting the value)
    const blob = new Blob( [ content ], { 'type': type } );



    // (Getting the value)
    const link = document.createElement( 'a' );



    // (Setting the properties)
    link.href     = URL.createObjectURL( blob );
    link.download = name;



    // (Triggering the event)
    link.click();



    // (Revoking the object URL)
    URL.revokeObjectURL( link.href );
}

// Returns [void]
export const downloadFromBlob = function (name, blob)
{
    // (Getting the value)
    const a = document.createElement( 'a' );



    // (Setting the properties)
    a.href     = URL.createObjectURL( blob );
    a.download = name;



    // (Triggering the event)
    a.click();



    // (Revoking the object URL)
    URL.revokeObjectURL( a.href );
}



// Returns [Promise:Array<File>]
export const select = function (type, multiple)
{
    // Returning the value
    return new Promise
    (
        function (resolve, reject)
        {
            // (Getting the value)
            const input = document.createElement( 'input' );



            // (Setting the properties)
            input.type          = 'file';
            input.accept        = type || '*/*';
            input.multiple      = multiple || false;
            input.style.display = 'none';



            // (Triggering the event)
            input.click();



            // (Listening for the event)
            input.addEventListener('change', function (event) {
                // (Calling the function)
                resolve( event.target.files );
            });
        }
    )
    ;
}

// Returns [Promise:string]
export const readText = function (file)
{
    // Returning the value
    return new Promise
    (
        function (resolve, reject)
        {
            // (Creating a FileReader)
            const reader = new FileReader();



            // (Listening for the event)
            reader.onload = function (e)
            {
                // (Calling the function)
                resolve( e.target.result );
            };



            // (Reading the file)
            reader.readAsText( file );
        }
    )
    ;
}

// Returns [Promise:ArrayBuffer]
export const read = function (file)
{
    // Returning the value
    return new Promise
    (
        function (resolve, reject)
        {
            // (Creating a FileReader)
            const reader = new FileReader();



            // (Listening for the event)
            reader.onload = function (e)
            {
                // (Calling the function)
                resolve( e.target.result );
            };



            // (Reading the file)
            reader.readAsArrayBuffer( file );
        }
    )
    ;
}