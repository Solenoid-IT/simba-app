<script>

    import { onMount } from 'svelte';



    export let element;

    export let mediaStream;
    export let autoplay = false;

    export let width = 200;



    // (Listening for the event)
    onMount
    (
        function ()
        {
            // (Getting the value)
            const videoElement = element.querySelector('video');



            // (Setting the property)
            videoElement.srcObject = mediaStream;

            // debug
            //console.debug( 'srcObject', mediaStream );



            /*

            // (Listening for the event)
            videoElement.addEventListener('ended', function () {
                // (Setting the value)
                videoElement.srcObject = null;

                // debug
                console.debug( 'videoEnded' );
            });

            */
        }
    )
    ;

</script>

<div class="video" bind:this={ element }>
    <!-- svelte-ignore a11y-media-has-caption -->
    <video autoplay={ autoplay } width={ width }></video>
</div>

<style>

    .video
    {
        
    }

    .video video
    {
        border-radius: 4px;
    }

</style>