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
    import MonacoEditor from '@/views/components/MonacoEditor.svelte';

    import { user } from '@/stores/user.js';
    import { appData } from '@/stores/appData.js';

    import { onMount, tick } from 'svelte';



    let entityId;



    let title = '';



    const client = new Client();



    const entity = client.entity( 'Trigger' );

    Entity.requestToken = $user['request_token'];



    let records = [];



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
                    },

                    {
                        'column':  'enabled',
                        'value':   record['enabled'] ? 'true' : 'false',

                        'content':
                            `
                                <span class="tag-label tag-label-sm bg-${ record['enabled'] ? 'enabled' : 'disabled' } cursor-pointer action-input" data-action="toggle_enabled" title="toggle ON/OFF">${ record['enabled'] ? 'true' : 'false' }</span>
                            `
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



        // (Getting the values)
        form.setValues( FlexObject.compress( body ) );



        // (Getting the value)
        editorContent = body['request']['content'];

        if ( editor )
        {// Value found
            // (Setting the value)
            editor.setValue( editorContent );
        }



        // (Getting the value)
        entityId = body['id'];



        // (Showing the modal)
        modal.show();
    }

    // Returns [Promise:bool]
    subject.setEnabled = async function (id, enabled)
    {
        // (Getting the value)
        const input =
        {
            'id':      id,
            'enabled': enabled
        }
        ;



        // (Getting the values)
        const { code, headers, body } = await entity.set( 'set_enabled', input );

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

            case 'delete':
                // (Deleting the records)
                await entity.delete( [ data.id ] );
            break;

            case 'toggle_enabled':
                // (Setting the enabled)
                await subject.setEnabled( data.id, data.element.innerText !== 'true' );
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



    // Returns [void]
    function displayNewRecordForm ()
    {
        // (Displaying the form)
        entity.displayNewRecordForm();



        // (Setting the value)
        editorContent = null;



        // (Setting the value)
        editor.setValue( defaultEditorContent );



        // (Setting the properties)
        document.querySelector('.input[name="request.method"]').value   = sampleRequestMethod;
        document.querySelector('.input[name="request.url"]').value      = sampleRequestUrl;
        document.querySelector('.input[name="response_timeout"]').value = 30;
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
    
    
    
    let methodSelect;



    let defaultEditorContent = `User-Agent: SimbaApp/1.0.0\nEvent-Type: { EVENT_TYPE }\n\n{\n\t"event_type": "{ EVENT_TYPE }",\n\t"event_description": "{ EVENT_DESCRIPTION }",\n\t"event_timestamp": "{ EVENT_TIMESTAMP }",\n\t"event_source": "{ EVENT_SOURCE }",\n\t"tenant_id": { TENANT_ID },\n\t"user_id": { USER_ID },\n\t"resource_id": "{ RESOURCE_ID }",\n\t"element_id": { ELEMENT_ID }\n}`;

    let editorContent = null;



    let editor;

    // Returns [void]
    function onEditorReady (event)
    {
        // (Getting the value)
        const api = event.detail;



        // (Getting the value)
        editor = api;



        // (Setting the value)
        editor.setValue( editorContent ?? defaultEditorContent );
    }



    const sampleRequestMethod = 'POST';
    const sampleRequestUrl    = 'https://domain.tld/event?type={ EVENT_TYPE }';



    let pageLength;

    let aliases =
    {
        'owner': 'ref.user.name'
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
    controls
    selectable
    fixedHeader
    paginator
    bind:pageLength={ pageLength }
    aliases={ aliases }
    pageList={ subject.list }
>
    <div slot="fixed-controls">
        { #if $user['hierarchy'] === 1 }
            <button type="button" class="btn btn-primary btn-sm" data-action="add" title="add" on:click={ displayNewRecordForm } disabled={ !editor }>
                <i class="fa-solid fa-plus"></i>
            </button>
        { /if }
    </div>
    <div slot="selection-controls">
        { #if $user['hierarchy'] === 1 }
            <button type="button" class="btn btn-sm btn-danger bulk-action" data-action="delete" title="remove">
                <i class="fa-solid fa-trash"></i>
            </button>
        { /if }
    </div>
</Table>



<Modal title="{ entity.name }" bind:api={ modal } on:close={ () => { entityId = null; } } width="1200px">
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

        <div class="row mt-2">
            <div class="col">
                <label class="d-block m-0">
                    Events
                    <textarea class="form-control input form-input" name="events" placeholder="# sample&#13;&#13;User.insert&#13;*.update&#13;Group.delete&#13;FirewallRule.*" style="min-height: 160px;"></textarea>
                </label>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col">
                <fieldset class="fieldset">
                    <legend>
                        <span class="mr-2">Request (*)</span>

                        <Helper>
                            <div slot="content">
                                The HTTP request to be sent to the target endpoint URL when the trigger is activated
                            </div>
                        </Helper>
                    </legend>

                    <div>
                        <div class="input-group mb-2">
                            <input type="text" class="form-control input form-input" name="request.method" data-type="string" data-required>
                            <input type="text" class="form-control input form-input" name="request.url" data-type="string" data-required style="width: 80%;">
                        </div>

                        <MonacoEditor on:ready={ onEditorReady } name="request.content" height="500px" required/>

                        <div class="row mt-4">
                            <div class="col">
                                <label class="d-block m-0">
                                    Response Timeout (*)

                                    <Helper>
                                        <div slot="content">
                                            How many seconds to wait for the external endpoint response
                                        </div>
                                    </Helper>

                                    <div class="input-group">
                                        <input type="number" class="form-control input form-input" name="response_timeout" data-type="int" min="0" data-required style="width: 94%;">
                                        <span class="form-control text-right">s</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col">
                <!-- svelte-ignore a11y-label-has-associated-control -->
                <label class="d-block m-0">
                    <Switch name="enabled">
                        Enabled
                    </Switch>
                </label>
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

<style>

    .input[name="callback_url"]
    {
        width: 80%;
    }

</style>