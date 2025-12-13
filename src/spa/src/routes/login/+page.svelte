<svelte:head>
    <title>Login</title>
</svelte:head>

<script>

    import { envs } from '@/envs.js';

    import { idk } from '@/stores/idk.js';

    import { goto } from '$app/navigation';

    import * as IDB from '@/modules/IDB.js';

    import { Client } from '@/modules/Client.ts';

    import App from '@/views/App.svelte';
    import Form from '@/views/components/Form.svelte';
    import PasswordField from '@/views/components/PasswordField.svelte';
    import Modal from '@/views/components/Modal.svelte';
    import Table from '@/views/components/Table.svelte';




    let loginErrMsg = '';
    let loginWrnMsg = '';



    let loginForm;



    const client = new Client( '/api/public' );



    const userEntity = client.entity( 'User' );



    // Returns [Promise:bool]
    async function onLoginFormSubmit ()
    {
        // (Setting the values)
        loginErrMsg = '';
        loginWrnMsg = '';



        // (Validating the form)
        let result = loginForm.validate();

        if ( !result.valid ) return false;



        // (Getting the value)
        const input = result.fetch();



        // (Getting the values)
        const { code, headers, body } = await client.run( `${ userEntity.name }.login`, input );

        if ( code !== 200 )
        {// (Request failed)
            // Returning the value
            return false;
        }



        if ( body && body['location'] )
        {// Value found
            // (Moving to the URL)
            goto( body['location'] );



            // Returning the value
            return true;
        }



        // (Setting the value)
        loginForm.disabled = true;



        // (Setting the value)
        loginWrnMsg = 'Confirm operation by email ...';



        // (Doing the login via authorization)
        await loginViaAuthorization();



        // Returning the value
        return true;
    }



    let idkModal;



    const IDK =
    {
        'remove': async function ( ids )
        {
            if ( !confirm( 'Are you sure to remove the selected entries from the local memory ?' ) ) return;

            for ( const id of ids )
            {// Processing the value
                // (Removing the value)
                await ( new IDB.Connection( envs.IDB_DATABASE, 'idk' ) ).remove( id );
            }
        }
    }
    ;



    let idkTable;
    let idkTableRecords = [];

    // Returns [Promise:bool]
    async function onIDKTableEntryAction (event)
    {
        // (Getting the value)
        const data = event.detail;

        switch ( data.type )
        {
            case 'login':
                // (Hiding the modal)
                idkModal.hide();



                // (Getting the value)
                const idk = await ( new IDB.Connection( envs.IDB_DATABASE, 'idk' ) ).get( idkTableRecords[ data.index ].id );



                // (Login via IDK)
                await loginViaIDK( idk );
            break;

            case 'delete':
                // (Removing the idk)
                await IDK.remove( [ idkTableRecords[ data.index ].id ] );

                // (Opening the IDK Manager)
                await openIDKManager();
            break;
        }
    }

    // Returns [Promise:bool]
    async function onIDKTableBulkRemove ()
    {
        // (Setting the value)
        const ids = [];

        for ( const index of idkTable.fetchSelectedRecords() )
        {// Processing each entry
            // (Appending the value)
            ids.push( idkTableRecords[ index ].id );
        }



        // (Removing the IDKs)
        await IDK.remove( ids );



        // (Opening the IDK Manager)
        await openIDKManager();



        // Returning the value
        return true;
    }

    // Returns [Promise:bool]
    async function onImportIDK ()
    {
        // (Selecting the files)
        const file = ( await Solenoid.File.select( '.idk' ) )[0];



        // (Getting the value)
        const idk = await Solenoid.File.read( file );



        // (Getting the value)
        const userId = parseInt( idk.split( "\n\n" )[0] );



        // (Setting the value)
        await ( new IDB.Connection( envs.IDB_DATABASE, 'idk' ) ).set( userId, idk );



        // (Opening the IDK Manager)
        await openIDKManager();



        // Returning the value
        return true;
    }



    let idks = [];

    // Returns [Promise:bool]
    async function openIDKManager ()
    {
        // (Setting the value)
        const users = [];



        // (Getting the value)
        const results = await ( new IDB.Connection( envs.IDB_DATABASE, 'idk' ) ).list();

        for ( const k in results )
        {// Processing the value
            // (Appending the value)
            users.push( k );
        }



        // (Getting the values)
        const { code, headers, body } = await client.run( `${ userEntity.name }.fetch_name`, users );

        if ( body === false )
        {// (Request failed)
            // Returning the value
            return false;
        }



        // (Getting the value)
        idks = body;



        // (Setting the value)
        idkTableRecords = [];

        for ( const record of Object.values( idks ) )
        {// Processing the value
            // (Getting the value)
            const r =
            {
                'id':             record['uuid'],

                'values':
                [
                    {
                        'column': 'tenant',
                        'value':  record['tenant']
                    },

                    {
                        'column': 'user',
                        'value':  record['name']
                    },
                ],

                'controls':
                    `
                        <div class="d-flex justify-content-center align-items-center">
                            <button type="button" class="btn btn-sm btn-primary action-input" data-action="login" title="login">
                                <i class="fa-solid fa-right-to-bracket"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger action-input ml-2" data-action="delete" title="remove">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    `
                ,

                'hidden': false
            }
            ;

            // (Appending the value)
            idkTableRecords.push( r );
        }

        // (Getting the value)
        idkTableRecords = idkTableRecords;



        // (Showing the modal)
        idkModal.show();



        // Returning the value
        return true;
    }



    // Returns [Promise:bool]
    async function loginViaIDK (idk)
    {
        // (Getting the value)
        const { code, headers, body } = await client.run( `${ userEntity.name }.login_via_idk`, idk );

        if ( code !== 200 )
        {// (Request failed)
            // Returning the value
            return false;
        }



        if ( body && body['location'] )
        {// Value found
            // (Moving to the URL)
            goto( body['location'] );



            // Returning the value
            return true;
        }



        // Returning the value
        return true;
    }



    let quickAccessModal;
    let quickAccessForm;



    let quickAccessFormMsg = '';



    // Returns [Promise:bool]
    async function loginViaAuthorization ()
    {
        // (Getting the values)
        const { code, headers, body } = await client.run( `${ userEntity.name }.login_via_authorization` );

        if ( code !== 200 )
        {// (Request failed)
            // Returning the value
            return false;
        }



        if ( body && body['location'] )
        {// Value found
            // (Moving to the URL)
            goto( body['location'] );
        }



        // Returning the value
        return true;
    }

    // Returns [Promise:bool]
    async function onQuickAccessFormSubmit ()
    {
        // (Validating the form)
        const result = quickAccessForm.validate();

        if ( !result.valid ) return false;



        // (Setting the value)
        quickAccessFormMsg = '';



        // (Getting the value)
        const input = result.fetch();



        // (Getting the values)
        const { code, headers, body } = await client.run( `${ userEntity.name }.request_quick_access`, input['email'] );

        if ( code !== 200 )
        {// (Request failed)
            // Returning the value
            return false;
        }



        // (Setting the value)
        quickAccessForm.disabled = true;



        // (Setting the value)
        quickAccessFormMsg = 'Confirm operation by email ...';



        // (Doing the login via authorization)
        await loginViaAuthorization();



        // Returning the value
        return true;
    }



    $:
        if ( quickAccessModal )
        {// Value found
            // (Getting the value)
            const urlParams = new URLSearchParams( window.location.search );

            if ( urlParams.has( 'quick_access' ) )
            {// Value found
                // (Showing the modal)
                quickAccessModal.show();
            }
        }

</script>

<App>
    <div class="container">

        <!-- Outer Row -->
        <div class="row justify-content-center m-0">

            <!--<div class="col-xl-10 col-lg-12 col-md-9">-->
            <div class="col">

                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        
                        <div class="p-5">
                            <img class="mb-3" id="login_logo" src="/assets/logo.jpg" alt="">

                            <Form id="login_form" on:submit={ onLoginFormSubmit } bind:api={ loginForm }>
                                <div class="form-group">
                                    <input type="text" class="form-control input form-input" name="login" placeholder="Login" data-required>
                                </div>
                                <div class="form-group">
                                    <PasswordField name="password" placeholder="Password" required/>
                                </div>

                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fa-solid fa-right-to-bracket"></i>
                                </button>

                                <div class="row mt-3">
                                    <div class="col text-center">
                                        <span class="color-danger">{ loginErrMsg }</span>
                                        <span class="color-warning">{ loginWrnMsg }</span>
                                    </div>
                                </div>
                            </Form>
                            <hr>
                            <div class="row mt-2">
                                <div class="col text-center">
                                    <button class="btn btn-primary ml-2" on:click={ openIDKManager } title="open IDK Manager">
                                        <i class="fa-solid fa-key"></i>
                                    </button>
                                </div>
                                <div class="col text-center">
                                    <button class="btn btn-danger" on:click={ quickAccessModal.show } title="quick access">
                                        <i class="fa-solid fa-lock-open"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

        </div>

    </div>

    <Modal id="quick_access_modal" title="Quick Access" urlkey="quick_access" bind:api={ quickAccessModal }>
        <Form id="quick_access_form" bind:api={ quickAccessForm } on:submit={ onQuickAccessFormSubmit }>
            <div class="row">
                <div class="col">
                    <label class="m-0 d-block">
                        Email
                        <input type="text" class="form-control input form-input" name="email" data-required>
                    </label>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col text-center">
                    <span class="color-warning">{ quickAccessFormMsg }</span>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col text-center">
                    <button type="submit" class="btn btn-primary">Request</button>
                </div>
            </div>
        </Form>
    </Modal>

    <Modal id="idk_modal" title="IDK Manager" urlkey="idk" bind:api={ idkModal }>
        <Table controls selectable entityType="IDK" bind:api={ idkTable } bind:records={ idkTableRecords } on:entry-action={ onIDKTableEntryAction }>
            <div slot="fixed-controls">
                <button type="button" class="btn btn-primary btn-sm" title="add" on:click={ onImportIDK }>
                    <i class="fa-solid fa-plus"></i>
                </button>
            </div>
            <div slot="selection-controls">
                <button type="button" class="btn btn-sm btn-danger" title="remove" on:click={ onIDKTableBulkRemove }>
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </Table>
    </Modal>
</App>

<style>

    :global( body )
    {
        background-color: #c2c2c2;
    }

    .container
    {
        /*width: 768px;*/
        height: 100vh;
        /*max-width: 768px !important;*/
        display: flex;
        align-items: center;
    }

    .container > .row
    {
        width: 100%;
    }

    .container > .row > .col
    {
        display: flex;
        justify-content: center;
    }

    .card
    {
        width: 576px !important;
    }

    #login_logo
    {
        margin: 0 auto;
        padding: 20px;
        height: 140px;
        display: table;
        object-fit: cover;
        border-radius: 32px;
    }

</style>