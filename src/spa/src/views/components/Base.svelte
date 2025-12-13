<script>

    import { envs } from '@/envs.js';

    import * as HTTP from '@/modules/HTTP.js';
    import * as IDB from '@/modules/IDB.js';

    import { Client } from '@/modules/Client.ts';

    import { user } from '@/stores/user.js';
    import { idk } from '@/stores/idk.js';

    import { appData } from '@/stores/appData.js';

    import Sidebar from '@/views/components/Sidebar.svelte';
    import Footer from '@/views/components/Footer.svelte';
    import Header from '@/views/components/Header.svelte';
    import Modal from '@/views/components/Modal.svelte';
    import Form from '@/views/components/Form.svelte';

    import NetworkStatus from '@/views/components/NetworkStatus.svelte';

    import { onMount, tick } from 'svelte';
    import { Entity } from '@/modules/Entity';



    let networkStatus;



    const client = new Client( '/api/user' );

    const trustedDeviceEntity = client.entity( 'TrustedDevice' );



    // Returns [Promise:bool]
    async function setDeviceAsTrusted (name)
    {
        // (Getting the value)
        const { code, headers, body } = await trustedDeviceEntity.insert( name );

        if ( code !== 200 )
        {// (Device set as trusted)
            // Returning the value
            return false;
        }



        // Returning the value
        return true;
    }



    // Returns [Promise:bool]
    async function fetchData ()
    {
        // (Getting the values)
        const { code, headers, body } = await client.run( 'User.fetch' );

        if ( code !== 200 )
        {// (Client is not authorized)
            // (Setting the location)
            window.location.href = '/login';

            // Returning the value
            return false;
        }



        // (Getting the value)
        $user = body;



        // (Getting the value)
        $idk = await ( new IDB.Connection( envs.IDB_DATABASE, 'idk' ) ).get( $user['uuid'] );



        // Returning the value
        return true;
    }



    // (Listening for the event)
    onMount
    (
        async function ()
        {
            // (Fetching the data)
            await fetchData();



            // (Waiting for the rendering)
            await tick();



            if ( $user && $user['add_trusted_device'] )
            {// Value is true
                if ( confirm( 'Do you want to set this device as trusted ?' ) )
                {// (Confirmation is OK)
                    // (Opening the modal)
                    openTrustedDeviceModal();
                }
            }



            // (Listening for the events)
            window.addEventListener( 'offline', function () {
                // (Setting the values)
                networkStatus.online  = false;
                networkStatus.message = `<b>OFFLINE</b>`;
            });

            window.addEventListener( 'online', async function () {
                // (Getting the value)
                const idbConnection = new IDB.Connection( envs.IDB_DATABASE, 'request' );



                // (Getting the value)
                const objects = await idbConnection.list( 'values' );

                for ( const object of objects )
                {// Processing each entry
                    // (Sending the request)
                    const response = await ( new HTTP.Request( object['method'], object['headers'], object['body'] ) ).send( object['url'] );



                    // (Deleting the record)
                    idbConnection.remove( object['id'] );
                }



                // (Getting the values)
                networkStatus.online  = true;
                networkStatus.message = '<b>ONLINE</b>' + ( objects.length > 0 ? `:: <b>${ objects.length }</b> requests processed from the queue` : '' );
            });



            // (Setting the value)
            $appData = {};
        }
    )
    ;



    // Returns [void]
    function scrollToTop ()
    {
        // (Getting the value)
        const element = document.querySelector( '#content' );

        if ( !element ) return;



        // (Scrolling to the top)
        element.scrollTo
        (
            {
                'top': 0,
                'left': 0,

                'behavior': 'smooth'
            }
        )
        ;
    }



    let trustedDeviceModal;

    let trustedDeviceForm;

    // Returns [Promise:bool]
    async function onTrustedDeviceFormSubmit ()
    {
        // (Validating the form)
        let result = trustedDeviceForm.validate();

        if ( !result.valid ) return false;



        // (Getting the value)
        const input = result.fetch();



        // (Setting the device as trusted)
        await setDeviceAsTrusted( input['name'] );


        // (Hiding the modal)
        trustedDeviceModal.hide();



        // Returning the value
        return true;
    }



    // Returns [void]
    function openTrustedDeviceModal ()
    {
        // (Resetting the form)
        trustedDeviceForm.reset();

        // (Showing the modal)
        trustedDeviceModal.show();
    }



    $:
        if ( $user )
        {// Value found
            // (Getting the value)
            Entity.requestToken = $user['request_token'];
        }

</script>

{ #if $user }
    <NetworkStatus bind:api={ networkStatus }/>

    <!-- Page Wrapper -->
    <div id="wrapper">
        <Sidebar/>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <Header/>
                <div class="container-fluid">
                    <slot/>
                </div>
            </div>
            <Footer/>
        </div>
    </div>

    <!-- Scroll to Top Button -->
    <!-- svelte-ignore a11y-click-events-have-key-events -->
    <!-- svelte-ignore a11y-missing-attribute -->
    <a class="scroll-to-top rounded" on:click={ scrollToTop }>
        <i class="fas fa-angle-up"></i>
    </a>

    <Modal id="trusted_device_modal" title="Trusted Device" bind:api={ trustedDeviceModal } width="640px">
        <!-- svelte-ignore missing-declaration -->
        <Form id="trusted_device_form" bind:api={ trustedDeviceForm } on:submit={ onTrustedDeviceFormSubmit }>
            <div class="row">
                <div class="col">
                    <label class="d-block">
                        Name (*)
                        <input type="text" class="form-control input form-input" name="name" data-type="text" data-required>
                    </label>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col text-center">
                    <button type="submit" class="btn btn-primary">
                        Save
                    </button>
                </div>
            </div>
        </Form>
    </Modal>
{ /if }

<style>

    :global( html )
    {
        overflow: hidden;
    }

    #content
    {
        position: relative;
    }

    .container-fluid
    {
        padding-top: 10px;
        /*height: 100vh;*/
        width: 100%;
        position: absolute;
        left: 0;
        right: 0;
        top: 70px;
        bottom: 0;
        overflow-y: auto;
    }

</style>