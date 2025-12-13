<script>

    import { createEventDispatcher } from 'svelte';
    import { onMount } from 'svelte';

    // Definisce le prop del componente
    /** @type {number} La tonalità HSL corrente (0-360) */
    export let hue = 0;

    /** @type {number} La saturazione HSL (0-100) */
    export let saturation = 100;

    /** @type {number} La luminosità HSL (0-100) */
    export let lightness = 50;



    export let saturationControls = false;
    export let lightnessControls  = false;
    


    export let input;
    export let required = false;



    let colorBoxVisible = false;



    const dispatch = createEventDispatcher();



    $: hslColor = `hsl(${hue}, ${saturation}%, ${lightness}%)`;

    $: hexColor = hslToHex( hue, saturation, lightness );



    // Returns [void]
    function handleHueChange ()
    {
        dispatch
        (
            'change',
            {
                hue,
                saturation,
                lightness,

                'hsl': hslColor
            }
        )
        ;
    }



    // Returns [string]
    function hslToHex (h, s, l)
    {
        // (Getting the values)
        s /= 100;
        l /= 100;



        // (Getting the values)
        let c = (1 - Math.abs(2 * l - 1)) * s,
            x = c * (1 - Math.abs(((h / 60) % 2) - 1)),
            m = l - c / 2,
            r = 0,
            g = 0,
            b = 0
        ;



        if ( 0 <= h && h < 60 )
        {
            r = c; g = x; b = 0;
        }
        else
        if ( 60 <= h && h < 120 )
        {
            r = x; g = c; b = 0;
        }
        else
        if ( 120 <= h && h < 180 )
        {
            r = 0; g = c; b = x;
        }
        else
        if ( 180 <= h && h < 240 )
        {
            r = 0; g = x; b = c;
        }
        else
        if ( 240 <= h && h < 300 )
        {
            r = x; g = 0; b = c;
        }
        else
        if ( 300 <= h && h < 360 )
        {
            r = c; g = 0; b = x;
        }



        // (Getting the values)
        r = Math.round( ( r + m ) * 255 );
        g = Math.round( ( g + m ) * 255 );
        b = Math.round( ( b + m ) * 255 );



        // Returns [string]
        const componentToHex = function (c)
        {
            // (Getting the value)
            const hex = c.toString( 16 );

            // Returning the value
            return hex.length === 1 ? '0' + hex : hex;
        }
        ;



        // Returning the value
        return ( "#" + componentToHex( r ) + componentToHex( g ) + componentToHex( b ) ).toUpperCase();
    }

    // Returns [object]
    function hexToHsl (hex)
    {
        // 1. Pulizia e conversione in RGB
        // Rimuove il '#' iniziale, espande i formati brevi (es. #F00 -> #FF0000)
        let r = 0, g = 0, b = 0;

        if (hex.startsWith('#')) {
            hex = hex.slice(1);
        }

        if (hex.length === 3) {
            r = parseInt(hex[0] + hex[0], 16);
            g = parseInt(hex[1] + hex[1], 16);
            b = parseInt(hex[2] + hex[2], 16);
        } else if (hex.length === 6) {
            r = parseInt(hex.substring(0, 2), 16);
            g = parseInt(hex.substring(2, 4), 16);
            b = parseInt(hex.substring(4, 6), 16);
        } else {
            // Ritorna valori neutri in caso di formato non valido
            return { h: 0, s: 0, l: 0 };
        }

        // Normalizza i componenti RGB in un intervallo [0, 1]
        r /= 255;
        g /= 255;
        b /= 255;

        // 2. Calcolo HSL
        const max = Math.max(r, g, b);
        const min = Math.min(r, g, b);
        let h, s, l = (max + min) / 2;

        if (max === min) {
            // Grigi o neri/bianchi
            h = s = 0; 
        } else {
            const d = max - min;
            s = l > 0.5 ? d / (2 - max - min) : d / (max + min);

            switch (max) {
                case r: h = (g - b) / d + (g < b ? 6 : 0); break;
                case g: h = (b - r) / d + 2; break;
                case b: h = (r - g) / d + 4; break;
            }

            h /= 6;
        }



        // (Getting the value)
        const object =
        {
            'h': Math.round( h * 360 ),
            's': Math.round( s * 100 ),
            'l': Math.round( l * 100 )
        }
        ;



        // Returning the value
        return object;
    }



    // Returns [void]
    function copyColor ()
    {
        // (Writing to the clipboard)
        navigator.clipboard.writeText( hslToHex( hue, saturation, lightness ) );
    }



    // Returns [void]
    function toggleColorBox ()
    {
        // (Getting the value)
        colorBoxVisible = !colorBoxVisible;

        // (Setting the value)
        element.api.edit = true;
    }



    let element;

    $:
        if ( element )
        {// Value found
            // (Setting the value)
            element.api = {};



            // Returns [string]
            element.api.getValue = function ()
            {
                // Returning the value
                return hslToHex( hue, saturation, lightness );
            }

            // Returns [void]
            element.api.setValue = function (value)
            {
                // (Getting the values)
                const { h, s, l } = hexToHsl( value );

                // (Getting the values)
                hue        = h;
                saturation = s;
                lightness  = l;
            }



            // Returns [void]
            element.api.reset = function ()
            {
                // (Setting the values)
                hue        = 0;
                saturation = 100;
                lightness  = 50;



                // (Setting the value)
                colorBoxVisible = false;
            }
        }

</script>



<div class="color-textfield" class:form-widget={ input !== null } data-input={ input } data-required={ required } bind:this={ element }>
    <div class="color-picker-input">
        <div class="input-group">
            <div class="form-control input">{ hexColor }</div>
            <div class="input-group-append">
                <!-- svelte-ignore a11y-click-events-have-key-events -->
                <span class="form-control input-group-text cursor-pointer" title="show/hide color-box" on:click={ toggleColorBox }>
                    <div class="color-box" style="background-color: {hslColor};"></div>
                </span>
            </div>
        </div>
    </div>



    { #if colorBoxVisible }
        <div class="color-picker-container">
            <!-- svelte-ignore a11y-click-events-have-key-events -->
            <div class="color-display" style="background-color: {hslColor};" on:click={ copyColor } title="copy to clipboard"></div>
            
            <label for="hue-slider">Hue (H): {hue}°</label>
            <input
                type="range"
                id="hue-slider"
                min="0"
                max="360"
                step="1"
                bind:value={hue}
                on:input={handleHueChange}
                class="hue-slider"
                style="--saturation: {saturation}%; --lightness: {lightness}%;"
            />
            
            { #if saturationControls }
                <div class="row">
                    <div class="col">
                        <label class="d-block">
                            Saturation (S)

                            <div class="input-group">
                                <input type="number" class="form-control form-control-sm" min="0" max="100" bind:value={ saturation } on:input={ handleHueChange } />
                                <div class="input-group-append">
                                    <span class="form-control form-control-sm input-group-text" style="font-size: .875rem;">%</span>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
            { /if }

            { #if lightnessControls }
                <div class="row">
                    <div class="col">
                        <label class="d-block">
                            Lightness (L)
                            <div class="input-group">
                                <input type="number" class="form-control form-control-sm" min="0" max="100" bind:value={ lightness } on:input={ handleHueChange } />
                                <div class="input-group-append">
                                    <span class="form-control form-control-sm input-group-text" style="font-size: .875rem;">%</span>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
            { /if }
        </div>
    { /if }
</div>



<style>
    /* 1. Stile Generale */
    .color-picker-container
    {
        margin: 20px auto;
        padding: 20px;
        background-color: var( --simba-dark-card-body );
        border: 1px solid var( --simba-dark-border );
        box-shadow: 1px 1px 5px rgba(0,0,0,.2);
        border-radius: 4px;
        max-width: 400px;
    }

    /* 2. Visualizzazione del Colore */
    .color-display
    {
        height: 50px;
        border: 1px solid var( --simba-dark-border );
        border-radius: 4px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #000; /* Testo predefinito */
        text-shadow: 1px 1px 2px #fff, -1px -1px 2px #fff; /* Per visibilità su colori scuri */
        font-weight: bold;
        cursor: pointer;
    }

    /* 3. Stile della Barra di Tonalità (Hue) */
    .hue-slider
    {
        width: 100%;
        height: 25px;
        margin-top: 5px;
        margin-bottom: 15px;
        border-radius: 4px;
        background: linear-gradient(
            to right,
            hsl(0, var(--saturation), var(--lightness)),
            hsl(60, var(--saturation), var(--lightness)),
            hsl(120, var(--saturation), var(--lightness)),
            hsl(180, var(--saturation), var(--lightness)),
            hsl(240, var(--saturation), var(--lightness)),
            hsl(300, var(--saturation), var(--lightness)),
            hsl(360, var(--saturation), var(--lightness))
        );
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
        appearance: none;
        -webkit-appearance: none;
    }

    /* Nasconde il "track" del range input su Firefox */
    .hue-slider::-moz-range-track
    {
        background: transparent;
    }

    /* Stile per il "thumb" (il cerchietto/cursore) */
    .hue-slider::-webkit-slider-thumb
    {
        -webkit-appearance: none;
        appearance: none;
        width: 10px;
        height: 20px;
        border-radius: 4px;
        background-color: #ffffff;
        border: 1px solid var( --simba-dark-border );
        cursor: pointer;
    }

    .hue-slider::-moz-range-thumb
    {
        width: 10px;
        height: 20px;
        border-radius: 4px;
        background-color: #ffffff;
        border: 1px solid var( --simba-dark-border );
        cursor: pointer;
    }
</style>