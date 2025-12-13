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



    const entity = client.entity( 'FirewallRule' );

    Entity.requestToken = $user['request_token'];



    let records = [];



    let actionOptions =
    [
        {
            'value':   false,
            'content': `<span class="status-label"><span class="color-box bg-danger"></span>deny</span>`
        },

        {
            'value':   true,
            'content': `<span class="status-label"><span class="color-box bg-success"></span>allow</span>`
        },
    ]
    ;



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
                        'column':  'owner',
                        'value':   record['ref']['user']['name'],

                        'content': `<a class="resource-object" href="/users?id=${ record['owner'] }" target="_blank">${ record['ref']['user']['name'] }</a>`
                    },

                    {
                        'column': 'range',
                        'value':  record['range']
                    },

                    {
                        'column':  'action',
                        'value':   record['allowed'] ? 'allow' : 'deny',

                        'content':
                            `
                                <span class="status-label">
                                    <span class="color-box ${ record['allowed'] ? 'bg-success' : 'bg-danger' }"></span>
                                    ${ record['allowed'] ? 'allow' : 'deny' }
                                </span>
                            `
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



        // (Getting the value)
        const data =
        {
            'length':   $appData['length'],
            'cursor':   $appData['cursor']
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



        // (Setting the values)
        form.setValues( body );



        // (Getting the value)
        entityId = body['id'];



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
                // (Viewing the record)
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
        'owner': 'ref.user.name',
        'action': 'allowed'
    }
    ;

</script>

<Table
    entityType={ entity.name }
    bind:title={ title }
    bind:api={ table }
    bind:records={ records }
    on:entry-action={ onEntryAction }
    on:bulk-action={ onBulkAction }
    selectable
    fixedHeader
    controls
    paginator
    bind:pageLength={ pageLength }
    aliases={ aliases }
    pageList={ subject.list }
>
    <div slot="fixed-controls">
        { #if $user['hierarchy'] === 1 }
            <button type="button" class="btn btn-primary btn-sm" data-action="add" title="add" on:click={ () => { entity.displayNewRecordForm() } }>
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

<Modal title="{ entity.name }" bind:api={ modal } on:close={ () => { entityId = null; } } width="640px">
    <Form bind:api={ form } on:submit={ () => { subject.upsert() } }>
        <input type="hidden" class="input form-input" name="id" data-type="int" value="">

        <div class="row">
            <div class="col">
                <label class="d-block m-0">
                    Range (*)
                    <input type="text" class="form-control input form-input" name="range" placeholder="Ex. 0.0.0.0/0 or 1.2.3.4" data-type="string" data-regex="^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+(\/[0-9]+)?$" data-required>
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

        <div class="row mt-4">
            <div class="col">
                Action (*)
                <Select input="allowed" bind:options={ actionOptions } required/>
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
</Modal>