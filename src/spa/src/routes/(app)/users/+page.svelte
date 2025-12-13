<svelte:head>
    <title>{ title }</title>
</svelte:head>

<script>

    import * as URL from '@/modules/URL.js';
    import * as FlexObject from '@/modules/FlexObject.js';
    import * as Time from '@/modules/Time.js';

    import { Client } from '@/modules/Client.ts';
    import { Entity } from '@/modules/Entity.ts';

    import Table from '@/views/components/Table.svelte';
    import Modal from '@/views/components/Modal.svelte';
    import Form from '@/views/components/Form.svelte';
    import Helper from '@/views/components/Helper.svelte';
    import Switch from '@/views/components/Switch.svelte';
    import Select from '@/views/components/Select.svelte';

    import { user } from '@/stores/user.js';
    import { appData } from '@/stores/appData.js';

    import { onMount, tick } from 'svelte';



    let entityId;



    let title = '';



    const client = new Client();



    const entity = client.entity( 'User' );

    Entity.requestToken = $user['request_token'];



    let records = [];



    let hierarchyOptions = [];



    // (Setting the value)
    const subject = {};



    // Returns [Promise:object|false]
    subject.list = async function (input)
    {
        // (Getting the values)
        const { code, headers, body } = await entity.list( input );

        if ( code !== 200 )
        {// (Request failed)
            // Returning the value
            return false;
        }



        // (Getting the value)
        $appData = body;



        // (Getting the value)
        //$user['hierarchies'] = $appData['hierarchies'];



        // (Setting the value)
        records = [];

        for ( const record of $appData['elements'] )
        {// Processing each entry
            // (Getting the value)
            const adminButtonsHTML =
                $user['hierarchy'] === 1
                    ?
                `
                    <button type="button" class="btn btn-sm btn-danger ml-2 action-input" data-action="delete" title="remove">
                        <i class="fa-solid fa-trash"></i>
                    </button>

                    <button type="button" class="btn btn-sm btn-danger ml-4 action-input" data-action="reset_account" title="reset account">
                        <i class="fa-solid fa-rotate"></i>
                    </button>
                `
                    :
                ''
            ;



            // (Getting the value)
            const r =
            {
                'id':             record['id'],

                'values':
                [
                    {
                        'column': 'hierarchy',
                        'value':  record['ref']['hierarchy']['name'],

                        'content':
                            `
                                <span class="tag-label tag-label-sm" style="background-color: ${ $user['hierarchies'][ record['hierarchy'] ]['color'] };">
                                    ${ record['ref']['hierarchy']['name'] }
                                </span>
                            `
                    },

                    {
                        'column': 'name',
                        'value':  record['name']
                    },

                    {
                        'column': 'email',
                        'value':  record['email']
                    },

                    {
                        'column': 'birth.name',
                        'value':  record['birth']['name']
                    },

                    {
                        'column': 'birth.surname',
                        'value':  record['birth']['surname']
                    },

                    {
                        'column':  'datetime.insert',
                        'value':   record['datetime']['insert'] ? Time.toLocal( record['datetime']['insert'] ) : ''
                    },

                    {
                        'column':  'datetime.update',
                        'value':   record['datetime']['update'] ? Time.toLocal( record['datetime']['update'] ) : ''
                    }
                ],

                'controls':
                    `
                        <div class="d-flex justify-content-center align-items-center">
                            <button type="button" class="btn btn-sm btn-primary action-input" data-action="view" title="view">
                                <i class="fa-solid fa-up-right-from-square"></i>
                            </button>
                            ${ adminButtonsHTML }
                        </div>
                    `
                ,

                'hidden': false
            }
            ;



            // (Appending the value)
            records.push( r );
        }

        // (Getting the value)
        records = records;



        // (Setting the value)
        hierarchyOptions = [];

        for ( const record of Object.values( $user['hierarchies'] ) )
        {// Processing each entry
            // (Appending the value)
            hierarchyOptions.push
            (
                {
                    'value':   record['id'],
                    'content': `<span class="status-label"><span class="color-box" style="background-color: ${ record['color'] };"></span>${ record['name'] }</span>`
                }
            )
            ;
        }

        // (Getting the value)
        hierarchyOptions = hierarchyOptions;



        // (Getting the value)
        const data =
        {
            'length': $appData['length'],
            'cursor': $appData['cursor']
        }
        ;



        // Returning the value
        return data;
    }

    // Returns [Promise:bool]
    subject.upsert = async function ()
    {
        // (Validating the form)
        const result = form.validate();

        if ( !result.valid ) return false;



        // (Getting the value)
        const input = result.fetch();



        // (Getting the values)
        const { code, headers, body } = await entity.upsert( input );

        if ( code !== 200 )
        {// (Request failed)
            // Returning the value
            return false;
        }



        if ( input['id'] )
        {// (Operation is 'update')
            if ( input['id'] === $user['id'] )
            {// Match OK
                // (Getting the values)
                $user['name']             = input['name'];
                $user['birth']['name']    = input['birth']['name'];
                $user['birth']['surname'] = input['birth']['surname'];
            }
        }
        else
        {// (Operation is 'insert')
            // (Alerting the value)
            alert( `Confirm operation by email "${ $user['email'] }" ...\n\nClick on "OK" after you have confirmed` );

            // (Setting the location)
            window.location.href = '';
        }



        // (Opening the cursor)
        await table.openCursor();



        // Returning the value
        return true;
    }

    // Returns [Promise:void]
    subject.view = async function (id)
    {
        // (Resetting the form)
        form.reset();



        // (Getting the values)
        const { code, headers, body } = await entity.find( id );

        if ( code !== 200 )
        {// (Request failed)
            // Returning the value
            return false;
        }



        // (Setting the values)
        form.setValues( FlexObject.compress( body ) );



        // (Getting the value)
        entityId = body['id'];



        // (Setting the values)
        changeEmailForm.setValues( { 'email': body['email'] } );



        // (Showing the modal)
        modal.show();
    }



    // Returns [Promise:bool]
    subject.resetAccount = async function (id)
    {
        if ( !confirm( 'Are you sure to reset the selected user account ?\n\nUser password and his sessions will be removed.' ) ) return;



        // (Getting the values)
        const { code, headers, body } = await client.run( `${ entity.name }.reset_account`, id, new Headers( { 'Request-Token': Entity.requestToken } ) );

        if ( code !== 200 )
        {// (Request failed)
            // Returning the value
            return false;
        }



        // Returning the value
        return true;
    }



    // Returns [Promise:void]
    async function viewRecord (id)
    {
        // (Viewing the record)
        await subject.view( id );
    }



    // (Listening for the event)
    onMount
    (
        async function ()
        {
            // (Opening the cursor)
            await table.openCursor();



            // (Waiting for the rendering)
            await tick();



            if ( URL.hasParam( 'id' ) )
            {// Value found
                // (Viewing the resource)
                await viewRecord( URL.getParam( 'id' ) );
            }
        }
    )
    ;



    let table;

    $:
        if ( table )
        {// Value found
            // (Setting the table)
            entity.setTable( table );
        }



    // Returns [Promise:bool]
    async function onEntryAction (event)
    {
        // (Getting the value)
        const data = event.detail;

        switch ( data.type )
        {
            case 'view':
                // (Viewing the record)
                await subject.view( data.id );
            break;

            case 'delete':
                // (Deleting the records)
                await entity.delete( [ data.id ] );
            break;

            case 'reset_account':
                // (Resetting the record)
                await subject.resetAccount( data.id );
            break;
        }
    }

    // Returns [Promise:void]
    async function onBulkAction (event)
    {
        // (Getting the value)
        const data = event.detail;

        switch ( data.type )
        {
            case 'delete':
                // (Getting the value)
                const ids = data.selection.map( function (index) { return records[ index ].id; } );

                // (Deleting the records)
                await entity.delete( ids );
            break;
        }
    }



    let modal;

    $:
        if ( modal )
        {// Value found
            // (Setting the modal)
            entity.setModal( modal );
        }
    


    let form;

    $:
        if ( form )
        {// Value found
            // (Setting the form)
            entity.setForm( form );
        }



    $:
        if ( entityId )
        {// Value found
            // (Setting the URL params)
            URL.setParams( { 'id': entityId } );
        }
        else
        {// Value not found
            if ( entityId === null )
            {// Match OK
                // (Removing the URL params)
                URL.removeParams( [ 'id' ] );   
            }
        }



    let pageLength;

    let aliases =
    {
        'hierarchy': 'ref.hierarchy.name'
    }
    ;



    // Returns [void]
    function onAddEntry ()
    {
        // (Displaying the new record form)
        entity.displayNewRecordForm();

        // (Setting the value)
        entityId = null;
    }



    let changeEmailForm;

    // Returns [Promise:bool]
    async function onChangeEmailFormSubmit ()
    {
        // (Validating the form)
        let result = changeEmailForm.validate();

        if ( !result.valid ) return false;



        if ( !confirm( 'Are you sure to change the email ?' ) ) return false;



        // (Getting the value)
        const input =
        {
            'id':    entityId,
            'email': result.fetch()['email']
        }
        ;



        // (Getting the values)
        const { code, headers, body } = await entity.set( 'set_email', input );

        if ( code !== 200 )
        {// (Request failed)
            // Returning the value
            return false;
        }



        // (Alerting the value)
        alert(`Confirm operation by email "${ $user['email'] }" ...\n\nClick on "OK" after you have confirmed`);

        // (Setting the location)
        window.location.href = '';



        // Returning the value
        return true;
    }

</script>

<Table
    entityType={ entity.name }
    bind:title={ title }
    bind:api={ table }
    bind:records={ records }
    on:entry-action={ onEntryAction }
    on:bulk-action={ onBulkAction }
    controls
    selectable
    fixedHeader
    paginator
    bind:pageLength={ pageLength }
    pageList={ subject.list }
    aliases={ aliases }
>
    <div slot="fixed-controls">
        { #if $user['hierarchy'] === 1 }
            <button type="button" class="btn btn-primary btn-sm" data-action="add" title="add" on:click={ onAddEntry }>
                <i class="fa-solid fa-plus"></i>
            </button>
        { /if }
    </div>
    <div slot="selection-controls">
        { #if $user['hierarchy'] === 1 }
            <button type="button" class="btn btn-danger btn-sm bulk-action" data-action="delete" title="remove">
                <i class="fa-solid fa-trash"></i>
            </button>
        { /if }
    </div>
</Table>

<Modal title="{ entity.name }" bind:api={ modal } on:close={ () => { entityId = null; } }>
    <Form bind:api={ form } on:submit={ () => { subject.upsert() } }>
        <input type="hidden" class="input form-input" name="id" data-type="int" value="">

        <div class="row">
            <div class="col">
                { #if !entityId }
                    <div class="alert alert-warning font-size-12 font-weight-600 m-0 p-1 pl-2 pr-2 mb-3" role="alert">
                        <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                        User creation <b>needs</b> email confirmation for both of parts : Current logged in and new one user emails
                    </div>
                { /if }
            </div>
        </div>

        <div class="row mt-2">
            <div class="col">
                Hierarchy (*)

                <Helper>
                    <div slot="content">
                        <div class="row">
                            <div class="col">
                                <table class="hierarchy-table">
                                    <tbody>
                                        <tr>
                                            <td><b>F</b></td>
                                            <td>Find</td>
                                        </tr>
                                        <tr>
                                            <td><b>L</b></td>
                                            <td>List</td>
                                        </tr>
                                        <tr>
                                            <td><b>U</b></td>
                                            <td>Update</td>
                                        </tr>
                                        <tr>
                                            <td><b>I</b></td>
                                            <td>Insert</td>
                                        </tr>
                                        <tr>
                                            <td><b>D</b></td>
                                            <td>Delete</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col">
                            <table class="hierarchy-table">
                                <tbody>
                                    { #if $appData && $appData['hierarchies'] }
                                        { #each Object.values( $appData['hierarchies'] ) as hierarchy }
                                            <tr>
                                                <td>{ hierarchy['id'] }</td>
                                                <td><span class="tag-label d-block text-center" style="background-color: { hierarchy['color'] };">{ hierarchy['name'] }</span></td>
                                                <td>{ @html hierarchy['description'] }</td>
                                            </tr>
                                        { /each }
                                    { /if }
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                </Helper>

                <Select input="hierarchy" bind:options={ hierarchyOptions } required/>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col">
                <label class="d-block m-0">
                    Name (*)
                    <input type="text" class="form-control input form-input" name="name" data-type="string" data-required>
                </label>
            </div>
        </div>

        { #if !entityId }
            <div class="row mt-2">
                <div class="col">
                    <label class="d-block m-0">
                        Email (*)
                        <input type="text" class="form-control input form-input" name="email" data-type="string" data-required data-regex="^[^\@]+\@[^\@]+$">
                    </label>
                </div>
            </div>
        { /if }

        <div class="row mt-2">
            <div class="col">
                <fieldset class="fieldset">
                    <legend class="legend">Birth</legend>

                    <div class="row">
                        <div class="col">
                            <label class="d-block m-0">
                                Name
                                <input type="text" class="form-control input form-input" name="birth.name" data-type="string">
                            </label>
                        </div>
                        <div class="col">
                            <label class="d-block m-0">
                                Surname
                                <input type="text" class="form-control input form-input" name="birth.surname" data-type="string">
                            </label>
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>

        { #if $user['hierarchy'] === 1 }
            <div class="row mt-4">
                <div class="col text-center">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </div>
        { /if }
    </Form>

    <br>

    <Form bind:api={ changeEmailForm } on:submit={ onChangeEmailFormSubmit }>
        <fieldset class="fieldset" class:d-none={ !entityId }>
            <legend>Email</legend>
            <div class="row">
                <div class="col">
                    <div class="alert alert-warning font-size-12 font-weight-600 m-0 p-1 pl-2 pr-2 mb-3" role="alert">
                        <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                        Email change <b>needs</b> email confirmation for both of parts : Current user email and the new one
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col d-flex align-items-center">
                    <input type="text" class="form-control input form-input" name="email" data-required data-regex="^[^\@]+\@[^\@]+$">

                    <button type="submit" class="btn btn-primary ml-3">Save</button>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col">
                    <span class="color-warning"></span>
                </div>
            </div>
        </fieldset>
    </Form>
</Modal>

<style>

    .hierarchy-table
    {
        font-size: 12px;
        border-collapse: collapse;
    }

    .hierarchy-table td
    {
        padding: 2px;
    }

</style>