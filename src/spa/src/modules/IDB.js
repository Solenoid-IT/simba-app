// © Solenoid-IT

export class Connection
{
    constructor (database, store)
    {
        // (Getting the values)
        this.database = database;
        this.store    = store;
    }



    // Returns [Promise:IDBDatabase|Error]
    async open ()
    {
        // (Getting the value)
        const instance = this;



        // Returning the value [Promise:void]
        return new Promise
        (
            function (resolve, reject)
            {
                // (Getting the value)
                const response = indexedDB.open( instance.database, 1 );



                // (Listening for the events)
                response.onsuccess = function (event)
                {
                    // (Getting the value)
                    instance.connection = event.target.result;

                    // (Calling the function)
                    resolve( instance.connection );
                }
                ;

                response.onerror   = function (event) { reject( event.target.error ) };

                /*
                
                request.onupgradeneeded = function (event)
                {
                    // (Getting the value)
                    const db = event.target.result;console.debug(event, db);

                    if ( !db.objectStoreNames.contains( p.store ) )
                    {// Value not found
                        // (Creating the object store)
                        //db.createObjectStore( p.store, { 'keyPath': 'id' } );
                        db.createObjectStore( p.store, { 'autoIncrement': autoIncrement } );
                    }
                }
                ;

                */
            }
        )
        ;
    }

    // Returns [Promise:bool]
    async close ()
    {
        if ( this.connection )
        {// Value found
            // (Closing the connection)
            this.connection.close();

            // (Setting the value)
            this.connection = null;
        }



        // Returning the value
        return this;
    }



    // Returns [Promise:bool]
    async push (value, key)
    {
        if ( key === undefined ) key = null;



        // (Opening the database)
        const connection = await this.open();



        // (Setting the value)
        const response = connection.transaction( this.store, 'readwrite' ).objectStore( this.store ).add( value, key );



        // Returning the value
        return new Promise
        (
            function (resolve, reject)
            {
                // (Listening for the events)
                response.onsuccess = function () { resolve( true ); };
                response.onerror   = function () { reject( response.error ); };
            }
        )
        ;
    }



    // Returns [Promise:string|null]
    async get (key)
    {
        // (Opening the database)
        const connection = await this.open();



        // (Setting the value)
        const response = connection.transaction( this.store, 'readonly' ).objectStore( this.store ).get( key );



        // Returning the value
        return new Promise
        (
            function (resolve, reject)
            {
                // (Listening for the events)
                response.onsuccess = function () { resolve( response.result === undefined ? null : response.result ); };
                response.onerror   = function () { reject( response.error ); };
            }
        )
        ;
    }

    // Returns [Promise:bool]
    async set (key, value)
    {
        // (Opening the connection)
        const connection = await this.open();



        // (Setting the value)
        const response = connection.transaction( this.store, 'readwrite' ).objectStore( this.store ).put( value, key );



        // Returning the value
        return new Promise
        (
            function (resolve, reject)
            {
                

                // (Listening for the events)
                response.onsuccess = function () { resolve( true ); };
                response.onerror   = function () { reject( response.error ); };
            }
        )
        ;
    }

    // Returns [Promise:bool]
    async remove (key)
    {
        // (Opening the database)
        const connection = await this.open();



        // (Getting the value)
        const response = connection.transaction( this.store, 'readwrite' ).objectStore( this.store ).delete( key );



        // Returning the value
        return new Promise
        (
            function (resolve, reject)
            {
                // (Listening for events)
                response.onsuccess = function () { resolve( response.result ) };
                response.onerror   = function () { reject( response.error ) };
            }
        )
        ;
    }



    // Returns [Promise:object]
    async list (type)
    {
        if ( type === undefined ) type = 'object';



        // (Opening the database)
        const connection = await this.open();



        // (Getting the value)
        const response = connection.transaction( this.store, 'readonly' ).objectStore( this.store ).openCursor();



        // Returning the value
        return new Promise
        (
            function (resolve, reject)
            {
                // (Setting the value)
                const results = {};



                // (Listening for event)
                response.onsuccess = function ()
                {
                    // (Getting the value)
                    const cursor = response.result;

                    if ( cursor )
                    {// Value found
                        // (Getting the value)
                        results[ cursor.key ] = cursor.value;



                        // (Continuing the iteration)
                        cursor.continue();
                    }
                    else
                    {// Value not found
                        switch ( type )
                        {
                            case 'object':
                                // (Calling the function)
                                resolve( results );
                            break;

                            case 'values':
                                // (Calling the function)
                                resolve( Object.values( results ) );
                            break;
                        }
                    }
                }
                ;

                // (Listening for the event)
                response.onerror = function () { reject( response.error ) };
            }
        )
        ;
    }
}