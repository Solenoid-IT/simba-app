<script>

    export let api =
    {
        'online':  true,
        'message': ''
    }
    ;



    const MESSAGE_TIMEOUT = 10;// 10 s

    let timeoutId;


    // Returns [void]
    function onMessageChange (val)
    {
        if ( api.online )
        {// Value is true
            // (Clearing the timeout)
            clearTimeout( timeoutId );

            // (Setting the value)
            timeoutId = setTimeout
            (
                function ()
                {
                    // (Setting the value)
                    api.message = '';
                },
                MESSAGE_TIMEOUT * 1000
            )
            ;
        }
    }

    $: onMessageChange( api.message );

</script>

{ #if api.message }
    <div class="network-status" class:online={ api.online } class:offline={ !api.online }>
        { @html api.message }
    </div>
{ /if }

<style>

    .network-status
    {
        width: 200px;
        height: 30px;
        margin: 10px;
        padding: 4px 10px;
        position: fixed;
        z-index: 9999;
        right: 0;
        top: 0;
        color: #ffffff;
        border-radius: 4px;
    }

    .network-status.online
    {
        background-color: var(--success);
    }

    .network-status.offline
    {
        background-color: var(--danger);
    }

</style>