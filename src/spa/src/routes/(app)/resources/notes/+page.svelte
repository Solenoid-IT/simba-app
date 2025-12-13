<svelte:head>
    <title>{ title }</title>
</svelte:head>

<script>

    import * as URL from '@/modules/URL.js';
    import * as HTTP from '@/modules/HTTP.js';
    import * as File from '@/modules/File.js';
    import * as FlexObject from '@/modules/FlexObject.js';
    import * as Time from '@/modules/Time.js';

    import { Client } from '@/modules/Client.ts';
    import { Entity } from '@/modules/Entity.ts';

    import { user } from '@/stores/user.js';
    import { appData } from '@/stores/appData.js';
    import { idbConnections } from '@/stores/idbConnections.js';

    import App from '@/views/App.svelte';
    import Base from '@/views/components/Base.svelte';
    import Table from '@/views/components/Table.svelte';
    import Modal from '@/views/components/Modal.svelte';
    import Form from '@/views/components/Form.svelte';
    import Helper from '@/views/components/Helper.svelte';
    import Switch from '@/views/components/Switch.svelte';
    import Select from '@/views/components/Select.svelte';
    import PasswordField from '@/views/components/PasswordField.svelte';
    import ShareModal from '@/views/components/ShareModal.svelte';

    import { onMount, tick } from 'svelte';
    import { filter } from 'jszip';



    const RESOURCE_ID = 1;



    let resourceType = 'Resources/Note';

    let resourceName = 'Note';

    let elementId;



    let title = '';



    const client = new Client();



    const entity = client.entity( 'Resources/Note' );

    Entity.requestToken = $user['request_token'];



    let records = [];



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
        $appData['paginator'] = body;



        // (Setting the value)
        records = [];

        for ( const record of $appData['paginator']['elements'] )
        {// Processing each entry
            // (Getting the value)
            const r =
            {
                'id':              record['id'],

                'values':
                [
                    {
                        'column':  'owner',
                        'value':   record['ref']['user']['name'],

                        'content': `<a class="resource-object" href="/users?id=${ record['owner'] }" target="_blank">${ record['ref']['user']['name'] }</a>`,

                        'shrink':  true
                    },

                    {
                        'column': 'name',
                        'value':  record['name']
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
                            <button type="button" class="btn btn-sm btn-info ml-2 action-input" data-action="share" title="share">
                                <i class="fa-solid fa-share-nodes"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger ml-2 action-input" data-action="delete" title="remove" ${ record['share_rule'] !== 1 ? 'disabled' : '' }>
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    `
                ,

                'data':
                {
                    'share_rule': record['share_rule']
                },

                'hidden': false
            }
            ;



            // (Appending the value)
            records.push( r );
        }

        // (Getting the value)
        records = records;



        // (Getting the value)
        const data =
        {
            'length': $appData['paginator']['length'],
            'cursor': $appData['paginator']['cursor']
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
            return;
        }



        // (Getting the value)
        const element = FlexObject.compress( body );



        // (Setting the values)
        form.setValues( element );



        // (Getting the value)
        elementId = element['id'];



        // (Showing the modal)
        modal.show();
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



    // Returns [Promise:void]
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

            case 'share':
                // (Opening the modal)
                await shareModal.open( RESOURCE_ID, data.id, records[ data.index ].data['share_rule'] );
            break;

            case 'delete':
                // (Deleting the records)
                await entity.delete( [ data.id ] );
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



    let pageLength;



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



    // Returns [Promise:bool]
    async function onExportCSV ()
    {
        // (Getting the value)
        const input = table.getSelectedIds();



        // (Getting the values)
        const { code, headers, body } = await entity.export( input );

        if ( code !== 200 )
        {// (Request failed)
            // Returning the value
            return false;
        }



        // (Getting the value)
        const filename = headers.get( 'content-disposition' ).split( ';' )[1].split( '=' )[1].slice( 1, -1 );


        // (Downloading the file)
        File.downloadFromBlob( filename, new Blob( [ body ], { 'type': 'text/csv' } ) );



        // Returning the value
        return true;
    }

    // Returns [Promise:bool]
    async function onImportCSV ()
    {
        // (Getting the value)
        const file = ( await File.select( 'text/csv' ) )[0];



        // (Getting the value)
        const fileContent = await File.readText( file );



        // (Getting the value)
        const input = fileContent;



        // (Getting the values)
        const { code, headers, body } = await entity.import( input );

        if ( code !== 200 )
        {// (Request failed)
            // Returning the value
            return false;
        }



        // (Setting the value)
        const errors = [];

        for ( const error of body['errors'] )
        {// Processing each entry
            if ( error === null ) continue;

            // (Appending the value)
            errors.push( error );
        }



        if ( errors.length > 0 )
        {// Value is not empty
            // (Alerting the value)
            alert( errors.join( "\n" ) );
        }



        // (Opening the cursor)
        await table.openCursor();



        // Returning the value
        return true;
    }



    $:
        if ( elementId )
        {// Value found
            // (Setting the URL params)
            URL.setParams( { 'id': elementId } );
        }
        else
        {// Value not found
            if ( elementId === null )
            {// Match OK
                // (Removing the URL params)
                URL.removeParams( [ 'id' ] );   
            }
        }



    let shareModal;



    let aliases =
    {
        'owner': 'ref.user.name'
    }

</script>

<Table
    entityType={ resourceName }
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
        { #if $user['hierarchy'] < 3 }
            <button type="button" class="btn btn-primary btn-sm" data-action="add" title="add" on:click={ () => { entity.displayNewRecordForm() } }>
                <i class="fa-solid fa-plus"></i>
            </button>
        { /if }

        <button type="button" class="btn btn-secondary btn-sm ml-3" title="export (csv)" on:click={ onExportCSV }>
            <i class="fa-solid fa-download"></i>
        </button>

        <button type="button" class="btn btn-secondary btn-sm ml-2" title="import (csv)" on:click={ onImportCSV }>
            <i class="fa-solid fa-upload"></i>
        </button>
    </div>
    <div slot="selection-controls">
        { #if $user['hierarchy'] < 3 }
            <button type="button" class="btn btn-danger btn-sm bulk-action" data-action="delete" title="remove">
                <i class="fa-solid fa-trash"></i>
            </button>
        { /if }
    </div>
</Table>

<Modal title="{ resourceName }" bind:api={ modal } on:close={ () => { elementId = null; } } width="900px">
    <Form bind:api={ form } on:submit={ () => { subject.upsert() } }>
        <input type="hidden" class="input form-input" name="id" data-type="int" value="">

        <div class="row">
            <div class="col">
                <label class="d-block m-0">
                    Name (*)

                    <input type="text" class="form-control input form-input" name="name" data-type="string" data-required>
                </label>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col">
                <label class="d-block m-0">
                    Description

                    <textarea class="form-control input form-input" name="description"></textarea>
                </label>
            </div>
        </div>

        { #if $user['hierarchy'] < 3 }
            <div class="row mt-4">
                <div class="col text-center">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </div>
        { /if }
    </Form>
</Modal>



<ShareModal bind:api={ shareModal }/>