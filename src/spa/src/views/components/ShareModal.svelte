<script>

    import { Client } from '@/modules/Client.ts';
    import { Entity } from '@/modules/Entity.ts';

    import { user } from '@/stores/user.js';
    import { appData } from '@/stores/appData.js';

    import Modal from '@/views/components/Modal.svelte';
    import Form from '@/views/components/Form.svelte';
    import Table from '@/views/components/Table.svelte';



    export let api = {};

    api.resourceId  = null;
    api.elementId   = null;
    api.shareRuleId = null;



    let resourceOwner;



    const client = new Client();



    const userShareRuleEntity  = client.entity( 'UserShareRule' );
    const groupShareRuleEntity = client.entity( 'GroupShareRule' );

    Entity.requestToken = $user['request_token'];



    // Returns [Promise:void]
    api.open = async function (resourceId, elementId, shareRuleId)
    {
        // (Getting the values)
        api.resourceId  = resourceId;
        api.elementId   = elementId;
        api.shareRuleId = shareRuleId;



        // (Getting the value)
        const input =
        {
            'resource': api.resourceId,
            'element':  api.elementId
        }
        ;



        // (Getting the value)
        const { code, headers, body} = await client.run( 'Resource.get_share_info', input );

        if ( code !== 200 )
        {// (Request failed)
            // Returning the value
            return;
        }



        // (Getting the value)
        resourceOwner = body['owner'];



        // (Setting the value)
        userRuleRecords = [];

        for ( const record of body['user_rules'] )
        {// Processing each entry
            // (Getting the value)
            const r =
            {
                'id':             record['id'],

                'values':
                [
                    {
                        'column': 'user',
                        'value':  $user['users'][ record['user'] ]['name'],

                        'content':
                            `
                                <a href="/users?id=${ record['user'] }" target="_blank">${ $user['users'][ record['user'] ]['name'] }</a>
                            `
                    },

                    {
                        'column': 'share_rule',
                        'value':  $user['share_rules'][ record['share_rule'] ]['name']
                    },
                ],

                'controls':
                    `
                        <div class="d-flex justify-content-center align-items-center">
                            <button type="button" class="btn btn-sm btn-primary action-input" data-action="view" title="view" ${ api.shareRuleId !== 1 ? 'disabled' : '' }>
                                <i class="fa-solid fa-up-right-from-square"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger ml-2 action-input" data-action="delete" title="remove" ${ api.shareRuleId !== 1 ? 'disabled' : '' }>
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    `
                ,

                'hidden': false
            }
            ;



            // (Appending the value)
            userRuleRecords.push( r );
        }

        // (Getting the value)
        userRuleRecords = userRuleRecords;



        // (Setting the value)
        groupRuleRecords = [];

        for ( const record of body['group_rules'] )
        {// Processing each entry
            // (Getting the value)
            const r =
            {
                'id':             record['id'],

                'values':
                [
                    {
                        'column': 'group',
                        'value':  $user['groups'][ record['group'] ]['name'],

                        'content':
                            `
                                <a href="/groups?id=${ record['group'] }" target="_blank">${ $user['groups'][ record['group'] ]['name'] }</a>
                            `
                    },

                    {
                        'column': 'share_rule',
                        'value':  $user['share_rules'][ record['share_rule'] ]['name']
                    },
                ],

                'controls':
                    `
                        <div class="d-flex justify-content-center align-items-center">
                            <button type="button" class="btn btn-sm btn-primary action-input" data-action="view" title="view" ${ api.shareRuleId !== 1 ? 'disabled' : '' }>
                                <i class="fa-solid fa-up-right-from-square"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger ml-2 action-input" data-action="delete" title="remove" ${ api.shareRuleId !== 1 ? 'disabled' : '' }>
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    `
                ,

                'hidden': false
            }
            ;



            // (Appending the value)
            groupRuleRecords.push( r );
        }

        // (Getting the value)
        groupRuleRecords = groupRuleRecords;



        // (Showing the modal)
        modal.show();
    }
    ;



    let modal;
    let form;



    let userRuleModal;
    let userRuleForm;

    let userRuleUser = null;

    // Returns [Promise:void]
    async function onUserRuleTableRecordAction (event)
    {
        // (Getting the value)
        const entry = event.detail;

        switch ( entry.action )
        {
            case 'view':
                // (Getting the values)
                const { code, headers, body } = await userShareRuleEntity.find( entry.id );

                if ( code !== 200 )
                {// (Request failed)
                    // Returning the value
                    return;
                }



                // (Setting the values)
                userRuleForm.setValues( body );



                // (Showing the modal)
                userRuleModal.show();



                // (Getting the value)
                userRuleUser = body['user'];
            break;

            case 'delete':
                // (Deleting the records)
                await userShareRuleEntity.delete( [ entry.id ] );



                // (Opening the modal)
                await api.open( api.resourceId, api.elementId, api.shareRuleId );
            break;
        }
    }

    // Returns [Promise:bool]
    async function onUserRuleFormSubmit ()
    {
        // (Validating the form)
        const result = userRuleForm.validate();

        if ( !result.valid ) return false;



        // (Getting the value)
        const input = result.fetch();

        if ( !input['id'] )
        {// Value not found
            // (Getting the values)
            input['resource'] = api.resourceId;
            input['element']  = api.elementId;
        }



        // (Upserting the element)
        const { code, headers, body } = await userShareRuleEntity.upsert( input );

        if ( code !== 200 )
        {// (Request failed)
            // Returning the value
            return false;
        }



        // (Opening the modal)
        await api.open( api.resourceId, api.elementId, api.shareRuleId );



        // Returning the value
        return true;
    }



    let groupRuleModal;
    let groupRuleForm;

    // Returns [Promise:bool]
    async function onGroupRuleFormSubmit ()
    {
        // (Validating the form)
        const result = groupRuleForm.validate();

        if ( !result.valid ) return false;



        // (Getting the value)
        const input = result.fetch();

        if ( !input['id'] )
        {// Value not found
            // (Getting the values)
            input['resource'] = api.resourceId;
            input['element']  = api.elementId;
        }



        // (Upserting the element)
        const { code, headers, body } = await groupShareRuleEntity.upsert( input );

        if ( code !== 200 )
        {// (Request failed)
            // Returning the value
            return false;
        }



        // (Opening the modal)
        await api.open( api.resourceId, api.elementId, api.shareRuleId );



        // Returning the value
        return true;
    }



    let userRuleTable;
    let userRuleRecords = [];

    // Returns [Promise:void]
    async function onAddUserRule ()
    {
        // (Getting the values)
        const { code, headers, body } = await client.run( 'User.list_index' );

        if ( code !== 200 )
        {// (Request failed)
            // Returning the value
            return;
        }



        // (Getting the value)
        $user['users'] = body;



        // (Resetting the form)
        userRuleForm.reset();

        // (Showing the modal)
        userRuleModal.show();



        // (Setting the value)
        userRuleUser = null;
    }

    // Returns [Promise:bool]
    async function onBulkRemoveUserRule ()
    {
        // (Setting the value)
        const ids = [];

        for ( const id of userRuleTable.fetchSelectedRecords() )
        {// Processing each entry
            // (Appending the value)
            ids.push( userRuleRecords[id].id );
        }



        // (Getting the values)
        const { code, headers, body } = await userShareRuleEntity.delete( ids );

        if ( code !== 200 )
        {// (Request failed)
            // Returning the value
            return false;
        }



        // Returning the value
        return true;
    }



    let groupRuleTable;
    let groupRuleRecords = [];

    // Returns [Promise:void]
    async function onGroupRuleTableRecordAction (event)
    {
        // (Getting the value)
        const entry = event.detail;

        switch ( entry.action )
        {
            case 'view':
                // (Getting the values)
                const { code, headers, body } = await groupShareRuleEntity.find( entry.id );

                if ( code !== 200 )
                {// (Request failed)
                    // Returning the value
                    return;
                }



                // (Setting the values)
                groupRuleForm.setValues( body );



                // (Showing the modal)
                groupRuleModal.show();
            break;

            case 'delete':
                // (Deleting the records)
                await groupShareRuleEntity.delete( [ entry.id ] );



                // (Opening the modal)
                await api.open( api.resourceId, api.elementId, api.shareRuleId );
            break;
        }
    }

    // Returns [Promise:void]
    async function onAddGroupRule ()
    {
        // (Getting the values)
        const { code, headers, body } = await client.run( 'Group.list_index' );

        if ( code !== 200 )
        {// (Request failed)
            // Returning the value
            return;
        }



        // (Getting the value)
        $user['groups'] = body;



        // (Resetting the form)
        groupRuleForm.reset();

        // (Showing the modal)
        groupRuleModal.show();
    }

    // Returns [Promise:bool]
    async function onBulkRemoveGroupRule ()
    {
        // (Setting the value)
        const ids = [];

        for ( const id of groupRuleTable.fetchSelectedRecords() )
        {// Processing each entry
            // (Appending the value)
            ids.push( groupRuleRecords[id].id );
        }



        // (Getting the values)
        const { code, headers, body } = await groupShareRuleEntity.delete( ids );

        if ( code !== 200 )
        {// (Request failed)
            // Returning the value
            return false;
        }



        // Returning the value
        return true;
    }

</script>



{ #if $user['users'] }
    <Modal title="Share" bind:api={ modal } width="900px">
        <Form bind:api={ form }>
            <!--<input type="hidden" class="input form-input" name="resource_id" value="" data-type="int">
            <input type="hidden" class="input form-input" name="element_id" value="" data-type="int">-->

            <div class="row">
                <div class="col">
                    <fieldset class="fieldset">
                        <legend>User Rules</legend>
                        <Table controls bind:api={ userRuleTable } bind:records={ userRuleRecords } on:record.action={ onUserRuleTableRecordAction }>
                            <div slot="fixed-controls">
                                { #if $user['hierarchy'] < 3 }
                                    <button type="button" class="btn btn-primary btn-sm" title="add" on:click={ onAddUserRule } disabled={ api.shareRuleId !== 1 }>
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                { /if }
                            </div>
                            <div slot="selection-controls">
                                { #if $user['hierarchy'] < 3 }
                                    <button type="button" class="btn btn-danger btn-sm" title="remove" on:click={ onBulkRemoveUserRule }>
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                { /if }
                            </div>
                        </Table>
                    </fieldset>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col">
                    <fieldset class="fieldset">
                        <legend>Group Rules</legend>
                        <Table controls bind:api={ groupRuleTable } bind:records={ groupRuleRecords } on:record.action={ onGroupRuleTableRecordAction }>
                            <div slot="fixed-controls">
                                { #if $user['hierarchy'] < 3 }
                                    <button type="button" class="btn btn-primary btn-sm" title="add" on:click={ onAddGroupRule } disabled={ api.shareRuleId !== 1 }>
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                { /if }
                            </div>
                            <div slot="selection-controls">
                                { #if $user['hierarchy'] < 3 }
                                    <button type="button" class="btn btn-danger btn-sm" title="remove" on:click={ onBulkRemoveGroupRule }>
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                { /if }
                            </div>
                        </Table>
                    </fieldset>
                </div>
            </div>
        </Form>
    </Modal>

    <Modal title="User Rule" bind:api={ userRuleModal }>
        <Form bind:api={ userRuleForm } on:submit={ onUserRuleFormSubmit }>
            <input type="hidden" class="input form-input" name="id" value="" data-type="int">

            <div class="row">
                <div class="col">
                    <!-- svelte-ignore a11y-label-has-associated-control -->
                    <label class="d-block m-0">
                        User (*)
                        <select class="form-control input form-input" name="user" data-type="int" data-required>
                            <option value=""></option>
                            <option disabled></option>

                            { #each Object.values( $user['users'] ?? [] ) as user }
                                <option value={ user['id'] }>{ user['name'] }</option>
                            { /each }
                        </select>
                    </label>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col">
                    <label class="d-block m-0">
                        Type (*)
                        <select class="form-control input form-input" name="share_rule" data-type="int" data-required>
                            <option value=""></option>
                            <option disabled></option>

                            { #each Object.values( $user['share_rules'] ?? [] ) as rule }
                                <option value={ rule['id'] }>{ rule['name'] }</option>
                            { /each }
                        </select>
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

    <Modal title="Group Rule" bind:api={ groupRuleModal }>
        <Form bind:api={ groupRuleForm } on:submit={ onGroupRuleFormSubmit }>
            <input type="hidden" class="input form-input" name="id" value="" data-type="int">

            <div class="row">
                <div class="col">
                    <label class="d-block m-0">
                        Group (*)
                        <select class="form-control input form-input" name="group" data-type="int" data-required>
                            <option value=""></option>
                            <option disabled></option>

                            { #each Object.values( $user['groups'] ?? [] ) as group }
                                <option value={ group['id'] }>{ group['name'] }</option>
                            { /each }
                        </select>
                    </label>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col">
                    <label class="d-block m-0">
                        Type (*)
                        <select class="form-control input form-input" name="share_rule" data-type="int" data-required>
                            <option value=""></option>
                            <option disabled></option>

                            { #each Object.values( $user['share_rules'] ?? [] ) as rule }
                                <option value={ rule['id'] }>{ rule['name'] }</option>
                            { /each }
                        </select>
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
{ /if }