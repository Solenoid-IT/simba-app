<script>

    import { createEventDispatcher } from 'svelte';

    import SearchTextfield from './SearchTextfield.svelte';



    const dispatch = createEventDispatcher();



    export let input       = 'location';
    export let required    = false;
    export let placeholder = '';

    export let value       = '';

    export let results     = [];

    export let reset;



    // Returns [void]
    function onChange (event)
    {
        // (Triggering the event)
        dispatch( 'change', event.detail );
    }

    // Returns [void]
    function onSelect (event)
    {
        // (Triggering the event)
        dispatch( 'select', event.detail );
    }



    // Returns [void]
    function onLocalizeButtonClick ()
    {
        // (Opening the URL)
        //Solenoid.Location.open( `https://www.google.it/maps/place/Viale+F.+Chabod,+11,+11100+Aosta+AO/@45.7420633,7.3210601` );
        Solenoid.Location.open( `https://www.google.it/maps/place/${ encodeURI( value ) }`, true );
    }

</script>

<SearchTextfield bind:input={ input } bind:value={ value } bind:required={ required } bind:placeholder={ placeholder } bind:results={ results } bind:reset={ reset } on:change={ onChange } on:select={ onSelect }>
    <button type="button" class="btn btn-sm btn-secondary" title="localize" style="width: 38px;" on:click={ onLocalizeButtonClick }>
        <i class="fa-solid fa-location-dot"></i>
    </button>
</SearchTextfield>