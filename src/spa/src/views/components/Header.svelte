<svelte:window on:keydown={ onKeyDown }/>

<script>

    import { envs } from '@/envs.js';

    import * as URL from '@/modules/URL.js';
    import * as IDB from '@/modules/IDB.js';
    import * as File from '@/modules/File.js';
    import * as Encryption from '@/modules/Encryption.js';
    import * as Time from '@/modules/Time.js';
    import * as Buffer from '@/modules/Buffer.js';
    import * as FlexObject from '@/modules/FlexObject.js';

    import { KeyPair, PublicKey, PrivateKey } from '@/modules/Encryption.js';

    import { Client } from '@/modules/Client.ts';
    import { Entity } from '@/modules/Entity.ts';

    import { user } from '@/stores/user.js';
    import { appData } from '@/stores/appData.js';
    import { idk } from '@/stores/idk.js';

    import Modal from '@/views/components/Modal.svelte';
    import Form from '@/views/components/Form.svelte';
    import PasswordField from '@/views/components/PasswordField.svelte';
    import Switch from '@/views/components/Switch.svelte';
    import Helper from '@/views/components/Helper.svelte';
    import PropTable from '@/views/components/PropTable.svelte';
    import Table from '@/views/components/Table.svelte';

    import { goto } from '$app/navigation';
    import { onMount, tick } from 'svelte';
    import { browser } from '$app/environment';



    let profileModal;
    let securityModal;



    const client = new Client( '/api/user' );



    const userEntity = client.entity( 'User' );



    let changeNameForm;

    // Returns [Promise:bool]
    async function onChangeNameFormSubmit ()
    {
        // (Validating the form)
        let result = changeNameForm.validate();

        if ( !result.valid ) return false;



        if ( !confirm( 'Are you sure to change the name ?' ) ) return false;



        // (Getting the value)
        const input = result.fetch();



        // (Getting the values)
        const { code, headers, body } = await userEntity.set( 'set_own_name', input['name'] );

        if ( code !== 200 )
        {// (Request failed)
            // Returning the value
            return false;
        }



        // (Getting the value)
        $user['name'] = result.entries['name'].value;



        // (Alerting the value)
        //alert('Name has been changed');



        // Returning the value
        return true;
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
        const input = result.fetch();



        // (Getting the values)
        const { code, headers, body } = await userEntity.set( 'set_own_email', input['email'] );

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



    let changeBirthDataForm;

    // Returns [Promise:bool]
    async function onChangeBirthDataFormSubmit ()
    {
        // (Validating the form)
        let result = changeBirthDataForm.validate();

        if ( !result.valid ) return false;



        if ( !confirm( 'Are you sure to change the birth data ?' ) ) return false;



        // (Getting the value)
        const input = result.fetch();



        // (Getting the values)
        const { code, headers, body } = await userEntity.set( 'set_own_birth_data', input );

        if ( code !== 200 )
        {// (Request failed)
            // Returning the value
            return false;
        }



        // (Getting the values)
        $user['birth']['name']    = result.entries['name'].value;
        $user['birth']['surname'] = result.entries['surname'].value;



        // (Alerting the value)
        //alert('Birth data have been changed');



        // Returning the value
        return true;
    }



    let changePasswordForm;

    // Returns [Promise:bool]
    async function onChangePasswordFormSubmit ()
    {
        // (Validating the form)
        let result = changePasswordForm.validate();

        if ( !result.valid ) return false;



        if ( !confirm( 'Are you sure to change the password ?' ) ) return false;



        // (Getting the value)
        const input = result.fetch();



        // (Getting the values)
        const { code, headers, body } = await userEntity.set( 'set_own_password', input['password'] );

        if ( code !== 200 )
        {// (Request failed)
            // Returning the value
            return false;
        }



        // (Setting the value)
        $user['password_set'] = true;



        // (Resetting the form)
        changePasswordForm.reset();



        // (Alerting the value)
        //alert('Password has been changed');



        // Returning the value
        return true;
    }



    let mfaSwitch;

    // Returns [Promise:bool]
    async function onSetMfa (event)
    {
        // (Getting the value)
        const input =
        {
            'mfa': event.detail.target.checked
        }
        ;



        // (Getting the values)
        const { code, headers, body } = await userEntity.set( 'set_own_mfa', input['mfa'] ? 1 : 0 );

        if ( code !== 200 )
        {// (Request failed)
            // Returning the value
            return false;
        }



        // Returning the value
        return true;
    }



    let mfaTrustedDeviceSwitch;

    // Returns [Promise:bool]
    async function onSetMfaTrustedDevice (event)
    {
        // (Getting the value)
        const input =
        {
            'trusted_device': event.detail.target.checked
        }
        ;



        // (Getting the values)
        const { code, headers, body } = await userEntity.set( 'set_own_mfa_trusted_device', input['trusted_device'] ? 1 : 0 );

        if ( code !== 200 )
        {// (Request failed)
            // Returning the value
            return false;
        }



        // Returning the value
        return true;
    }



    let idkSwitch;

    // Returns [Promise:bool]
    async function onSetIdk (event)
    {
        // (Setting the value)
        let message = null;



        // (Getting the value)
        const checked = event.detail.target.checked;

        if ( checked )
        {// Value is true
            // (Setting the value)
            message = "Are you sure to enable the IDK authentication ?\n\nWhen enabled you can authenticate in 2 ways :\n\n1) By providing the generated key\n2) With your password (plus MFA if enabled)";
        }
        else
        {// Value is false
            // (Setting the value)
            message = "Are you sure to disable the IDK authentication ?\n\nWhen disabled you can authenticate with your password (plus MFA if enabled)";
        }



        if ( !confirm( message ) )
        {// (Confirmation is failed)
            if ( checked )
            {// Value is true
                // (Setting the property)
                jQuery( '#set_idk_form .form-input[name="idk"]' ).prop( 'checked', false );

                // Returning the value
                return false;
            }
            else
            {// Value is false
                // (Setting the property)
                jQuery( '#set_idk_form .form-input[name="idk"]' ).prop( 'checked', true );
            }



            // Returning the value
            return false;
        }



        // (Setting the value)
        let input = {};



        let keyPair = null;

        if ( checked )
        {// Value is true
            // (Getting the value)
            const nonce = await Encryption.generateNonce( 16 );



            // (Getting the value)
            keyPair = await KeyPair.generate();



            // (Getting the value)
            const encNonce = Buffer.toBase64( await keyPair.getPublicKey().encrypt( nonce ) );



            // (Getting the value)
            input =
            {
                'authentication': checked,
                'public_key':     keyPair.getPublicKey().toString(),
                'enc_nonce':      encNonce
            }
            ;
        }
        else
        {// Value is false
            // (Getting the value)
            input =
            {
                'authentication': checked,
                'public_key':     null,
                'enc_nonce':      null
            }
            ;
        }
        


        // (Getting the values)
        const { code, headers, body } = await userEntity.set( 'set_own_idk', input );

        if ( code !== 200 )
        {// (Request failed)
            // Returning the value
            return false;
        }



        if ( checked )
        {// Value found
            // (Getting the value)
            $idk = `${ $user['uuid'] }\n\n${ keyPair.getPrivateKey().toString() }`;

            // (Downloading the file)
            File.download( `${ $user['name'] }@${ $user['tenant']['name'] }.idk`, 'text/plain', $idk );
            // (Alerting the value)
            alert( 'IDK has been ' + ( checked ? "enabled.\n\nSave the downloaded key in a safe place !" : 'disabled' ) );



            // (Getting the value)
            $user['idk'] = checked;



            // (Setting the value)
            await ( new IDB.Connection( envs.IDB_DATABASE, 'idk' ) ).set( $user['uuid'], $idk );
        }
        else
        {// Value not found
            // (Setting the value)
            $idk = null;



            // (Setting the value)
            $user['idk'] = false;



            // (Setting the value)
            await ( new IDB.Connection( envs.IDB_DATABASE, 'idk' ) ).remove( $user['uuid'] );
        }



        // Returning the value
        return true;
    }



    let idkForcedSwitch;

    // Returns [Promise:bool]
    async function onSetIdkForced (event)
    {
        // (Setting the value)
        let message = null;



        // (Getting the value)
        const checked = event.detail.target.checked;

        if ( checked )
        {// Value is true
            // (Setting the value)
            message = "Are you sure to force the IDK authentication ?\n\nWhen enabled you can authenticate only by providing the generated key";
        }
        else
        {// Value is false
            // (Setting the value)
            message = "Are you sure to not force the IDK authentication ?\n\nWhen disabled you can authenticate in 2 ways :\n\n1) With your password (plus MFA if enabled)\n2) By providing the generated key";
        }



        if ( !confirm( message ) )
        {// (Confirmation is failed)
            if ( checked )
            {// Value is true
                // (Setting the property)
                jQuery( '#set_idk_form .form-input[name="idk_forced"]' ).prop( 'checked', false );

                // Returning the value
                return false;
            }
            else
            {// Value is false
                // (Setting the property)
                jQuery( '#set_idk_form .form-input[name="idk_forced"]' ).prop( 'checked', true );
            }



            // Returning the value
            return false;
        }



        // (Getting the value)
        const input =
        {
            'idk_forced': checked
        }
        ;



        // (Getting the values)
        const { code, headers, body } = await userEntity.set( 'set_own_idk_forced', input['idk_forced'] ? 1 : 0 );

        if ( code !== 200 )
        {// (Request failed)
            // Returning the value
            return false;
        }



        // Returning the value
        return true;
    }



    // Returns [Promise:bool]
    async function logout ()
    {
        if ( !confirm( 'Are you sure to logout ?' ) ) return false;



        // (Getting the values)
        const { code, headers, body } = await client.run( 'User.logout' );

        if ( code !== 200 )
        {// (Request failed)
            // Returning the value
            return false;
        }



        // (Setting the location)
        window.location.href = '/login';



        // Returning the value
        return true;
    }



    // Returns [void]
    function setLoginTableRecords ()
    {
        // (Setting the value)
        loginTableRecords = [];

        for ( const record of Object.values( $user['sessions'] ) )
        {// Processing each entry
            // (Getting the value)
            const r =
            {
                'id':             record['session']['id'],

                'values':
                [
                    {
                        'column':  'description',
                        'value':   record['description'],

                        'content': record['description']
                    },

                    {
                        'column': 'ip',
                        'value':  record['ip']
                    },

                    {
                        'column': 'country',
                        'value':  record['ip_info']['country']['code'] ? `${ record['ip_info']['country']['name'] } ( ${ record['ip_info']['country']['code'] } )` : ''
                    },

                    {
                        'column': 'isp',
                        'value':  record['ip_info']['isp'] ? `${ record['ip_info']['isp'] } ( ${ record['ip_info']['isp'] } )` : ''
                    },

                    {
                        'column': 'browser',
                        'value':  record['ua_info']['browser']
                    },

                    {
                        'column': 'os',
                        'value':  record['ua_info']['os']
                    },

                    {
                        'column': 'hw',
                        'value':  record['ua_info']['hw']
                    },

                    {
                        'column':  'datetime.login',
                        'value':   record['datetime']['insert'] ? Time.toLocal( record['datetime']['insert'] ) : ''
                    }
                ],

                'controls':
                    `
                        <div class="d-flex justify-content-center align-items-center">
                            <button type="button" class="btn btn-sm btn-danger action-input" data-action="delete" title="disconnect client" ${ record['current'] ? 'disabled' : '' }>
                                <i class="fa-solid fa-sign-out-alt"></i>
                            </button>
                        </div>
                    `
                ,

                'data':
                {
                    'current_session': record['current']
                },

                'hidden': false
            }
            ;

            // (Appending the value)
            loginTableRecords.push( r );
        }

        // (Getting the value)
        loginTableRecords = loginTableRecords;
    }

    // Returns [void]
    function setTrustedDeviceTableRecords ()
    {
        if ( !$user['trusted_devices'] ) return;



        // (Setting the value)
        trustedDeviceTableRecords = [];

        for ( const record of Object.values( $user['trusted_devices'] ) )
        {// Processing each entry
            // (Getting the value)
            const r =
            {
                'id':             record['id'],

                'values':
                [
                    {
                        'column':  'name',
                        'value':   record['name'] ?? '',

                        'content': `<input type="text" class="form-control form-control-sm input input-text" name="name" value="${ record['name'] ?? '' }">`
                    },

                    {
                        'column': 'browser',
                        'value':  record['ua_info']['browser']
                    },

                    {
                        'column': 'os',
                        'value':  record['ua_info']['os']
                    },

                    {
                        'column': 'hw',
                        'value':  record['ua_info']['hw']
                    },

                    {
                        'column':  'datetime.verification',
                        'value':   record['datetime']['insert'] ? Time.toLocal( record['datetime']['insert'] ) : ''
                    }
                ],

                'controls':
                    `
                        <div class="d-flex justify-content-center align-items-center">
                            <button type="button" class="btn btn-sm btn-primary action-input" data-action="set_current" title="set current device">
                                <i class="fa-solid fa-check"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger action-input ml-2" data-action="delete" title="remove device">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    `
                ,

                'hidden': false
            }
            ;

            // (Appending the value)
            trustedDeviceTableRecords.push( r );
        }

        // (Getting the value)
        trustedDeviceTableRecords = trustedDeviceTableRecords;
    }

    // Returns [void]
    function setPersonalTokenTableRecords ()
    {
        if ( !$user['personal_tokens'] ) return;



        // (Setting the value)
        personalTokenTableRecords = [];

        for ( const record of Object.values( $user['personal_tokens'] ) )
        {// Processing each entry
            // (Getting the value)
            const r =
            {
                'id':              record['id'],

                'values':
                [
                    {
                        'column': 'name',
                        'value':  record['name']
                    },

                    {
                        'column': 'datetime.insert',
                        'value':  record['datetime']['insert'] ? Time.toLocal( record['datetime']['insert'] ) : ''
                    },

                    {
                        'column': 'datetime.update',
                        'value':  record['datetime']['update'] ? Time.toLocal( record['datetime']['update'] ) : ''
                    }
                ],

                'controls':
                    `
                        <div class="d-flex justify-content-center align-items-center">
                            <button type="button" class="btn btn-sm btn-primary action-input" data-action="view" title="view">
                                <i class="fa-solid fa-up-right-from-square"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger ml-2 action-input" data-action="delete" title="remove">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    `
                ,

                'hidden': false
            }
            ;



            // (Appending the value)
            personalTokenTableRecords.push( r );
        }

        // (Getting the value)
        personalTokenTableRecords = personalTokenTableRecords;
    }



    // Returns [void]
    function wsConnect ()
    {
        // (Getting the value)
        const ws = new WebSocket( `wss://${ window.location.host }/micro/alert` );

        // (Listening for the event)
        ws.addEventListener('open', function () {
            // (Logging the message)
            console.debug( 'Client connected to alert server' );
        });

        // (Listening for the event)
        ws.addEventListener('message', function (event) {
            // (Logging the message)
            console.debug( 'Client received a message from alert server' );



            // (Getting the value)
            const record = FlexObject.expand( JSON.parse( event.data ) );



            // (Getting the value)
            $user['alerts'][ record['id'] ] = record;



            // (Setting the value)
            alertSignaler = true;
        });

        // (Listening for the event)
        ws.addEventListener('close', function () {
            // (Logging the message)
            console.debug( 'Client disconnected from alert server' );



            // (Logging the message)
            console.debug( 'Trying to reconnect to the server ... (5s)' );

            // (Setting the timeout)
            setTimeout( wsConnect, 5000 );
        });
    }



    // (Listening for the event)
    onMount
    (
        async function ()
        {
            // (Waiting for the rendering)
            await tick();



            if ( !$user['password_set'] )
            {// (Password is not set)
                // (Showing the modal)
                securityModal.show();   



                // (Resetting the form)
                changePasswordForm.reset();

                // (Validating the form)
                changePasswordForm.validate();
            }



            // (Getting the value)
            mfaSwitch.checked = $user['mfa'];

            // (Getting the value)
            idkSwitch.checked = $user['idk'];

            // (Getting the value)
            idkForcedSwitch.checked = $user['idk_forced'];

            // (Getting the value)
            mfaTrustedDeviceSwitch.checked = $user['trusted_device'];



            // (Setting the login table records)
            setLoginTableRecords();



            // (Setting the trusted device table records)
            setTrustedDeviceTableRecords();



            // (Setting the personal token table records)
            setPersonalTokenTableRecords();



            // (Connecting the client to alert server)
            wsConnect();



            // (Getting the value)
            //alertSignaler = Object.keys( $user['alerts'] ).length > 0;
        }
    )
    ;



    // Returns [Promise:void]
    async function importIDK ()
    {
        // (Selecting the files)
        const file = ( await File.select( '.idk' ) )[0];



        // (Getting the value)
        $idk = await File.readText( file );

        // (Setting the value)
        await ( new IDB.Connection( envs.IDB_DATABASE, 'idk' ) ).set( $user['id'], $idk );
    }

    // Returns [Promise:void]
    async function exportIDK ()
    {
        // (Getting the value)
        const idk = await ( new IDB.Connection( envs.IDB_DATABASE, 'idk' ) ).get( $user['id'] );

        if ( !idk )
        {// (IDK is not present)
            alert( 'IDK is not present in the local memory' );

            return;
        }



        // (Downloading the file)
        File.download( `${ $user['name'] }@${ $user['tenant']['name'] }.idk`, 'text/plain', idk );
    }

    // Returns [Promise:void]
    async function ejectIDK ()
    {
        if ( !confirm( 'Are you sure to remove the IDK from the local memory ?' ) ) return;



        // (Removing the value)
        await ( new IDB.Connection( envs.IDB_DATABASE, 'idk' ) ).remove( $user['id'] );

        // (Setting the value)
        $idk = null;
    }



    // Returns [void]
    function toggleFullscreen ()
    {
        if ( document.fullscreenElement )
        {// Value is true
            // (Disabling the fullscreen)
            document.exitFullscreen();
        }
        else
        {// Value is false
            // (Enabling the fullscreen)
            document.body.requestFullscreen();
        }
    }



    // Returns [void]
    function onKeyDown (event)
    {
        if ( document.activeElement.classList.contains('input') ) return;

        if ( document.activeElement.closest('.input') !== null ) return;

        switch ( event.key )
        {
            case 'f':// (Fullscreen controls)
                // (Calling the function)
                toggleFullscreen();
            break;

            case 'o':// (Logout controls)
                // (Doing the logout)
                logout();
            break;
        }
    }



    let alertModal;

    let alertId    = null;
    let alertData  = {};

    // Returns [void]
    function viewAlert (id)
    {
        // (Getting the value)
        const alertSeverity = $user['alert_severities'][ $user['alerts'][id]['alert_severity'] ];



        // (Getting the values)
        alertId    = id;
        alertData  =
        {
            'datetime.insert': $user['alerts'][id]['datetime']['insert'] ? Time.toLocal( $user['alerts'][id]['datetime']['insert'] ) : '',

            'action':          $user['alerts'][id]['action'],
            'description':     $user['alerts'][id]['description'],
            'ip':              `${ $user['alerts'][id]['ip'] } - ${ $user['alerts'][id]['ip_info']['country']['code'] } - ${ $user['alerts'][id]['ip_info']['isp'] }`,
            'browser':         $user['alerts'][id]['ua_info']['browser'],
            'os':              $user['alerts'][id]['ua_info']['os'],
            'hardware':        $user['alerts'][id]['ua_info']['hw'],

            'severity':
                `
                <span class="text-right">
                    <span class="color-label">
                        <span class="color-label-color" style="background-color: ${ alertSeverity['color'] };"></span>
                        <span class="color-label-text" style="background-color: ${ alertSeverity['color'] }0a; border: 1px solid ${ alertSeverity['color'] }1a;">${ $user['alerts'][id]['alert_severity'] + ' - ' + alertSeverity['name'] }</span>
                    </span>
                </span>
                `
        }
        ;



        // (Showing the modal)
        alertModal.show();
    }

    // Returns [Promise:bool]
    async function markAlertAsRead (ids)
    {
        // (Getting the values)
        const { code, headers, body } = await userEntity.set( 'mark_alert_as_read', ids );

        if ( code !== 200 )
        {// (Request failed)
            // Returning the value
            return false;
        }



        // (Setting the value)
        const records = {};

        for ( const record of Object.values( $user['alerts'] ) )
        {// Processing each entry
            if ( ids.includes( record['id'] ) ) continue;

            // (Getting the value)
            records[ record['id'] ] = record;
        }

        // (Getting the value)
        $user['alerts'] = records;



        // (Hiding the modal)
        alertModal.hide();



        // Returning the value
        return true;
    }



    const sessionEntity = client.entity( 'Session' );



    let loginTable;
    let loginTableRecords = [];

    $:
        if ( loginTable )
        {// Value found
            // (Setting the table)
            sessionEntity.setTable( loginTable );
        }

    

    // Returns [Promise:void]
    async function onLoginTableEntryAction (event)
    {
        // (Getting the value)
        const data = event.detail;

        switch ( data.type )
        {
            case 'delete':
                // (Deleting the records)
                await sessionEntity.delete( [ data.id ] );
            break;
        }
    }

    // Returns [Promise:void]
    async function onLoginTableBulkAction (event)
    {
        // (Getting the value)
        const data = event.detail;

        switch ( data.type )
        {
            case 'delete':
                // (Getting the value)
                const ids = data.selection.map( function (index) { return loginTableRecords[ index ].id; } );

                // (Deleting the records)
                await sessionEntity.delete( ids );
            break;
        }
    }



    const trustedDeviceEntity = client.entity( 'TrustedDevice' );



    let trustedDeviceTable;
    let trustedDeviceTableRecords = [];

    $:
        if ( trustedDeviceTable )
        {// Value found
            // (Setting the table)
            trustedDeviceEntity.setTable( trustedDeviceTable );
        }



    // Returns [Promise:void]
    async function onTrustedDeviceTableEntryAction (event)
    {
        // (Getting the value)
        const data = event.detail;

        switch ( data.type )
        {
            case 'set_current':
                if ( confirm( 'Are you sure to set the current device as trusted ?' ) )
                {// (Confirmation is ok)
                    // (Setting the item)
                    localStorage.setItem( 'trusted_device', data.id );
                }
            break;

            case 'delete':
                // (Deleting the records)
                await trustedDeviceEntity.delete( [ data.id ] );
            break;
        }
    }

    // Returns [Promise:void]
    async function onTrustedDeviceTableBulkAction (event)
    {
        // (Getting the value)
        const data = event.detail;

        switch ( data.type )
        {
            case 'delete':
                // (Getting the value)
                const ids = data.selection.map( function (index) { return trustedDeviceTableRecords[ index ].id; } );

                // (Deleting the records)
                await trustedDeviceEntity.delete( ids );
            break;
        }
    }

    // Returns [Promise:bool]
    async function onTrustedDeviceTableRecordFieldChange (event)
    {
        // (Getting the value)
        const data = event.detail;



        // (Getting the value)
        const input =
        {
            'id':   data.id,
            'name': data.value
        }
        ;



        // (Getting the values)
        const { code, headers, body } = await trustedDeviceEntity.set( 'set_name', input );

        if ( code !== 200 )
        {// (Request failed)
            // Returning the value
            return false;
        }



        // Returning the value
        return true;
    }



    // (Getting the value)
    const personalTokenEntity = client.entity( 'PersonalToken' );

    const PersonalToken =
    {
        // Returns [Promise:bool]
        'list': async function ()
        {
            // (Getting the values)
            const { code, headers, body } = await personalTokenEntity.list();

            if ( code !== 200 )
            {// (Request failed)
                // Returning the value
                return false;
            }



            // (Getting the value)
            $user['personal_tokens'] = body;

            // (Setting table records)
            setPersonalTokenTableRecords();



            // Returning the value
            return true;
        },

        // Returns [Promise:bool]
        'view': async function (id)
        {
            // (Getting the values)
            const { code, headers, body } = await personalTokenEntity.find( id );

            if ( code !== 200 )
            {// (Request failed)
                // Returning the value
                return false;
            }



            // (Resetting the form)
            personalTokenForm.reset();

            // (Setting the input)
            personalTokenForm.setValues( body );



            // (Showing the modal)
            personalTokenModal.show();



            // Returning the value
            return true;
        }
    }
    ;



    let personalTokenTable;
    let personalTokenTableRecords = [];

    $:
        if ( personalTokenTable )
        {// Value found
            // (Setting the table)
            personalTokenEntity.setTable( personalTokenTable );
        }



    // Returns [Promise:void]
    async function onPersonalTokenTableEntryAction (event)
    {
        // (Getting the value)
        const data = event.detail;

        switch ( data.type )
        {
            case 'view':
                // (Viewing the record)
                await PersonalToken.view( data.id );
            break;

            case 'delete':
                // (Deleting records)
                await personalTokenEntity.delete( [ data.id ] );
            break;
        }
    }

    // Returns [Promise:void]
    async function onPersonalTokenTableBulkAction (event)
    {
        // (Getting the value)
        const data = event.detail;

        switch ( data.type )
        {
            case 'delete':
                // (Getting the value)
                const ids = data.selection.map( function (index) { return personalTokenTableRecords[ index ].id; } );

                // (Deleting the records)
                await personalTokenEntity.delete( ids );
            break;
        }
    }



    let personalTokenModal;

    $:
        if ( personalTokenModal )
        {// Value found
            // (Setting the modal)
            personalTokenEntity.setModal( personalTokenModal );
        }



    let personalTokenForm;

    $:
        if ( personalTokenForm )
        {// Value found
            // (Setting the form)
            personalTokenEntity.setForm( personalTokenForm );
        }



    // Returns [Promise:void]
    async function onPersonalTokenFormSubmit ()
    {
        // (Validating the form)
        const result = personalTokenForm.validate();

        if ( !result.valid ) return false;



        // (Getting the value)
        const input = result.fetch();



        // (Getting the values)
        const { code, headers, body } = await personalTokenEntity.upsert( input );

        if ( code !== 200 )
        {// (Request failed)
            // Returning the value
            return false;
        }



        if ( body['token'] )
        {// (Operation is insert)
            // (Writing to the clipboard)
            await navigator.clipboard.writeText( body['token'] );

            // (Alerting the message)
            alert( `Token has been generated and copied to clipboard.\n\nSave it in a safe place.` );
        }



        // (Listing the records)
        await PersonalToken.list();



        // Returning the value
        return true;
    }



    // Returns [Promise:bool]
    async function onProfileItemClick ()
    {
        // (Showing the modal)
        profileModal.show();



        // Returning the value
        return true;
    }

    // Returns [Promise:bool]
    async function onSecurityItemClick ()
    {
        // (Getting the values)
        const { code, headers, body } = await client.run( 'Security.fetch' );

        if ( code !== 200 )
        {// (Request failed)
            // Returning the value
            return false;
        }



        // (Getting the value)
        $user['sessions'] = body['sessions'];

        // (Setting the login table records)
        setLoginTableRecords();



        // (Getting the value)
        $user['trusted_devices'] = body['trusted_devices'];

        // (Setting the trusted device table records)
        setTrustedDeviceTableRecords();



        // (Getting the value)
        $user['personal_tokens'] = body['personal_tokens'];

        // (Setting the personal token table records)
        setPersonalTokenTableRecords();



        // (Showing the modal)
        securityModal.show();



        // Returning the value
        return true;
    }



    let workspaceModal;

    // Returns [Promise:bool]
    async function onWorkspaceItemClick ()
    {
        // (Showing the modal)
        workspaceModal.show();



        // Returning the value
        return true;
    }



    const tenantEntity = client.entity( 'Tenant' );

    const Workspace =
    {
        // Returns [Promise:bool]
        'setName': async function (input)
        {
            // (Getting the values)
            const { code, headers, body } = await tenantEntity.set( 'set_name', input );

            if ( code !== 200 )
            {// (Request failed)
                // Returning the value
                return false;
            }



            // Returning the value
            return true;
        }
    }
    ;



    let changeWorkspaceForm;

    // Returns [Promise:void]
    async function onChangeWorkspaceFormSubmit ()
    {
        // (Validating the form)
        const result = changeWorkspaceForm.validate();

        if ( !result.valid ) return false;



        // (Getting the value)
        const input = result.fetch();



        if ( !confirm( 'Are you sure to change the name of current workspace ?' ) ) return;



        if ( await Workspace.setName( input['name'] ) )
        {// (Request OK)
            // (Getting the value)
            $user['tenant']['name'] = input['name'];
        }
    }



    let userMenuOpen = false;


    
    let alertSignaler = false;



    $:
        if ( $user )
        {// Value found
            // (Setting the request token)
            Entity.requestToken = $user['request_token'];
        }

</script>

<!-- Topbar -->
<nav class="navbar navbar-expand topbar static-top shadow">

    <!-- Sidebar Toggle (Topbar) -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>

    <!-- Brand -->
    <a class="brand-box nav-link d-flex align-items-center justify-content-center" href="/">
        <img src="/assets/logo.jpg" alt="" class="app-logo">
        <div class="mx-3">Simba</div>
    </a>

    <!-- Topbar Search -->
    <form
        class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
        <div class="input-group">
            <input type="text" class="form-control input form-input border-0 small" placeholder="Search ...">
            <div class="input-group-append">
                <button class="btn btn-primary" type="button">
                    <i class="fas fa-search fa-sm"></i>
                </button>
            </div>
        </div>
    </form>

    <!-- Topbar Navbar -->
    <ul class="navbar-nav ml-auto">

        <!-- Nav Item - Search Dropdown (Visible Only XS) -->
        <li class="nav-item dropdown no-arrow d-sm-none">
            <!-- svelte-ignore a11y-invalid-attribute -->
            <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-search fa-fw"></i>
            </a>
            <!-- Dropdown - Messages -->
            <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in" aria-labelledby="searchDropdown">
                <form class="form-inline mr-auto w-100 navbar-search">
                    <div class="input-group">
                        <input type="text" class="form-control input bg-light border-0 small" placeholder="Search for..." aria-label="Search" aria-describedby="basic-addon2">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button">
                                <i class="fas fa-search fa-sm"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </li>

        <!-- Nav Item - Alerts -->
        <li class="nav-item dropdown no-arrow mx-1">
            <!-- svelte-ignore a11y-invalid-attribute -->
            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" on:click={ () => { alertSignaler = false; } } title="alerts">
                <i class="fas fa-bell fa-fw"></i>

                { #if alertSignaler }
                    <div class="alert-signaler"></div>
                { /if }

                { #if Object.values( $user['alerts'] ).length > 0 }
                    <!-- Counter - Alerts -->
                    <span class="badge badge-danger badge-counter">{ Object.values( $user['alerts'] ).length }</span>
                { /if }
            </a>
            <!-- Dropdown - Alerts -->
            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in alert-dropdown" aria-labelledby="alertsDropdown">
                <h6 class="dropdown-header">
                    <!-- svelte-ignore a11y-missing-attribute -->
                    <!-- svelte-ignore a11y-click-events-have-key-events -->
                    Alerts
                    <span class="float-right cursor-pointer" on:click={ () => { markAlertAsRead( Object.values( $user['alerts'] ).map( function (record) { return record['id']; } ) ); } } title="Mark all as read">
                        <i class="fa-solid fa-check"></i>
                    </span>
                </h6>

                <div class="dropdown-list-inner d-block">
                    { #each Object.values( $user['alerts'] ) as alert }
                        <!-- svelte-ignore a11y-click-events-have-key-events -->
                        <!-- svelte-ignore a11y-missing-attribute -->
                        <!-- svelte-ignore a11y-invalid-attribute -->
                        <a class="dropdown-item d-flex align-items-center p-0" href="#" on:click={ () => { viewAlert( alert['id'] ); } }>
                            <div class="log-type" style="background-color: { $user['alert_severities'][ alert['alert_severity'] ]['color'] };" title={ $user['alert_severities'][ alert['alert_severity'] ]['level'] + ' - ' + $user['alert_severities'][ alert['alert_severity'] ]['name'] }>
                                <!--
                                <div class="icon-circle bg-danger">
                                    <i class="fas fa-exclamation-triangle text-white"></i>
                                </div>
                                -->
                            </div>
                            <div class="log-content" style="background-color: { $user['alert_severities'][ alert['alert_severity'] ]['color'] }0a;">
                                <div class="small text-gray-500">{ Time.toLocal( alert['datetime']['insert'] ) }</div>
                                { @html alert['description'] }
                                <div class="small text-gray-500" style="font-size: 8px;">{ alert['ua_info']['browser'] } - { alert['ua_info']['os'] }</div>
                                <div class="small text-gray-500" style="font-size: 8px;">{ alert['ip'] } - { alert['ip_info']['country']['code'] }</div>
                            </div>
                        </a>
                    { /each }
                </div>

                <!--<a class="dropdown-item text-center small text-gray-500" href="#">Show All Alerts</a>-->
            </div>
        </li>

        <!-- Nav Item - Messages -->
        <li class="nav-item dropdown no-arrow mx-1" style="display: none !important;">
            <!-- svelte-ignore a11y-invalid-attribute -->
            <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-envelope fa-fw"></i>
                <!-- Counter - Messages -->
                <span class="badge badge-danger badge-counter">7</span>
            </a>

            <!-- Dropdown - Messages -->
            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="messagesDropdown">
                <h6 class="dropdown-header">
                    Message Center
                </h6>
                <!-- svelte-ignore a11y-invalid-attribute -->
                <a class="dropdown-item d-flex align-items-center" href="#">
                    <div class="dropdown-list-image mr-3">
                        <img class="rounded-circle" src="/assets/tpl/sb-admin-2/img/undraw_profile_1.svg" alt="...">
                        <div class="status-indicator bg-success"></div>
                    </div>
                    <div class="font-weight-bold">
                        <div class="text-truncate">Hi there! I am wondering if you can help me with a problem I've been having.</div>
                        <div class="small text-gray-500">Emily Fowler · 58m</div>
                    </div>
                </a>
                <!-- svelte-ignore a11y-invalid-attribute -->
                <!-- svelte-ignore a11y-invalid-attribute -->
                <a class="dropdown-item d-flex align-items-center" href="#">
                    <div class="dropdown-list-image mr-3">
                        <img class="rounded-circle" src="/assets/tpl/sb-admin-2/img/undraw_profile_2.svg" alt="...">
                        <div class="status-indicator"></div>
                    </div>
                    <div>
                        <div class="text-truncate">I have the photos that you ordered last month, how would you like them sent to you?</div>
                        <div class="small text-gray-500">Jae Chun · 1d</div>
                    </div>
                </a>
                <!-- svelte-ignore a11y-invalid-attribute -->
                <a class="dropdown-item d-flex align-items-center" href="#">
                    <div class="dropdown-list-image mr-3">
                        <img class="rounded-circle" src="/assets/tpl/sb-admin-2/img/undraw_profile_3.svg" alt="...">
                        <div class="status-indicator bg-warning"></div>
                    </div>
                    <div>
                        <div class="text-truncate">Last month's report looks great, I am very happy with the progress so far, keep up the good work!</div>
                        <div class="small text-gray-500">Morgan Alvarez · 2d</div>
                    </div>
                </a>
                <!-- svelte-ignore a11y-invalid-attribute -->
                <a class="dropdown-item text-center small text-gray-500" href="#">Read More Messages</a>
            </div>
        </li>

        <!-- Nav Item - Fullscreen -->
        <li class="nav-item dropdown no-arrow mx-1">
            <!-- svelte-ignore a11y-invalid-attribute -->
            <a class="nav-link dropdown-toggle" href="#" role="button" title="Fullscreen ON/OFF (F)" on:click={ toggleFullscreen }>
                <i class="fa-solid fa-expand"></i>
            </a>
        </li>

        <div class="topbar-divider d-none d-sm-block"></div>

        <!-- Nav Item - User Information -->
        <li class="nav-item dropdown no-arrow">
            <!-- svelte-ignore a11y-invalid-attribute -->
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-toggle="dropdown" on:click={ () => { userMenuOpen = !userMenuOpen; } }>
                <!--
                <span class="user-menu-button ml-2 mr-2 d-none d-lg-inline small">
                    <span class="mr-1" style="border-bottom: 1px solid { $user['hierarchies'][ $user['hierarchy'] ]['color'] }" title="user">{ $user['name'] }</span>@<span class="ml-1" title="tenant">{ $user['tenant']['name'] }</span>
                    
                    { #if ( userMenuOpen ) }
                        <i class="fa-solid fa-chevron-up ml-3"></i>
                    { :else }
                        <i class="fa-solid fa-chevron-down ml-3"></i>
                    { /if }
                </span>
                -->


                <span class="user-menu-button ml-2 mr-2 small color-label" style="background-color: transparent;">
                    <span class="color-label-color" style="background-color: { $user['hierarchies'][ $user['hierarchy'] ]['color'] };"></span>
                    <span class="color-label-text" style="background-color: { $user['hierarchies'][ $user['hierarchy'] ]['color'] }0a; border: 1px solid { $user['hierarchies'][ $user['hierarchy'] ]['color'] }1a;">
                        <span class="mr-1" title="user">{ $user['name'] }</span>@<span class="ml-1" title="tenant">{ $user['tenant']['name'] }</span>

                        { #if ( userMenuOpen ) }
                            <i class="fa-solid fa-chevron-up ml-3"></i>
                        { :else }
                            <i class="fa-solid fa-chevron-down ml-3"></i>
                        { /if }
                    </span>
                </span>
            </a>
            <!-- Dropdown - User Information -->
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="userDropdown">
                <!-- svelte-ignore a11y-invalid-attribute -->
                <a class="dropdown-item" href="#" on:click={ onProfileItemClick }>
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    Profile
                </a>
                <!-- svelte-ignore a11y-invalid-attribute -->
                <a class="dropdown-item" href="#" on:click={ onSecurityItemClick }>
                    <i class="fas fa-lock fa-sm fa-fw mr-2 text-gray-400"></i>
                    Security
                </a>
                <div class="dropdown-divider"></div>
                <!-- svelte-ignore a11y-invalid-attribute -->
                <a class="dropdown-item" href="#" on:click={ onWorkspaceItemClick }>
                    <i class="fas fa-users-viewfinder fa-sm fa-fw mr-2 text-gray-400"></i>
                    Workspace
                </a>
                <a class="dropdown-item" href="/activity_log">
                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                    Activity Log
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="/users">
                    <i class="fas fa-users fa-sm fa-fw mr-2 text-gray-400"></i>
                    Users
                </a>
                <a class="dropdown-item" href="/groups">
                    <i class="fas fa-users fa-sm fa-fw mr-2 text-gray-400"></i>
                    Groups
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="/firewall_rules">
                    <i class="fas fa-shield fa-sm fa-fw mr-2 text-gray-400"></i>
                    Firewall Rules
                </a>
                <a class="dropdown-item" href="/triggers">
                    <i class="fas fa-bolt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Triggers
                </a>
                <div class="dropdown-divider"></div>
                <!-- svelte-ignore a11y-invalid-attribute -->
                <a class="dropdown-item" href="#" on:click={ logout }>
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Logout
                </a>
            </div>
        </li>
    </ul>

</nav>
<!-- End of Topbar -->



<Modal id="profile_modal" title="Profile" bind:api={ profileModal } urlkey="profile">
    <Form id="change_name_form" bind:api={ changeNameForm } on:submit={ onChangeNameFormSubmit }>
        <fieldset class="fieldset">
            <legend>Name</legend>
            <div class="row">
                <div class="col d-flex align-items-center">
                    <input type="text" class="form-control input form-input" name="name" value="{ $user['name'] }" data-required>

                    <button type="submit" class="btn btn-primary ml-3">Save</button>
                </div>
            </div>
        </fieldset>
    </Form>

    <br>

    <Form id="change_email_form" bind:api={ changeEmailForm } on:submit={ onChangeEmailFormSubmit }>
        <fieldset class="fieldset">
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
                    <input type="text" class="form-control input form-input" name="email" value="{ $user['email'] }" data-required data-regex="^[^\@]+\@[^\@]+$">

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

    <br>

    <Form id="change_birth_data_form" bind:api={ changeBirthDataForm } on:submit={ onChangeBirthDataFormSubmit }>
        <fieldset class="fieldset">
            <legend>Birth Data</legend>
            <div class="row">
                <div class="col d-flex align-items-end" style="justify-content: space-between;">
                    <label class="m-0">
                        Name
                        <input type="text" class="form-control input form-input" name="name" value="{ $user['birth']['name'] }">
                    </label>
                    
                    <label class="m-0 ml-3">
                        Surname
                        <input type="text" class="form-control input form-input" name="surname" value="{ $user['birth']['surname'] }">
                    </label>

                    <button type="submit" class="btn btn-primary ml-3">Save</button>
                </div>
            </div>
        </fieldset>
    </Form>

    <br>
</Modal>

<Modal id="security_modal" title="Security" bind:api={ securityModal } urlkey="security" width="1300px">
    <Form id="change_password_form" bind:api={ changePasswordForm } on:submit={ onChangePasswordFormSubmit }>
        <fieldset class="fieldset">
            <legend>Password</legend>
            <div class="row">
                <div class="col d-flex align-items-start">
                    <PasswordField name="password" generable measurable required/>

                    <button type="submit" class="btn btn-primary ml-3">
                        Save
                    </button>
                </div>
            </div>
        </fieldset>
    </Form>

    <br>

    <Form id="set_mfa_form">
        <fieldset class="fieldset">
            <legend class="d-flex">
                <span class="mr-2">MFA (Multi-Factor Authentication)</span>

                <Helper>
                    <div slot="content">
                        1) Login with your username and password
                        <br>
                        2) Confirm operation by email
                    </div>
                </Helper>
            </legend>
            <div class="row">
                <div class="col">
                    <Switch name="mfa" on:change={ onSetMfa } } bind:api={ mfaSwitch }>
                        Enabled
                    </Switch>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col">
                    <Switch name="mfa_trusted_device" on:change={ onSetMfaTrustedDevice } } bind:api={ mfaTrustedDeviceSwitch }>
                        Trusted Device policy
                    </Switch>
                </div>
            </div>
        </fieldset>
    </Form>

    <br>

    <Form id="set_idk_form">
        <fieldset class="fieldset">
            <legend>
                <span class="mr-2">IDK (Identity Key)</span>

                <Helper>
                    <div slot="content" style="position: relative;">
                        1) Import the IDK file
                        <br>
                        2) Login with IDK
                    </div>
                </Helper>
            </legend>
            <div class="row">
                <div class="col">
                    <Switch name="idk" on:change={ onSetIdk } } bind:api={ idkSwitch }>
                        Enabled
                    </Switch>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col">
                    <Switch name="idk_forced" on:change={ onSetIdkForced } } bind:api={ idkForcedSwitch }>
                        Forced
                    </Switch>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col text-right">
                    { #if $idk }
                        <button class="btn btn-danger" on:click={ ejectIDK } title="eject IDK">
                            <i class="fa-solid fa-eject"></i>
                        </button>
                        <button class="btn btn-secondary ml-3" on:click={ exportIDK } title="export IDK">
                            <i class="fa-solid fa-download"></i>
                        </button>
                    { :else }
                        <button class="btn btn-secondary" on:click={ importIDK } title="import IDK">
                            <i class="fa-solid fa-upload"></i>
                        </button>
                    { /if }
                </div>
            </div>
        </fieldset>
    </Form>

    <br>

    <Table entityType='Sessions' controls bind:api={ loginTable } bind:records={ loginTableRecords } on:entry-action={ onLoginTableEntryAction } on:bulk-action={ onLoginTableBulkAction } selectable dropdown>
        <div slot="selection-controls">
            <button type="button" class="btn btn-sm btn-danger bulk-action" data-action="delete" title="disconnect client">
                <i class="fa-solid fa-sign-out-alt"></i>
            </button>
        </div>
    </Table>

    <br>

    <Table entityType='Trusted Devices' controls bind:api={ trustedDeviceTable } bind:records={ trustedDeviceTableRecords } on:entry-action={ onTrustedDeviceTableEntryAction } on:bulk-action={ onTrustedDeviceTableBulkAction } on:record.field.change={ onTrustedDeviceTableRecordFieldChange } selectable dropdown>
        <div slot="selection-controls">
            <button type="button" class="btn btn-sm btn-danger bulk-action" data-action="delete" title="remove device">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>
    </Table>

    <br>

    <Table entityType='Personal Tokens' controls bind:api={ personalTokenTable } bind:records={ personalTokenTableRecords } on:entry-action={ onPersonalTokenTableEntryAction } on:bulk-action={ onPersonalTokenTableBulkAction } selectable>
        <div slot="fixed-controls">
            <button type="button" class="btn btn-sm btn-primary" title="add" on:click={ () => { personalTokenEntity.displayNewRecordForm() } }>
                <i class="fa-solid fa-plus"></i>
            </button>
        </div>
        <div slot="selection-controls">
            <button type="button" class="btn btn-sm btn-danger bulk-action" data-action="delete" title="remove">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>
    </Table>
</Modal>

<Modal id="workspace_modal" title="Workspace" bind:api={ workspaceModal } urlkey="workspace">
    <Form id="change_workspace_form" bind:api={ changeWorkspaceForm } on:submit={ onChangeWorkspaceFormSubmit }>
        <fieldset class="fieldset">
            <legend>Name</legend>
            <div class="row">
                <div class="col d-flex align-items-center">
                    <input type="text" class="form-control input form-input" name="name" value="{ $user['tenant']['name'] }" data-required>

                    { #if $user['hierarchy'] === 1 }
                        <button type="submit" class="btn btn-primary ml-3">Save</button>
                    { /if }
                </div>
            </div>
        </fieldset>
    </Form>
</Modal>



<Modal id="alert_modal" title="Alert Info" bind:api={ alertModal }>
    <PropTable bind:data={ alertData }/>

    <div class="row mt-2">
        <div class="col text-center">
            <button class="btn btn-primary" on:click={ () => { markAlertAsRead( [ alertId ] ); } }>Mark as read</button>
        </div>
    </div>
</Modal>



<Modal title="Personal Token" bind:api={ personalTokenModal } width="900px">
    <Form bind:api={ personalTokenForm } on:submit={ onPersonalTokenFormSubmit }>
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
                    <textarea class="form-control input form-input" name="description" data-type="string"></textarea>
                </label>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col text-center">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </div>
    </Form>
</Modal>



<style>

    .brand-box
    {
        width: 208px;
    }

    .app-logo
    {
        display: table;
        height: 50px;
        border-radius: 4px;
    }

    .navbar
    {
        /*margin-left: 224px;*/
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        background-color: var( --simba-dark );
    }

    .navbar .nav-link
    {
        color: #ffffff;
    }

    .alert-dropdown .dropdown-list-inner
    {
        height: 540px;
        overflow-y: auto;
    }

    .user-menu-button
    {
        background: var( --simba-dark-bg );
        padding: 4px 10px;
        border-radius: 4px;
    }

</style>