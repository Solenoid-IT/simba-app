<script>

    import { createEventDispatcher } from 'svelte';

    const dispatch = createEventDispatcher();



    export let element     = null;

    export let input       = 'location';
    export let required    = false;
    export let placeholder = '';

    export let value       = '';

    export let results     = [];

    export function reset ()
    {
        // (Setting the values)
        value   = '';
        results = [];
    }



    let inputTimeout;

    /*

    // Returns [void]
    function onValueChange (val)
    {
        // Clearing the timeout
        clearTimeout( inputTimeout );

        // (Setting the timeout)
        inputTimeout = setTimeout
        (
            async function ()
            {
                // (Triggering the event)
                dispatch( 'change', val );
            },
            1000
        )
        ;
    }

    $: onValueChange( value );

    */

    // Returns [void]
    function onInput (event)
    {
        // Clearing the timeout
        clearTimeout( inputTimeout );

        // (Setting the timeout)
        inputTimeout = setTimeout
        (
            async function ()
            {
                // (Triggering the event)
                dispatch( 'change', event.target.value );
            },
            1000
        )
        ;
    }



    // Returns [void]
    function onClick (result, index)
    {
        // (Getting the value)
        value = result.value;



        for ( let i = 0; i < results.length; i++ )
        {// Iterating each step
            // (Setting the value)
            results[i]['selected'] = false;
        }

        // (Setting the value)
        results[index]['selected'] = true;



        // (Triggering the event)
        element.querySelector('.input').click();



        // (Triggering the event)
        dispatch( 'select', result );
    }



    $:
        if ( element )
        {// Value found
            // (Setting the value)
            element.api =
            {
                'reset': reset
            }
            ;
        }

</script>

<div class="search-textfield form-widget" bind:this={ element }>
    <div class="input-group">
        <input type="text" class="form-control input form-input" name="{ input }" data-required={ required ? true : null } placeholder={ placeholder } bind:value={ value } on:input={ onInput } autocomplete="off">
        <div class="input-group-append">
            <slot/>
        </div>
    </div>

    { #if results.length > 0 }
        <ul class="results">
            { #each results as result, i }
                <li class:selected={ result['selected'] }>
                    <!-- svelte-ignore a11y-click-events-have-key-events -->
                    <span on:click={ onClick( result, i ) }>
                        { @html result['content'] ?? result['value'] }
                    </span>
                </li>
            { /each }
        </ul>
    { /if }
</div>

<style>

    .results
    {
        margin: 0;
        margin-top: 8px;
        padding: 10px 0;
        list-style: none;
        border-radius: 4px;
        background-color: var( --simba-dark-bg );
        border: 1px solid var( --simba-dark-border );
    }

    .results li
    {
        padding: 4px 8px;
        color: #6e707e;
        background-color: #bebebe;
        background-color: var( --simba-dark-card-body );
    }

    .results li:nth-child(even)
    {
        background-color: #cfcfcf;
        background-color: var( --simba-dark-bg )
    }
    
    .results li:hover
    {
        color: #ffffff;
        background-color: var( --simba-primary );
    }

    .results li span
    {
        color: inherit;
        display: block;
        cursor: pointer;
    }

    .results li.selected
    {
        color: #ffffff;
        background-color: var( --simba-primary-hover );
    }

</style>