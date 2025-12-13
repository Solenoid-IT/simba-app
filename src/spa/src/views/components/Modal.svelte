<script>

    import { createEventDispatcher, onMount } from 'svelte';

    import * as URL from '../../modules/URL.js';

    const dispatch = createEventDispatcher();



    export let id;
    export let width = '640px';
    export let title;

    export let urlkey = '';

    let element;

    export let api;

    $:
        if ( element )
        {// Value found
            // (Setting the value)
            api = {};

            // Returns [void]
            api.show = function ()
            {
                // (Showing the modal)
                jQuery(element).modal('show');



                if ( !urlkey ) return;



                // (Getting the value)
                const urlParams = new URLSearchParams( window.location.search );

                // (Setting the param)
                urlParams.set( 'modal', urlkey );

                // (Pushing the state)
                history.pushState( {}, null, '?' + urlParams );
            }

            // Returns [void]
            api.hide = function ()
            {
                // (Showing the modal)
                jQuery(element).modal('hide');



                if ( !urlkey ) return;



                // (Getting the value)
                const urlParams = new URLSearchParams( window.location.search );

                // (Setting the param)
                urlParams.delete( 'modal' );

                // (Pushing the state)
                history.pushState( {}, null, '?' + urlParams );
            }



            // (Listening for the event)
            jQuery(element).on('hidden.bs.modal', function (e) {
                // (Stopping the immediate propagation)
                e.stopImmediatePropagation();



                // (Iterating each entry)
                element?.querySelectorAll( '.passwordfield' )?.forEach
                (
                    function (el)
                    {
                        if ( !el.api ) return;



                        // (Setting the value)
                        el.api.setValue( '' );

                        // (Setting the property)
                        el.api.setVisible( false );
                    }
                )
                ;



                // (Triggering the event)
                dispatch('close');



                if ( !urlkey ) return;



                // (Removing the params)
                URL.removeParams( [ 'modal' ] );
            });



            if ( urlkey )
            {// Value found
                if ( URL.hasParam( 'modal' ) && URL.getParam( 'modal' ) === urlkey )
                {// Match OK
                    // (Showing the modal)
                    api.show();
                }
            }
        }
    


    // (Listening for the event)
    onMount
    (
        function ()
        {
            // (Removing the element)
            jQuery('.modal-backdrop').remove();
        }
    )
    ;

</script>

<!-- Modal -->
<div class="modal fade" id="{ id }" tabindex="-1" role="dialog" aria-hidden="true" bind:this={ element }>
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: { width }">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{ title }</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <slot/>
            </div>
            <!--
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div>
            -->
        </div>
    </div>
</div>

<style>

    /*

    .modal-content
    {
        box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px 0px, rgba(60, 64, 67, 0.15) 0px 1px 3px 1px;
    }

    */

</style>