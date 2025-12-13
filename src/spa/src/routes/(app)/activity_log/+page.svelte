<svelte:head>
    <title>{ title }</title>
</svelte:head>

<script>

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

    import { onMount } from 'svelte';



    let title = '';



    const client = new Client();



    const entity = client.entity( 'Activity' );

    Entity.requestToken = $user['request_token'];



    let table;

    $:
        if ( table )
        {// Value found
            // (Setting the table)
            entity.setTable( table );
        }



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
        $appData = body;



        // (Setting the value)
        records = [];

        for ( const record of $appData['elements'] )
        {// Processing each entry
            // (Getting the value)
            const r =
            {
                'id':             record['id'],

                'values':
                [
                    {
                        'column': 'id',
                        'value':  record['id'],

                        'shrink': true
                    },

                    {
                        'column':  'user',
                        'value':   record['ref']['user']['name'],

                        'content': `<a class="resource-object" href="/users?id=${ record['user'] }" target="_blank">${ record['ref']['user']['name'] }</a>`,

                        'shrink':  true
                    },

                    {
                        'column': 'action',
                        'value':  record['action']
                    },

                    {
                        'column':  'description',
                        'value':   record['description'],

                        'content': record['description']
                    },

                    {
                        'column': 'ip',
                        'value':  [ record['ip'], record['ip_info']['country']['code'], record['ip_info']['isp'] ].join(' - ')
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
                        'column': 'resource.type',
                        'value':  record['resource']['type'],

                        'shrink': true
                    },

                    {
                        'column': 'resource.action',
                        'value':  record['resource']['action'],

                        'shrink': true
                    },

                    {
                        'column': 'datetime.insert',
                        'value':  record['datetime']['insert'] ? Time.toLocal( record['datetime']['insert'] ) : ''
                    },
                ],

                'controls': '',

                'hidden': false
            }
            ;



            // (Appending the value)
            records.push( r );
        }

        // (Getting the value)
        records = records;



        // debug
        //console.debug( 'input', input['cursor'] );



        // (Getting the value)
        const data =
        {
            'length': $appData['length'],
            'cursor': $appData['cursor']
        }
        ;



        // debug
        //console.debug( 'output', data['cursor'] );



        // Returning the value
        return data;
    }



    // (Listening for the event)
    onMount
    (
        async function ()
        {
            // (Opening the cursor)
            await table.openCursor();
        }
    )
    ;



    let pageLength;

    let aliases =
    {
        'user':    'ref.user.name',
        'browser': 'ua_info.browser',
        'os':      'ua_info.os',
        'hw':      'ua_info.hw'
    }
    ;

</script>

<Table
    entityType={ entity.name }
    bind:title={ title }
    bind:api={ table }
    bind:records={ records }
    controls
    fixedHeader
    paginator
    bind:pageLength={ pageLength }
    pageList={ subject.list }
    aliases={ aliases }
/>