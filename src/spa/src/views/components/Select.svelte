<script>

    import { createEventDispatcher } from 'svelte';

    const dispatch = createEventDispatcher();



    export let element;

    export let options       = [];
    export let selectedIndex = false;

    export let input    = null;
    export let required = null;

    export let searchable = null;

    export let api = {};



    let listVisible = false;
    let content     = null;

    let searchTimeout = null;
    let searchValue   = '';



    
    // Returns [void]
    function selectNext ()
    {
        // (Setting the value)
        let lastSelectedElementIndex = false;

        for ( let i = 0; i < options.length; i++ )
        {// Iterating each index
            if ( options[i]['selected'] )
            {// Value is true
                if ( i === options.length - 1 )
                {// (Index is the last)
                    // (Getting the value)
                    lastSelectedElementIndex = i;

                    // Breaking the iteration
                    break;
                }



                // (Setting the value)
                options[i]['selected'] = false;

                if ( options[ i + 1 ] !== undefined )
                {// Value found
                    // (Getting the value)
                    selectedIndex = i + 1;

                    // (Setting the value)
                    options[ selectedIndex ]['selected'] = true;

                    // (Getting the value)
                    lastSelectedElementIndex = selectedIndex;

                    // (Getting the value)
                    content = options[ selectedIndex ]['content'] ?? options[ selectedIndex ]['value'];

                    // Breaking the iteration
                    break;
                }
            }
        }



        if ( lastSelectedElementIndex === false )
        {// Value not found
            // (Setting the value)
            selectedIndex = 0;

            // (Setting the value)
            options[ selectedIndex ]['selected'] = true;
        }



        // (Getting the value)
        options = options;
    }

    // Returns [void]
    function selectPrev ()
    {
        // (Setting the value)
        let lastSelectedElementIndex = false;

        for ( let i = 0; i < options.length; i++ )
        {// Iterating each index
            if ( options[i]['selected'] )
            {// Value is true
                if ( i === 0 )
                {// (Index is the last)
                    // (Getting the value)
                    lastSelectedElementIndex = i;

                    // Breaking the iteration
                    break;
                }



                // (Setting the value)
                options[i]['selected'] = false;

                if ( options[ i - 1 ] !== undefined )
                {// Value found
                    // (Getting the value)
                    selectedIndex = i - 1;

                    // (Setting the value)
                    options[ selectedIndex ]['selected'] = true;

                    // (Getting the value)
                    lastSelectedElementIndex = selectedIndex;

                    // (Getting the value)
                    content = options[ selectedIndex ]['content'] ?? options[ selectedIndex ]['value'];

                    // Breaking the iteration
                    break;
                }
            }
        }



        if ( lastSelectedElementIndex === false )
        {// Value not found
            // (Getting the value)
            selectedIndex = options.length - 1;

            // (Setting the value)
            options[ selectedIndex ]['selected'] = true;
        }



        // (Getting the value)
        options = options;
    }



    // Returns [void]
    function selectIndex (index)
    {
        for ( let i = 0; i < options.length; i++ )
        {// Iterating each index
            // (Setting the value)
            options[i]['selected'] = false;
        }



        // (Getting the value)
        selectedIndex = index;

        for ( let i = 0; i < options.length; i++ )
        {// Iterating each index
            if ( i !== selectedIndex ) continue;



            // (Setting the value)
            options[ selectedIndex ]['selected'] = true;

            // (Getting the value)
            content = options[ selectedIndex ]['content'] ?? options[ selectedIndex ]['value'];
        }
    }

    // Returns [void]
    function selectValue (value)
    {
        for ( let i = 0; i < options.length; i++ )
        {// Iterating each index
            if ( options[i]['value'] === value )
            {// Value found
                // (Selecting the index)
                selectIndex( i );

                // Breaking the iteration
                break;
            }
        }
    }



    // Returns [void]
    function onListItemClick (index)
    {
        for ( let i = 0; i < options.length; i++ )
        {// Iterating each index
            // (Getting the value)
            options[i]['selected'] = i === index;
        }

        // (Getting the value)
        options = options;



        // (Getting the value)
        content = options[index]['content'] ?? options[index]['value'];



        // (Getting the value)
        selectedIndex = index;



        // (Triggering the event)
        element.querySelector('.form-input').focus();



        // (Triggering the event)
        dispatch( 'change', options[index] );



        // (Setting the value)
        listVisible = false;
    }



    // Returns [void]
    function onSelectBoxKeyDown (event)
    {
        switch ( event.key )
        {
            case 'Enter':
                // (Getting the value)
                listVisible = !listVisible;
            break;

            case 'ArrowDown':
                // (Selecting the next element)
                selectNext();
            break;

            case 'ArrowUp':
                // (Selecting the previous element)
                selectPrev();
            break;

            default:
                //console.debug(event.key);
        }
    }

    // Returns [void]
    function onSelectBoxKeyPress (event)
    {
        if ( searchable ) return;



        // (Clearing the timeout)
        clearTimeout( searchTimeout );



        // (Appending the value)
        searchValue += event.key;



        // (Setting the timeout)
        searchTimeout = setTimeout
        (
            function ()
            {
                for ( let i = 0; i < options.length; i++ )
                {// Iterating each index
                    // (Getting the value)
                    const html = document.createElement( 'div' );

                    // (Setting the property)
                    html.innerHTML = options[i]['content'] ?? options[i]['value'];



                    if ( html.innerText.toLowerCase().indexOf( searchValue.toLowerCase() ) !== -1 )
                    {// Match OK
                        // (Selecting the index)
                        selectIndex( i );

                        // Breaking the iteration
                        break;
                    }
                }



                // (Setting the value)
                searchValue = '';
            },
            300
        )
        ;
    }

    //$: console.debug(searchValue);



    $:
        if ( element )
        {// Value found
            // (Getting the value)
            element.api =
            {
                'getValue': function ()
                {
                    // Returning the value
                    return selectedIndex === false ? null : options[ selectedIndex ]['value'];
                },

                'setValue': function (val)
                {
                    for ( const k in options )
                    {// Processing each entry
                        if ( options[k]['value'] === val )
                        {// Match OK
                            // (Getting the value)
                            selectedIndex = k;

                            // (Setting the value)
                            options[ selectedIndex ]['selected'] = true;

                            // (Getting the value)
                            content = options[ selectedIndex ]['content'] ?? options[ selectedIndex ]['value'];

                            // Breaking the iteration
                            break;
                        }
                    }
                },

                'reset': function ()
                {
                    for ( const k in options )
                    {// Processing each entry
                        // (Setting the value)
                        options[k]['selected'] = false;

                        // (Setting the value)
                        options[k]['hidden'] = false;
                    }

                    // (Setting the value)
                    selectedIndex = false;

                    // (Setting the value)
                    listVisible = false;

                    // (Setting the value)
                    content = '';
                }
            }
            ;
        }
    
    
    
    $:
        if ( !listVisible )
        {// (List is not visible)
            for ( let i = 0; i < options.length; i++ )
            {// Iterating each index
                // (Setting the value)
                options[i]['hidden'] = false;
            }
        }
    
    
    
    // Returns [void]
    function onSearchInput (event)
    {
        for ( let i = 0; i < options.length; i++ )
        {// Iterating each index
            // (Getting the value)
            const html = document.createElement( 'div' );

            // (Setting the property)
            html.innerHTML = options[i]['content'] ?? options[i]['value'];



            // (Getting the value)
            options[i]['hidden'] = html.innerText.toLowerCase().indexOf( event.target.value.toLowerCase() ) === -1;
        }
    }



    // Returns [void]
    api.setValue = function (value)
    {
        // (Selecting the value)
        selectValue( value );
    }

</script>

<div class="select-box form-widget" bind:this={ element } data-input={ input } data-required={ required } on:keydown|stopPropagation={ onSelectBoxKeyDown } on:keypress={ onSelectBoxKeyPress }>
    <!-- svelte-ignore a11y-click-events-have-key-events -->
    <div class="input-group" on:click={ () => { listVisible = !listVisible; } }>
        <!-- svelte-ignore a11y-no-noninteractive-tabindex -->
        <div class="value form-control form-input" tabindex="0">{ @html content }</div>
        <div class="input-group-append">
            <button type="button" class="btn btn-sm btn-secondary">
                { #if listVisible }
                    <i class="fa-solid fa-caret-up"></i>
                { :else }
                    <i class="fa-solid fa-caret-down"></i>
                { /if }
            </button>
        </div>
    </div>

    { #if listVisible }
        { #if searchable }
            <div class="input-group mt-2">
                <input type="text" class="form-control form-control-sm input" name="search" value="" autocomplete="off" on:input|stopPropagation={ onSearchInput }>
                <div class="input-group-append">
                    <button type="button" class="btn btn-sm btn-secondary" disabled>
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </div>
            </div>
        { /if }

        <ul class="mt-2">
            { #each options as option, i }
                { #if !option['hidden'] }
                    <!-- svelte-ignore a11y-click-events-have-key-events -->
                    <li class:selected={ option['selected'] } on:click={ () => { onListItemClick( i ); } }>{ @html option['content'] ?? option['value'] }</li>
                { /if }
            { /each }
        </ul>
    { /if }
</div>

<style>

    .select-box
    {
        position: relative;
    }

    .select-box ul
    {
        margin: 0;
        padding: 10px 0;
        list-style: none;
        position: absolute;
        left: 0;
        right: 0;
        z-index: 4;
        border-radius: 4px;
        background-color: var( --simba-dark-bg );
        border: 1px solid var( --simba-dark-border );
    }

    .select-box ul li
    {
        padding: 4px 8px;
        color: #6e707e;
        background-color: var( --simba-dark-card-body );
        cursor: pointer;
    }

    .select-box ul li:hover
    {
        background-color: var( --simba-dark-bg )
    }

    .select-box ul li.selected
    {
        color: #ffffff;
        background-color: var( --simba-primary-hover );
    }

    .value
    {

    }

</style>