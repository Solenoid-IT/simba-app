<script>

    import { envs } from '@/envs.js';

    import { onMount } from 'svelte';

    import { appReady } from '@/stores/appReady.js';
    import { pendingRequest } from '@/stores/pendingRequest.js';

    import '@/app.css';
    import '@/app.custom.css';

    import Loader from '@/views/components/Loader.svelte';

    import { idk } from '@/stores/idk.js';
    import { idbConnections } from '@/stores/idbConnections.js';



    const APP_NAME = import.meta.env.VITE_APP_NAME;



    let loaderVisible = false;



    // Returns [Promise:bool]
    async function initIDB (db)
    {
        // Returning the value
        return new Promise
        (
            function (resolve, reject)
            {
                // (Getting the value)
                const response = indexedDB.open( db, 1 );

                // (Listening for the event)
                response.onupgradeneeded = function (event)
                {
                    // (Getting the value)
                    const db = event.target.result;

                    if ( !db.objectStoreNames.contains( 'idk' ) )
                    {// Value not found
                        // (Creating the object store)
                        db.createObjectStore( 'idk', { 'autoIncrement': false } );
                    }

                    if ( !db.objectStoreNames.contains( 'request' ) )
                    {// Value not found
                        // (Creating the object store)
                        db.createObjectStore( 'request', { 'autoIncrement': false } );
                    }
                }
                ;

                // (Listening for the event)
                response.onsuccess = function (event)
                {
                    // (Calling the function)
                    resolve( true );
                }
                ;
            }
        )
        ;
    }



    // (Listening for the event)
    onMount
    (
        async function ()
        {
            // (Listening for the event)
            window.addEventListener('message', function (event) {
                if ( event.origin !== window.location.origin ) return;



                // (Getting the value)
                const message = event.data;

                switch ( message.type )
                {
                    case 'request-start':
                        // (Setting the value)
                        loaderVisible = true;
                    break;

                    case 'request-end':
                        // (Setting the value)
                        loaderVisible = false;
                    break;
                }
            });



            // (Click-Event on the element)
            jQuery('body').delegate('.copyable', 'click', function () {
                // (Writing to the clipboard)
                navigator.clipboard.writeText( this.getAttribute( 'data-value' ) === null ? this.innerText : this.getAttribute( 'data-value' ) );
            });



            // (Initializing the IDB)
            await initIDB( envs.IDB_DATABASE );



            /*

            if ( window.location.port === '' )
            {// (URL is for production env)
                // (Registering the worker)
                navigator.serviceWorker.register( '/service-worker.js', { 'scope': '/' } )
                .then
                (
                    function (registration)
                    {
                        // (Logging the value)
                        console.log( 'Service-Worker :: Registered', registration );
                    }
                )
                .catch
                (
                    function (error)
                    {
                        // (Logging the value)
                        console.log( 'Service-Worker :: Error :: ' + error );
                    }
                )
                ;
            }

            */



            // (Logging the message)
            console.log( `${ APP_NAME }/${ '1.0.0' } -> READY` );
        }
    )
    ;

</script>

{ #if loaderVisible || $pendingRequest }
    <Loader/>
{ /if }

<div class="app" class:dark-mode={ true }>
    <slot/>
</div>