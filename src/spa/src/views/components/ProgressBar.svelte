<script>

    export let name  = 'Progress';
    export let value = 20;

    export let visible = false;

    export let api = {};



    // Returns [void]
    api.show = function ()
    {
        // (Setting the value)
        visible = true;
    }

    // Returns [void]
    api.hide = function ()
    {
        // (Setting the value)
        visible = false;
    }



    // Returns [void]
    api.setName = function (val)
    {
        // (Getting the value)
        name = val;
    }

    // Returns [void]
    api.setValue = function (val)
    {
        // (Getting the value)
        value = val;
    }

</script>

{ #if visible }
    <div class="progress-bar-container">
        <div class="progress-box">
            <h4 class="small font-weight-bold">
                { name }<span class="float-right">{ value } %</span>
            </h4>

            <div class="progress">
                <div class="progress-bar bg-primary" role="progressbar" style="width: { value }%;"></div>
            </div>
        </div>

        <div class="progress-blackscreen"></div>
    </div>
{ /if }

<style>

    .progress-box
    {
        width: 80%;
        height: 90px;
        padding: 20px;
        margin: auto;
        display: block;
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 8000;
        background-color: var( --simba-dark );
        border-radius: 4px;
    }

    .progress-blackscreen
    {
        width: 100%;
        height: 100%;
        position: fixed;
        left: 0;
        right: 0;
        top: 0;
        bottom: 0;
        z-index: 1000;
        background-color: #00000099;
    }

</style>