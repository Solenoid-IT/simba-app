<script>

    import { onMount, onDestroy, createEventDispatcher } from 'svelte';

    const dispatch = createEventDispatcher();



    const URL_PREFIX = '/assets/monaco-editor/min/vs';



    let editor = null;



    let element;



    export let name;
    export let required;

    export let value    = '';
    export let language = 'text';
    export let height   = '600px';



    export let api = {};



    // Returns [string]
    api.getValue = function ()
    {
        // Returning the value
        return editor.getValue();
    }

    // Returns [string]
    api.setValue = function (value)
    {
        // Returning the value
        return editor.setValue( value );
    }



    // (Listening for the event)
    onMount
    (
        async function ()
        {
            if ( typeof window.define === 'undefined' || !window.define.amd )
            {// Match failed
                // (Waiting for the promise)
                await new Promise
                (
                    function (resolve, reject)
                    {
                        // (Creating the element)
                        const scriptElement = document.createElement( 'script' );

                        // (Setting the properties)
                        scriptElement.src     = `${URL_PREFIX}/loader.js`;
                        scriptElement.onload  = resolve;
                        scriptElement.onerror = () => reject( new Error( "Unable to load file 'loader.js'" ) );



                        // (Appending the element)
                        document.head.appendChild( scriptElement );
                    }
                )
                ;
            }



            // (Configuring the require)
            window.require.config
            (
                { 
                    'paths':
                    {
                        'vs': URL_PREFIX
                    } 
                }
            )
            ;



            // (Getting the value)
            window.MonacoEnvironment = {
                'getWorkerUrl': function (moduleId, label) {
                    // (Setting the value)
                    let workerUrl = null;

                    switch (label)
                    {
                        case 'json':
                            // (Getting the value)
                            workerUrl = `${URL_PREFIX}/language/json/json.worker.js`;
                        break;

                        case 'css':
                        case 'scss':
                        case 'less':
                            // (Getting the value)
                            workerUrl = `${URL_PREFIX}/language/css/css.worker.js`;
                        break;

                        case 'html':
                        case 'handlebars':
                        case 'razor':
                            // (Getting the value)
                            workerUrl = `${URL_PREFIX}/language/html/html.worker.js`;
                        break;

                        case 'typescript':
                        case 'javascript':
                            // (Getting the value)
                            workerUrl = `${URL_PREFIX}/language/typescript/ts.worker.js`;
                        break;

                        default:
                            // (Getting the value)
                            workerUrl = `${URL_PREFIX}/editor/editor.worker.js`;
                    }



                    // Returning the value
                    return workerUrl;
                }
            };



            // (Requiring the loader)
            window.require( [ 'vs/editor/editor.main' ], function (monaco) {
                // (Creating the editor)
                editor = monaco.editor.create
                (
                    element,
                    {
                        'value':                           value,
                        'language':                        language,
                        'theme':                           'vs-dark',
                        'automaticLayout':                 true,
                        'fontSize':                        18,
                        'bracketPairColorization.enabled': false
                    }
                )
                ;



                // (Triggering the event)
                dispatch( 'ready', api );
            });
        }
    )
    ;

    // (Listening for the event)
    onDestroy
    (
        function ()
        {
            if ( editor )
            {// Value found
                // (Disposing the editor)
                editor.dispose();
            }
        }
    )
    ;



    $:
        if ( element )
        {// Value found
            // (Getting the value)
            element.api =
            {
                'getValue': api.getValue,
                'setValue': api.setValue
            }
            ;



            // (Setting the property)
            element.style.height = height;
        }

</script>

<div class="monaco-editor input form-input" bind:this={ element } data-input={ name } data-required={ required }></div>

<style>

    .monaco-editor
    {
        width: 100%;
        height: 600px;
        margin: 0;
        padding: 0;
        overflow: hidden;
    }

</style>