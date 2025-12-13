<svelte:window on:resize={ onResize } on:keydown={ onKeyDown }/>

<script>

    import { browser } from '$app/environment';

    import { onMount, tick } from 'svelte';

    import { createEventDispatcher } from 'svelte';

    import NoData from '@/views/components/NoData.svelte';



    const dispatch = createEventDispatcher();



    export let entityType = '';
    export let resourceLink = '';

    export let title        = null;

    export let input        = null;
    export let required     = null;

    export let controls     = false;

    export let minimal      = false;

    export let fixedHeader  = false;

    export let dropdown     = false;

    export let aliases      = {};



    export let paginator    = false;

    export let pageLengthOptions = [ 5, 10, 25, 50, 100, 200, 500 ];
    export let pageLength        = pageLengthOptions[2];
    export let pagePosition      = 0;
    export let pageGlobalSearch  = '';
    export let pageLocalSearch   = [];
    export let pageSort          = 'ASC';
    export let pageSortField     = null;

    export let length = 1;

    export let cursor = { 'lastId': 0, 'lastSortValue': null };

    export let pageList = async function () {};



    let cursorHistory = [ cursor ];

    let nextCursor = null;

    let pageHistory = [];



    let sortValueHistory = [];
    let nextSortValue    = null;



    let dropdownOpen = dropdown ? false : true;



    onMount
    (
        function ()
        {
            // (Listening for the event)
            jQuery(element).delegate('.action-input[data-action]', 'click', function (event) {
                // (Getting the value)
                const rowElement = this.closest('tr');

                // (Triggering the event)
                dispatch
                (
                    'record.action',
                    {
                        'action':     this.getAttribute('data-action'),
                        'id':         rowElement.getAttribute('data-id'),

                        'element':    this,
                        'rowElement': rowElement,

                        'index':      parseInt( rowElement.getAttribute('data-index') )
                    }
                )
                ;



                // (Getting the value)
                const index = parseInt( rowElement.getAttribute('data-index') );



                // (Getting the value)
                const object =
                {
                    'type':       this.getAttribute('data-action'),
                    'index':      index,
                    'id':         records[ index ].id,
                    'element':    this,
                    'rowElement': rowElement,
                }
                ;

                // (Triggering the event)
                dispatch( 'entry-action', object );
            });

            // (Listening for the event)
            jQuery(element).delegate('.selection-controls-box .bulk-action', 'click', function (event) {
                // (Setting the property)
                jQuery(element).find('.table .selection .table-input[value="all"]').prop( 'checked', false );



                // (Iterating each entry)
                element.querySelectorAll('.table tbody .selectable .table-input').forEach
                (
                    function (el)
                    {
                        // (Setting the property)
                        el.checked = false;
                    }
                )
                ;



                // (Getting the value)
                api.numSelectedRecords = api.fetchSelectedRecords().length;



                // (Triggering the event)
                dispatch( 'bulk-action', { 'type': this.getAttribute('data-action'), 'selection': api.getSelectedIndexes() } );



                for ( const i in records )
                {// Processing each entry
                    // (Setting the value)
                    records[i].selected = false;   
                }
            });

            // (Listening for the event)
            jQuery(element).delegate(`tbody tr .input.input-text`, 'blur', function (event) {
                // (Getting the value)
                const rowElement = this.closest('tr');



                // (Getting the values)
                const rowIndex    = parseInt( rowElement.getAttribute('data-index') );
                const columnIndex = jQuery( this.closest('td') ).index();



                // (Triggering the event)
                dispatch
                (
                    'record.field.change',
                    {
                        'id':          rowElement.getAttribute('data-id'),

                        'element':     this,
                        'rowElement':  rowElement,

                        'rowIndex':    rowIndex,
                        'columnIndex': columnIndex,

                        'key':         records[ rowIndex ].values[ columnIndex ].column,
                        'value':       this.value
                    }
                )
                ;
            });



            // (Getting the value)
            element.api =
            {
                'transformRecord': null,

                'listRecords': function ()
                {
                    // Returning the value
                    return element.api.transformRecord ? records.map( element.api.transformRecord ) : records;
                },

                

                'listIds': function ()
                {
                    // Returning the value
                    return records.map( function (record) { return record.id; } );
                },



                'emptyRecords': function ()
                {
                    // (Setting the value)
                    records = [];
                },

                'setRecords': function (value)
                {
                    // (Getting the value)
                    records = value;
                },



                'reset': function ()
                {
                    for ( const i in records )
                    {// Processing each entry
                        // (Setting the value)
                        records[i].selected = false;
                    }
                }
            }
            ;



            if ( input )
            {// Value found
                // (Setting the class name)
                element.classList.add('form-input');
            }



            // (Getting the value)
            api.element = element;
        }
    )
    ;



    let element;



    let sortColumn  = null;
    let sortReverse = false;



    export let records = [];

    export let selectable = false;



    export let api = {};



    // (Setting the values)
    api.columns            = [];
    api.fixedColumns       = [];

    api.useKeys            = false;
    api.keys               = {};

    api.filterEnabled      = false;
    api.activeFilter       = null;
    api.lastFilter         = null;
    api.numSelectedRecords = 0;



    // Returns [void]
    api.setColumns = function (columns)
    {
        // (Getting the value)
        api.columns = columns;
    }



    // Returns [number|false]
    api.getColumnIndex = function (column)
    {
        // (Setting the value)
        let index = false;

        for ( const i in records[0].values )
        {// Iterating each index
            if ( records[0].values[i].column === column )
            {// Match OK
                // (Getting the value)
                index = i;

                // Breaking the iteration
                break;
            }
        }



        // Returning the value
        return index;
    }

    // Returns [Array<string>]
    api.getColumnValues = function (column, filtered)
    {
        if ( typeof filtered === 'undefined' ) filtered = false;



        // Returning the value
        return [ ...new Set( records.filter( function (record) { return filtered ? !record.hidden : true; } ).map( function (record) { return record.values[ api.getColumnIndex(column) ].value; } ) ) ];
    }



    // Returns [object|null]
    api.getField = function (recordIndex, column)
    {
        // Returning the value
        return records[ recordIndex ] ? records[ recordIndex ].values[ api.getColumnIndex( column ) ] : null;
    }



    // Returns [Promise:void]
    api.sort = async function (column, reverse)
    {
        if ( typeof reverse === 'undefined' ) reverse = sortReverse;



        // (Getting the value)
        const index = api.getColumnIndex( column );



        if ( paginator )
        {// Value found
            // (Getting the values)
            pageSort      = reverse ? 'DESC' : 'ASC';
            pageSortField = aliases[ column ] ?? column;



            // (Getting the value)
            //const lastRecordField = api.getField( records.length - 1, column );



            // (Setting the value)
            pagePosition = 0;



            // (Getting the value)
            cursor =
            {
                'lastId':        0,
                'lastSortValue': null
            }
            ;



            // (Getting the value)
            pageHistory = [ cursor ];



            // (Calling the function)
            const data = await pageList
            (
                {
                    'length':       pageLength,
                    'cursor':       cursor,
                    'globalSearch': pageGlobalSearch,
                    'localSearch':  pageLocalSearch,
                    'sort':         pageSort,
                    'sortField':    pageSortField
                }
            )
            ;



            // (Setting the length)
            api.setLength( data['length'] );



            // (Setting the next cursor)
            api.setNextCursor( data['cursor'] );
        }
        else
        {// Value not found
            // (Sorting the array)
            records.sort
            (
                function (a, b)
                {
                    if ( a.values[index].value < b.values[index].value )
                    {// (A is lesser than B)
                        // Returning the value
                        return reverse ? 1 : -1;
                    }
                    else
                    if ( a.values[index].value > b.values[index].value )
                    {// (A is greater than B)
                        // Returning the value
                        return reverse ? -1 : 1;
                    }



                    // Returning the value
                    return 0;
                }
            )
            ;



            // (Getting the value)
            records = records;
        }



        // (Setting the value)
        sortColumn = column;

        // (Getting the value)
        sortReverse = !sortReverse;
    }

    // Returns [string|null]
    api.getSortColumn = function ()
    {
        // Returning the value
        return sortColumn;
    }



    // Returns [object]
    api.getSearchValues = function ()
    {
        // (Getting the value)
        const result =
        {
            'global': element.querySelector('.search-box .table-input')?.value,
            'local': {},
            'keys':  {}
        }
        ;



        // (Iterating each entry)
        element.querySelectorAll('.jtable thead tr th[data-column]').forEach
        (
            function (el)
            {
                // (Getting the values)
                result.local[ el.getAttribute('data-column') ] = el.querySelector('.table-input').value;
                result.keys[ el.getAttribute('data-column') ]  = api.useKeys ? api.keys[ el.getAttribute('data-column') ].entries.filter( function (entry) { return entry.checked && !entry.hidden; } ).map( function (entry) { return entry.value; } ) : [];
            }
        )
        ;



        // Returning the value
        return result;
    }

    // Returns [void]
    api.setSearchValues = function (searchValues)
    {
        // (Getting the value)
        element.querySelector('.search-box .table-input').value = searchValues.global;



        // (Iterating each entry)
        element.querySelectorAll('.jtable thead tr th[data-column]').forEach
        (
            function (el)
            {
                // (Getting the value)
                const column = el.getAttribute('data-column');



                // (Getting the value)
                el.querySelector('.table-input').value = searchValues.local[ column ];

                if ( api.useKeys )
                {// Value is true
                    for ( const i in api.keys[ column ].entries )
                    {// Processing each entry
                        if ( searchValues.keys[ column ].includes( api.keys[ column ].entries[i].value ) )
                        {// Match OK
                            // (Setting the properties)
                            api.keys[ column ].entries[i].checked = true;
                            api.keys[ column ].entries[i].hidden  = false;
                        }
                    }
                }
            }
        )
        ;
    }



    // Returns [void]
    api.filter = function (fn)
    {
        if ( selectable )
        {// Value is true
            // (Deselecting the records)
            api.deselectRecords();
        }



        switch ( fn )
        {
            case 'SEARCH_GLOBAL':// (OR of values)
                // (Getting the value)
                const searchValue = api.getSearchValues().global;

                for ( const i in records )
                {// Iterating each index
                    // (Setting the value)
                    let resultFound = false;

                    for ( const column of records[i].values )
                    {// Processing each entry
                        // (Getting the value)
                        const value = column.value ?? '';

                        if ( value.toString().toLowerCase().indexOf( searchValue.toLowerCase() ) !== -1 )
                        {// Match OK
                            // (Setting the value)
                            resultFound = true;

                            // Breaking the iteration
                            break;
                        }
                    }



                    // (Getting the value)
                    records[i].hidden = !resultFound;
                }
            break;

            case 'SEARCH_LOCAL':// (AND of values)
                // (Getting the value)
                const values = api.getSearchValues().local;

                for ( const i in records )
                {// Processing each entry
                    // (Setting the value)
                    let resultFound = true;

                    for ( const k in values )
                    {// Processing each entry
                        // (Getting the value)
                        const value = records[i].values[ api.getColumnIndex(k) ].value ?? '';

                        if ( value.toString().toLowerCase().indexOf( values[k].toString().toLowerCase() ) === -1 )
                        {// Match failed
                            // (Setting the value)
                            resultFound = false;

                            // Breaking the iteration
                            break;
                        }
                    }



                    // (Getting the value)
                    records[i].hidden = !resultFound;
                }
            break;

            case 'SEARCH_KEYS':// (OR of keys)
                // (Getting the value)
                const keys = api.getSearchValues().keys;

                for ( const i in records )
                {// Processing each entry
                    // (Setting the value)
                    let resultFound = true;

                    for ( const k in records[i].values )
                    {// Processing each entry
                        // (Getting the values)
                        const column = records[i].values[k].column;
                        const value  = records[i].values[k].value ?? '';

                        if ( keys[column].length > 0 && !keys[column].includes( value.toString() ) )
                        {// Match failed
                            // (Setting the value)
                            resultFound = false;

                            // Breaking the iteration
                            break;
                        }
                    }



                    // (Getting the value)
                    records[i].hidden = !resultFound;
                }
            break;

            default:
                // (Calling the function)
                fn();
        }
    }



    // Returns [object]
    api.extractKeys = function ()
    {
        // (Setting the value)
        const keys = {};



        if ( records.length === 0 ) return;



        for ( const i in records[0].values )
        {// Processing each entry
            // (Getting the value)
            const column = records[0].values[i].column;

            // (Getting the value)
            keys[ column ] =
            {
                'menuOpen':     false,
                'filterActive': false,

                'entries':      api.getColumnValues( column ).map( function (entry) { return { 'value': entry, 'checked': false, 'hidden': false }; } )
            }
            ;
        }



        // Returning the value
        return keys;
    }



    // Returns [void]
    api.resetFilter = function ()
    {
        for ( const i in records )
        {// Processing each entry
            // (Setting the value)
            records[i].hidden = false;
        }

        for ( const column in api.keys )
        {// Processing each entry
            for ( const i in api.keys[column].entries )
            {// Processing each entry
                // (Setting the values)
                api.keys[column].entries[i].checked = false;
                api.keys[column].entries[i].hidden  = false;
            }
        }



        // (Setting the value)
        element.querySelector('.search-box .table-input').value = '';

        // (Iterating each entry)
        element.querySelectorAll('.column-search-box .table-input').forEach
        (
            function (el)
            {
                // (Setting the value)
                el.value = '';
            }
        )
        ;

        // (Iterating each entry)
        element.querySelectorAll('.column-key-search-box .key-list .table-input[value="all"]').forEach
        (
            function (el)
            {
                // (Setting the value)
                el.checked = false;
            }
        )
        ;
    }

    // Returns [void]
    api.applyFilter = function (filter)
    {
        // (Setting the search values)
        api.setSearchValues( filter );
    }



    // Returns [Promise:void]
    api.saveFilter = async function ()
    {
        // (Getting the value)
        api.lastFilter = api.getSearchValues();



        // (Resetting the filter)
        api.resetFilter();



        if ( paginator )
        {// Value is true
            // (Setting the values)
            pageGlobalSearch = '';
            pageLocalSearch  = {};



            // (Setting the value)
            pagePosition = 0;



            // (Setting the value)
            cursor =
            {
                'lastId':        0,
                'lastSortValue': null
            }
            ;



            // (Getting the value)
            pageHistory = [ cursor ];



            // (Calling the function)
            const data = await pageList
            (
                {
                    'length':       pageLength,
                    'cursor':       cursor,
                    'globalSearch': pageGlobalSearch,
                    'localSearch':  pageLocalSearch,
                    'sort':         pageSort,
                    'sortField':    pageSortField
                }
            )
            ;



            // (Setting the length)
            api.setLength( data['length'] );



            // (Setting the next cursor)
            api.setNextCursor( data['cursor'] );
        }



        // (Getting the value)
        api.filterEnabled = !api.filterEnabled;
    }

    // Returns [Promise:void]
    api.restoreFilter = async function ()
    {
        if ( !api.lastFilter ) return;



        // (Applying the filter)
        api.applyFilter( api.lastFilter );



        if ( paginator )
        {// Value is true
            // (Getting the value)
            const searchValues = api.getSearchValues();



            // (Getting the value)
            pageGlobalSearch = searchValues.global;



            for ( const k in searchValues.local )
            {// Processing each entry
                if ( searchValues.local[k] === '' )
                {// Match OK
                    // (Deleting the value)
                    delete searchValues.local[k];
                }
            }



            // (Getting the value)
            pageLocalSearch = api.resolveAliases( searchValues.local );



            // (Calling the function)
            const data = await pageList
            (
                {
                    'length':       pageLength,
                    'cursor':       cursor,
                    'globalSearch': pageGlobalSearch,
                    'localSearch':  pageLocalSearch,
                    'sort':         pageSort,
                    'sortField':    pageSortField
                }
            )
            ;



            // (Setting the length)
            api.setLength( data['length'] );



            // (Setting the next cursor)
            api.setNextCursor( data['cursor'] );
        }
        else
        {// Value is false
            // (Filtering the table)
            api.filter( api.activeFilter );
        }

        

        // (Getting the value)
        api.filterEnabled = !api.filterEnabled;
    }



    // Returns [string]
    api.buildCSV = function (columns, columnSeparator, rowSeparator, enclosure, escape)
    {
        if ( records.length === 0 ) return '';



        if ( typeof columns === 'undefined' ) columns = records[0].values.map( function (entry) { return entry.column; } );
        if ( typeof columnSeparator === 'undefined' ) columnSeparator = ';'
        if ( typeof rowSeparator === 'undefined' ) rowSeparator = "\n";
        if ( typeof enclosure === 'undefined' ) enclosure = '"';
        if ( typeof escape === 'undefined' ) escape = '\\';



        // (Setting the value)
        let lines = [];



        // (Setting the value)
        let schema = [];

        for ( const column of columns )
        {// Processing each entry
            // (Appending the value)
            schema.push( /\s/.test( column ) ? `${ enclosure }${ column.replace( new RegExp( enclosure ), escape + enclosure ) }${ enclosure }` : column );
        }

        // (Getting the value)
        schema = schema.join( columnSeparator );




        // (Appending the value)
        lines.push( schema );



        for ( const i in records )
        {// Processing each entry
            if ( records[i].hidden ) continue;



            let cols = [];

            for ( const column of columns )
            {// Processing each entry
                for ( const j in records[i].values )
                {// Processing each entry
                    if ( records[i].values[j].column === column )
                    {// Match OK
                        // (Getting the value)
                        const value = records[i].values[j].value;

                        // (Appending the value)
                        cols.push( /\s/.test( value ) ? `${ enclosure }${ value.replace( new RegExp( enclosure ), escape + enclosure ) }${ enclosure }` : value );

                        // Breaking the iteration
                        break;
                    }
                }
            }



            // (Appending the value)
            //lines.push( records[i].values.map( function (entry) { return /\s/.test( entry.value ) ? `${ enclosure }${ entry.value.replace( new RegExp( enclosure ), escape + enclosure ) }${ enclosure }` : entry.value; } ).join( columnSeparator ) );
            lines.push( cols.join( columnSeparator ) );
        }



        // Returning the value
        return lines.join( rowSeparator );
    }

    // Returns [void]
    api.downloadCSV_OLD = function (filename)
    {
        if ( typeof filename === 'undefined' ) filename = 'export.csv';



        // (Getting the values)
        const blob = new Blob( [ api.buildCSV() ], { 'type': 'application/csv' } );
        const url  = window.URL.createObjectURL( blob );



        // (Creating an element)
        const a = document.createElement('a');

        // (Getting the values)
        a.href     = url;
        a.download = filename;



        // (Appending the child)
        document.body.appendChild(a);



        // (Triggering the event)
        a.click();

        // (Removing the element)
        a.remove();



        // (Revoking the url object)
        window.URL.revokeObjectURL(url);
    }

    // Returns [void]
    api.downloadCSV = function (filename)
    {
        if ( typeof filename === 'undefined' ) filename = 'export.csv';



        // (Creating an element)
        const a = document.createElement('a');

        // (Getting the values)
        a.href     = `data:application/csv;base64,${ btoa( api.buildCSV() ) }`;
        a.download = filename;



        // (Triggering the event)
        a.click();

        // (Removing the element)
        a.remove();
    }



    // Returns [Array<number>]
    api.getSelectedIndexes = function ()
    {
        // (Setting the value)
        const selectedIndexes = [];

        for ( let i = 0; i < records.length; i++ )
        {// Iterating each index
            if ( records[i].selected )
            {// Value is true
                // (Appending the value)
                selectedIndexes.push( i );
            }
        }



        // Returning the value
        return selectedIndexes;
    }

    // Returns [Array<number>]
    api.getSelectedIds = function ()
    {
        // (Setting the value)
        const selectedIds = [];

        for ( const index of api.getSelectedIndexes() )
        {// Iterating each index
            if ( records[ index ].selected )
            {// Value is true
                // (Appending the value)
                selectedIds.push( records[ index ].id );
            }
        }



        // Returning the value
        return selectedIds;
    }

    // Returns [Array<number>]
    api.fetchSelectedRecords = function ()
    {
        // (Setting the value)
        let recordsIds = [];

        // (Iterating each entry)
        element.querySelectorAll('.table tbody .selectable .table-input').forEach
        (
            function (el)
            {
                if ( el.checked )
                {// Value is true
                    // (Appending the value)
                    recordsIds.push( parseInt( el.closest('tr').getAttribute('data-index') ) );
                }
            }
        )
        ;



        // Returning the value
        return recordsIds;
    }

    // Returns [void]
    api.deselectRecords = function ()
    {
        for ( const i in records )
        {// Processing each entry
            // (Setting the value)
            records[i].selected = false;   
        }



        // (Setting the property)
        element.querySelector('.table .selection .table-input[value="all"]').checked = false;

        // (Iterating each entry)
        element.querySelectorAll('.table .selectable .table-input[value="select"]').forEach
        (
            function (el)
            {
                // (Setting the property)
                el.checked = false;
            }
        )
        ;



        // (Setting the value)
        api.numSelectedRecords = 0;
    }



    // Returns [object]
    api.getValuesByIndex = function (index)
    {
        // (Setting the value)
        const values = {};

        for ( const column of records[index]['values'] )
        {// Processing each entry
            // (Getting the value)
            values[ column['column'] ] = column['value'];
        }



        // Returning the value
        return values;
    }

    // Returns [object]
    api.getValuesByRecord = function (record)
    {
        // (Setting the value)
        const values = {};

        for ( const column of record['values'] )
        {// Processing each entry
            // (Getting the value)
            values[ column['column'] ] = column['value'];
        }



        // Returning the value
        return values;
    }

    // Returns [void]
    api.removeRecordByIndex = function (index)
    {
        // (Getting the value)
        records.splice( index, 1 );

        // (Getting the value)
        records = records;
    }

    // Returns [void]
    api.removeRecordById = function (id)
    {
        // (Setting the value)
        let index = null;

        for ( const i in records )
        {// Processing each entry
            if ( records[i].id === id )
            {// Match OK
                // (Getting the value)
                index = i;

                // Breaking the iteration
                break;
            }
        }



        if ( !index ) return;



        // (Getting the value)
        records.splice( index, 1 );

        // (Getting the value)
        records = records;
    }

    // Returns [object|false]
    api.getRecordById = function (id)
    {
        for ( const i in records )
        {// Processing each entry
            if ( records[i].id === id )
            {// Match OK
                // Returning the value
                return records[i];
            }
        }



        // Returning the value
        return false;
    }

    // Returns [object]
    api.getIdMap = function (ids)
    {
        // (Setting the value)
        const idMap = {};

        for ( const i in records )
        {// Processing each entry
            // (Getting the value)
            const id = records[i].id;

            if ( ids instanceof Array && ids.length > 0 )
            {// Value is not empty
                if ( !ids.includes( id ) ) continue;
            }
    


            // (Getting the value)
            idMap[ id ] = i;
        }



        // Returning the value
        return idMap;
    }



    // Returns [void]
    api.removeRecord = function (index)
    {
        // (Getting the value)
        records.splice( index, 1 );

        // (Getting the value)
        records = records;
    }

    // Returns [void]
    api.removeRecords = function (filter)
    {
        // (Setting the value)
        const targetRecords = [];

        for ( const i in records )
        {// Processing each entry
            if ( !filter( records[ i ], parseInt( i ) ) )
            {// Match OK
                // (Appending the value)
                targetRecords.push( records[ i ] );
            }
        }



        // (Getting the value)
        records = targetRecords;
    }

    // Returns [void]
    api.removeRecordsByIndexes = function (indexes)
    {
        // (Removing the records)
        api.removeRecords( function (r, i) { return indexes.includes( i ); } );
    }

    // Returns [void]
    api.removeRecordsByIds = function (ids)
    {
        // (Removing the records)
        api.removeRecords( function (r) { return ids.includes( r.id ); } );
    }



    // Returns [void]
    api.setLength = function (value)
    {
        // (Getting the value)
        length = value;
    }



    // Returns [object]
    api.resolveAliases = function (localSearch)
    {
        // (Setting the value)
        const results = {};

        for ( const k in localSearch )
        {// Processing each entry
            // (Getting the value)
            const field = aliases[k] ?? k;



            // (Getting the value)
            results[ field ] = localSearch[ k ];
        }



        // Returning the value
        return results;
    }

    // Returns [Array<string>]
    api.listFields = function ()
    {
        // Returning the value
        return records.length > 0 ? records[0].values.map( function (entry) { return entry.column; } ) : [];
    }



    // Returns [void]
    api.pushCursor = function (cursor)
    {
        // (Appending the value)
        pageHistory.push( cursor );
    }

    // Returns [void]
    api.setNextCursor = function (cursor)
    {
        // (Getting the value)
        nextCursor = cursor;
    }



    // Returns [Promise:void]
    api.openCursor = async function ()
    {
        // (Setting the value)
        cursor =
        {
            'lastId':        0,
            'lastSortValue': null
        }
        ;



        // (Pushing the cursor)
        api.pushCursor( cursor );



        // (Listing records)
        const data = await pageList
        (
            {
                'length': pageLength,
                'cursor': cursor
            }
        )
        ;



        if ( !data ) return;



        // (Setting the length)
        api.setLength( data['length'] );



        // (Setting the next cursor)
        api.setNextCursor( data['cursor'] );
    }



    let searchTimeout = null;



    // Returns [Promise:void]
    async function onGlobalSearch (event)
    {
        // (Setting the value)
        api.activeFilter = 'SEARCH_GLOBAL';

        // (Getting the value)
        api.filterEnabled = event.target.value.length > 0;



        if ( paginator )
        {// (Value is true)
            // (Setting the value)
            pagePosition = 0;



            // (Getting the value)
            pageGlobalSearch = event.target.value;



            // (Setting the value)
            cursor =
            {
                'lastId':        0,
                'lastSortValue': null
            }
            ;



            // (Getting the value)
            pageHistory = [ cursor ];



            // (Clearing the timeout)
            clearTimeout( searchTimeout );

            // (Setting the timeout)
            searchTimeout = setTimeout
            (
                async function ()
                {
                    // (Calling the function)
                    const data = await pageList
                    (
                        {
                            'length':       pageLength,
                            'cursor':       cursor,
                            'globalSearch': pageGlobalSearch,
                            'sort':         pageSort,
                            'sortField':    pageSortField
                        }
                    )
                    ;



                    // (Setting the length)
                    api.setLength( data['length'] );



                    // (Setting the next cursor)
                    api.setNextCursor( data['cursor'] );
                },

                300
            )
            ;
        }
        else
        {// (Value is false)
            // (Filtering the records)
            api.filter( api.activeFilter );
        }
    }

    // Returns [Promise:void]
    async function onLocalSearch (event)
    {
        // (Getting the value)
        const searchValues = api.getSearchValues();



        // (Getting the values)
        const globalFilter = searchValues.global.length > 0;
        const localFilter  = Object.values( searchValues.local ).filter( function (value) { return value.length > 0; } ).length > 0;



        // (Getting the value)
        api.activeFilter = localFilter ? 'SEARCH_LOCAL' : ( globalFilter ? 'SEARCH_GLOBAL' : null );



        // (Getting the value)
        api.filterEnabled = localFilter || globalFilter;



        if ( paginator )
        {// Value found
            // (Getting the value)
            const values = api.getSearchValues().local;

            for ( const k in values )
            {// Processing each entry
                if ( values[k] === '' )
                {// Match OK
                    // (Deleting the value)
                    delete values[k];
                }
            }



            // (Setting the value)
            pagePosition = 0;



            // (Getting the value)
            pageLocalSearch = api.resolveAliases( values );



            // (Setting the value)
            cursor =
            {
                'lastId':        0,
                'lastSortValue': null
            }
            ;



            // (Getting the value)
            pageHistory = [ cursor ];



            // (Clearing the timeout)
            clearTimeout( searchTimeout );

            // (Setting the timeout)
            searchTimeout = setTimeout
            (
                async function ()
                {
                    // (Calling the function)
                    const data = await pageList
                    (
                        {
                            'length':       pageLength,
                            'cursor':       cursor,
                            'globalSearch': pageGlobalSearch,
                            'localSearch':  pageLocalSearch,
                            'sort':         pageSort,
                            'sortField':    pageSortField
                        }
                    )
                    ;



                    // (Setting the length)
                    api.setLength( data['length'] );



                    // (Setting the next cursor)
                    api.setNextCursor( data['cursor'] );
                },

                300
            )
            ;
        }
        else
        {// Value not found
            // (Filtering the records)
            api.filter( api.activeFilter );
        }
    }



    /*

    // Returns [void]
    function onDataChange (rr)
    {
        // (Triggering the event)
        dispatch('datachange');
    }



    $:
        // (Calling the function)
        onDataChange(records);

    */
    
    
    
    // Returns [void]
    function onKeySearchMenuBtnClick (column)
    {
        // (Getting the value)
        api.keys[ column ].menuOpen = !api.keys[ column ].menuOpen;
    }



    // Returns [void]
    function onKeySelect (event, column, value)
    {
        for ( const i in api.keys[ column ].entries )
        {// Processing each entry
            if ( api.keys[ column ].entries[i].value === value )
            {// Value found
                // (Getting the value)
                api.keys[ column ].entries[i].checked = event.target.checked;
            }
        }



        // (Setting the value)
        let filterEnabled = false;

        loop: for ( const column in api.keys )
        {// Processing each entry
            for ( const i in api.keys[ column ].entries )
            {// Processing each entry
                if ( api.keys[ column ].entries[i].checked )
                {// Value is true
                    // (Setting the value)
                    filterEnabled = true;

                    // Breaking the iteration
                    break loop;
                }
            }
        }



        // (Getting the value)
        //api.keys[ column ].filterActive = api.keys[ column ].entries.filter( function (entry) { return entry.checked; } ).length > 0;



        // (Setting the value)
        api.activeFilter = 'SEARCH_KEYS';

        // (Getting the value)
        api.filterEnabled = filterEnabled;



        // (Filtering the records)
        api.filter( api.activeFilter );
    }

    // Returns [void]
    function onKeySelectAll (event, column)
    {
        for ( const i in api.keys[ column ].entries )
        {// Processing each entry
            // (Getting the value)
            api.keys[ column ].entries[i].checked = event.target.checked;
        }



        // (Setting the value)
        let filterEnabled = false;

        loop: for ( const column in api.keys )
        {// Processing each entry
            for ( const i in api.keys[ column ].entries )
            {// Processing each entry
                if ( api.keys[ column ].entries[i].checked )
                {// Value is true
                    // (Setting the value)
                    filterEnabled = true;

                    // Breaking the iteration
                    break loop;
                }
            }
        }



        // (Getting the value)
        //api.keys[ column ].filterActive = api.keys[ column ].entries.filter( function (entry) { return entry.checked; } ).length > 0;



        // (Setting the value)
        api.activeFilter = 'SEARCH_KEYS';

        // (Getting the value)
        api.filterEnabled = filterEnabled;



        // (Filtering the records)
        api.filter( api.activeFilter );
    }



    // Returns [void]
    function extractKeys ()
    {
        // (Getting the value)
        api.useKeys = !api.useKeys;

        if ( api.useKeys )
        {// Value is true
            // (Getting the value)
            api.keys = api.extractKeys();
        }
        else
        {// Value is false
            // (Setting the value)
            api.keys = {};
        }
    }



    // Returns [void]
    function onKeySearch (event, column)
    {
        // (Getting the value)
        const searchValue = event.target.value;

        for ( const i in api.keys[ column ].entries )
        {// Processing each entry
            // (Getting the value)
            api.keys[ column ].entries[i].hidden = api.keys[ column ].entries[i].value.toLowerCase().indexOf( searchValue.toLowerCase() ) === -1;
        }



        if ( api.keys[ column ].entries.filter( function (entry) { return entry.checked && !entry.hidden; } ).length > 0 )
        {// (All of the keys are checked)
            // (Filtering the records)
            api.filter('SEARCH_KEYS');
        }
    }



    /*
    
    $:
        if ( records.length - records.filter( function (record) { return record.hidden; } ).length > 0 )
        {// (There are hidden records)
            // (Setting the value)
            api.filterEnabled = true;
        }
        else
        {// (There are no hidden records)
            // (Setting the value)
            api.filterEnabled = false;
        }

    */



    // Returns [void]
    function toggleSelectAllRecords (event)
    {
        // (Getting the value)
        const checked = event.target.checked;

        for ( const i in records )
        {// Processing each entry
            // (Getting the value)
            records[i].selected = checked;
        }



        // (Iterating each entry)
        element.querySelectorAll('.table tbody .selectable .table-input').forEach
        (
            function (el)
            {
                // (Setting the property)
                el.checked = checked;
            }
        )
        ;



        // (Getting the value)
        api.numSelectedRecords = api.fetchSelectedRecords().length;



        // (Triggering the event)
        dispatch( 'selection.change' );
    }

    // Returns [void]
    function onSelectionChange (event)
    {
        // (Getting the value)
        const index = parseInt( event.target.closest( 'tr' ).getAttribute( 'data-index' ) );



        // (Getting the value)
        records[ index ].selected = event.target.checked;



        // (Getting the value)
        api.numSelectedRecords = api.fetchSelectedRecords().length;

        // (Triggering the event)
        dispatch
        (
            'selection.change',
            {
                'element': event.target.closest('tr')
            }
        )
        ;
    }



    $:
        if ( entityType )
        {// Value found
            // (Getting the value)
            title = `${ entityType } (${ paginator ? length : records.length })`;
        }
    
    $:
        if ( records )
        {// Value found
            // (Getting the value)
            title = `${ entityType } (${ paginator ? length : records.length })`;
        }



    let theadElement;

    // Returns [void]
    function setMaxHeight ()
    {
        // (Getting the value)
        const containerFluidElement = document.querySelector('.container-fluid');

        // (Setting the style)
        theadElement.closest( '.table-container' ).style.maxHeight = `${ Math.floor( ( containerFluidElement ? document.querySelector('.container-fluid').offsetHeight : window.innerHeight - 1 ) - theadElement.getBoundingClientRect().top - 1 ) }px`;
    }

    $:
        if ( theadElement && fixedHeader )
        {// Match OK
            // (Setting the max height)
            setMaxHeight();
        }
    
    

    // Returns [void]
    function onResize ()
    {
        if ( theadElement && fixedHeader )
        {// Match OK
            // (Setting the max height)
            setMaxHeight();
        }
    }



    // Returns [Promise:void]
    async function onPageLengthChange (value)
    {
        // (Getting the value)
        pageLength = value;



        // (Calling the function)
        const data = await pageList
        (
            {
                'length':       pageLength,
                'cursor':       cursor,
                'globalSearch': pageGlobalSearch,
                'localSearch':  pageLocalSearch,
                'sort':         pageSort,
                'sortField':    pageSortField
            }
        )
        ;



        // (Setting the length)
        api.setLength( data['length'] );



        // (Setting the next cursor)
        api.setNextCursor( data['cursor'] );
    }



    // Returns [Promise:void]
    async function onPrevPageClick ()
    {
        // (Decrementing the value)
        pagePosition -= pageLength;



        // (Getting the value)
        cursor = pageHistory[ pageHistory.length - 2 ] || { 'lastId': 0, 'lastSortValue': null };

        // (Popping the array)
        pageHistory.pop();



        // debug
        console.debug( 'pageHistory', pageHistory );



        // (Calling the function)
        const data = await pageList
        (
            {
                'length':       pageLength,
                'cursor':       cursor,
                'globalSearch': pageGlobalSearch,
                'localSearch':  pageLocalSearch,
                'sort':         pageSort,
                'sortField':    pageSortField
            }
        )
        ;



        // (Setting the length)
        api.setLength( data['length'] );



        // (Setting the next cursor)
        api.setNextCursor( data['cursor'] );
    }

    // Returns [Promise:void]
    async function onNextPageClick ()
    {
        // (Incrementing the value)
        pagePosition += pageLength;



        // (Getting the value)
        cursor = nextCursor;



        // (Pushing the cursor)
        api.pushCursor( cursor );



        // (Calling the function)
        const data = await pageList
        (
            {
                'length':       pageLength,
                'cursor':       cursor,
                'globalSearch': pageGlobalSearch,
                'localSearch':  pageLocalSearch,
                'sort':         pageSort,
                'sortField':    pageSortField
            }
        )
        ;



        // (Setting the length)
        api.setLength( data['length'] );



        // (Setting the next cursor)
        api.setNextCursor( data['cursor'] );
    }



    // Returns [void]
    function paginatorSearchReset ()
    {
        // ahcid
    }



    // Returns [void]
    function onKeyDown (event)
    {
        if ( document.activeElement.classList.contains('input') ) return;

        if ( document.activeElement.closest('.input') !== null ) return;

        if ( event.ctrlKey || event.metaKey || event.altKey ) return;

        switch ( event.key )
        {
            case '+':
                // (Triggering the event)
                element.querySelector('.btn[data-action="add"]')?.click();
            break;

            case 'r':
                // (Opening the cursor)
                api.openCursor();
            break;
        }
    }



    // Returns [void]
    function onRecordsChange (value)
    {
        // (Getting the value)
        api.columns = api.fixedColumns.length > 0 ? api.fixedColumns : ( value.length > 0 ? value[0].values.map( function (entry) { return { 'name': entry.column, 'hidden': entry.hidden }; } ) : [] );



        if ( paginator )
        {// Value found
            // (Getting the value)
            api.fixedColumns = api.columns;   
        }
    }

    $: onRecordsChange( records );

</script>

<div class="card shadow jtable component table-component" data-input={ input } data-required={ required } bind:this={ element }>
    <div class="card-header py-3 d-flex align-items-center" style="justify-content: space-between;">
        <h6 class="m-0 font-weight-bold text-primary">
            { #if resourceLink }
                <a href={ resourceLink } target="_blank">{ entityType }</a> ({ records.length })
            { :else }
                { entityType } ({ records.length })
            { /if }
        </h6>

        <slot name="fixed-controls"/>

        { #if dropdown }
            <!-- svelte-ignore a11y-click-events-have-key-events -->
            <div class="btn btn-sm btn-secondary btn-dropdown" on:click={ () => { dropdownOpen = !dropdownOpen; } } title="open / close">
                { #if dropdownOpen }
                    <i class="fa-solid fa-caret-up"></i>
                { :else }
                    <i class="fa-solid fa-caret-down"></i>
                { /if }
            </div>
        { /if }
    </div>

    <div class="card-body" class:p-0={ minimal && records.length > 0 } class:d-none={ !dropdownOpen }>
        { #if paginator || ( !paginator && records.length > 0 ) }
            <div class="table-responsive">
                <div class="dataTables_wrapper dt-bootstrap4">
                    { #if !minimal }
                        <div class="row">
                            <div class="col d-flex align-items-center" style="justify-content: space-between;">
                                <div class="controls-left">
                                    <div class="num-results">( <b>{ records.filter( function (record) { return !record.hidden; } ).length }</b> )</div>

                                    <button type="button" class="btn btn-secondary btn-sm ml-3" title="download csv" on:click={ () => { api.downloadCSV(); } }>
                                        <i class="fa-solid fa-download"></i>
                                    </button>

                                    <div class="selection-controls-box d-flex align-items-center ml-5">
                                        { #if element && api.numSelectedRecords > 0 }
                                                <span class="num-results mr-3" style="color: var( --simba-primary );">( <b>{ api.numSelectedRecords }</b> )</span>
                                                <slot name="selection-controls"/>
                                        { /if }
                                    </div>
                                </div>

                                { #if paginator }
                                    <div class="page-controls">
                                        <button type="button" class="btn btn-sm btn-secondary" title="prev" on:click={ onPrevPageClick } disabled={ pagePosition === 0 }>
                                            <i class="fa-solid fa-caret-left"></i>
                                        </button>

                                        <div class="ml-2 mr-2 d-inline-flex align-items-center">
                                            <span class="mr-2" style="white-space: nowrap;">{ pagePosition + 1 }-{ Math.min( pagePosition + pageLength, length ) } of { length }</span>

                                            <select class="form-control form-control-sm">
                                                { #each pageLengthOptions as value }
                                                    <option value={ value } selected={ pageLength === value } on:click={ () => { onPageLengthChange( value ); } }>{ value }</option>
                                                { /each }
                                            </select>
                                        </div>

                                        <button type="button" class="btn btn-sm btn-secondary" title="next" on:click={ onNextPageClick } disabled={ pagePosition + pageLength >= length }>
                                            <i class="fa-solid fa-caret-right"></i>
                                        </button>
                                    </div>
                                { /if }

                                <div class="search-box">
                                    <button type="button" class="btn btn-secondary btn-sm mr-3" title="extract keys" on:click={ extractKeys }>
                                        { #if api.useKeys }
                                            <i class="fa-solid fa-caret-up"></i>
                                        { :else }
                                            <i class="fa-solid fa-caret-down"></i>
                                        { /if }
                                    </button>

                                    { #if api.filterEnabled }
                                        <button type="button" class="btn btn-danger btn-sm" title="remove filter { api.activeFilter }" on:click={ api.saveFilter }>
                                            <i class="fa-solid fa-filter-circle-xmark"></i>

                                            <div class="active-filter-indicator">
                                                <span>{ api.activeFilter?.split('_')[1].charAt(0).toUpperCase() }</span>
                                            </div>
                                        </button>
                                    { :else }
                                        <button type="button" class="btn btn-secondary btn-sm" title="apply filter { api.activeFilter }" on:click={ api.restoreFilter }>
                                            <i class="fa-solid fa-filter"></i>
                                        </button>
                                    { /if }

                                    <input type="text" class="form-control form-control-sm input table-input ml-3" placeholder="Search ..." style="width: 250px;" on:input={ onGlobalSearch }>
                                </div>
                            </div>
                        </div>
                    { /if }

                    { #if paginator || ( !paginator && records.length > 0 ) }
                        <div class="row" class:m-0={ minimal && records.length > 0 }>
                            <div class="col-sm-12" class:p-0={ minimal && records.length > 0 }>
                                <div class="table-container" class:fixed-header={ fixedHeader }>
                                    <table class="table table-bordered dataTable m-0" width="100%" cellspacing="0" role="grid" style="width: 100%;">
                                        <thead bind:this={ theadElement }>
                                            <tr>
                                                { #if records.length > 0 }
                                                    { #if selectable }
                                                        <th class="selection text-center">
                                                            <input type="checkbox" class="input table-input" value="all" on:change={ toggleSelectAllRecords }>
                                                        </th>
                                                    { /if }
                                                { /if }

                                                { #each api.columns as column }
                                                    { #if !column.hidden }
                                                        <th data-column={ column.name }>
                                                            <!-- svelte-ignore a11y-click-events-have-key-events -->
                                                            <div class="column-header" on:click={ () => { api.sort( column.name ); } }>
                                                                <div class="column-name">{ column.name }</div>
                                                                <div class="column-controls d-flex" style="flex-direction: column; font-size: 12px;">
                                                                    { #if sortColumn === column.name }
                                                                        { #if sortReverse }
                                                                            <i class="fa-solid fa-caret-up"></i>
                                                                        { :else }
                                                                            <i class="fa-solid fa-caret-down"></i>
                                                                        { /if }
                                                                    { /if }
                                                                </div>
                                                            </div>

                                                            <div class="column-search-box">
                                                                <input type="text" class="form-control form-control-sm input table-input" on:input={ onLocalSearch }>
                                                            </div>

                                                            { #if api.useKeys }
                                                                <div class="column-key-search-box mt-2">
                                                                    <!-- svelte-ignore a11y-click-events-have-key-events -->
                                                                    <div class="column-key-search-btn d-flex justify-content-center align-items-center" on:click={ onKeySearchMenuBtnClick( column.name ) } data-state="{ api.keys[ column.name ].entries.filter( function (entry) { return entry.checked; } ).length > 0 ? 'active' : 'idle' }">
                                                                        { #if Object.keys( api.keys ).length > 0 }
                                                                            { #if api.keys[ column.name ].menuOpen }
                                                                                <i class="fa-solid fa-caret-up"></i>
                                                                            { :else }
                                                                                <i class="fa-solid fa-caret-down"></i>
                                                                            { /if }
                                                                        { :else }
                                                                            <i class="fa-solid fa-caret-down"></i>
                                                                        { /if }
                                                                    </div>

                                                                    { #if Object.keys( api.keys ).length > 0 }
                                                                        <div class="column-key-search-menu" data-state={ api.keys[ column.name ].menuOpen ? 'open' : 'closed' }>
                                                                            <div class="row">
                                                                                <div class="col">
                                                                                    <input type="text" class="form-control form-control-sm input table-input" name="search" on:input={ onKeySearch( event, column.name ) }>
                                                                                </div>
                                                                            </div>

                                                                            <ul class="key-list">
                                                                                <li>
                                                                                    <label class="m-0 d-block">
                                                                                        <input type="checkbox" class="input table-input table-input-all mb-3" value="all" on:change={ onKeySelectAll( event, column.name ) }> ALL [ { api.keys[ column.name ].entries.filter( function (entry) { return !entry.hidden; } ).length } ]
                                                                                    </label>
                                                                                </li>

                                                                                <!--{ #each api.getColumnValues( column ) as key }
                                                                                    { #if !api.keys[ column ].entries.filter( function (entry) { return entry.value === key; } )[0].hidden }
                                                                                        <li>
                                                                                            <label class="m-0 d-block">
                                                                                                <input type="checkbox" class="input table-input" value={ key } on:change={ onKeySelect( event, column, key) }> { key }
                                                                                            </label>
                                                                                        </li>
                                                                                    { /if }
                                                                                { /each }-->

                                                                                { #each api.keys[ column.name ].entries.filter( function (entry) { return !entry.hidden; } ) as entry }
                                                                                    <li>
                                                                                        <label class="m-0 d-block">
                                                                                            <input type="checkbox" class="input table-input" value={ entry.value } checked={ entry.checked } on:change={ onKeySelect( event, column.name, entry.value ) }> { entry.value }
                                                                                        </label>
                                                                                    </li>
                                                                                { /each }
                                                                            </ul>
                                                                        </div>
                                                                    { /if }
                                                                </div>
                                                            { /if }
                                                        </th>
                                                    { /if }
                                                { /each }

                                                { #if records.length > 0 }
                                                    <th class="controls"></th>
                                                { /if }
                                            </tr>
                                        </thead>

                                        <tbody>
                                            { #each records as record, i }
                                                { #if !record.hidden }
                                                    <tr data-index={ i } data-id={ record.id }>
                                                        { #if selectable }
                                                            <td class="selectable text-center w-0">
                                                                <input type="checkbox" class="input table-input" value="select" checked={ record.selected } on:change={ onSelectionChange }>
                                                            </td>
                                                        { /if }

                                                        { #each record.values as value }
                                                            { #if !value.hidden }
                                                                <td class:w-0={ value.shrink }>
                                                                    { #if typeof value.content === 'undefined' }
                                                                        { value.value ?? '' }
                                                                    { :else }
                                                                        { @html value.content ?? '' }
                                                                    { /if }
                                                                </td>
                                                            { /if }
                                                        { /each }

                                                        <td class="controls text-center">
                                                            { #if controls }
                                                                { #if record.controls }
                                                                    { @html record.controls }
                                                                { /if }
                                                            { /if }
                                                        </td>
                                                    </tr>
                                                { /if }
                                            { /each }
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        { #if records.length === 0 }
                            <div class="row nodata-separator mt-4">
                                <NoData/>
                            </div>
                        { /if }
                    { :else }
                        <div class="row nodata-separator mt-4">
                            <NoData/>
                        </div>
                    { /if }
                </div>
            </div>
        { :else }
            <div class="row">
                <NoData/>
            </div>
        { /if }
    </div>
</div>

<style>

    .jtable
    {
        font-size: 12px;
        font-weight: 700;
    }

    /*

    .table tbody tr:hover
    {
        background-color: #d4d4d4;
    }

    .table tbody tr:nth-child(even)
    {
        background-color: #e1e1e1;
    }

    */

    .table-container.fixed-header
    {
        display: block;
        overflow-y: auto;
        /*max-height: 300px;*/

        /*max-height: 68vh !important;*/
    }

    .table-container.fixed-header .table thead
    {
        position: sticky;
        top: 0;
    }

    .table tbody tr td
    {
        vertical-align: middle;
    }

    .table .column-header
    {
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
    }

    .controls-left
    {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .search-box
    {
        margin: 10px 4px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .search-box .table-input
    {
        flex-grow: 1;
    }

    .column-key-search-box
    {
        position: relative;
    }

    .column-key-search-btn[data-state="idle"]
    {
        color: #ffffff;
        background-color: #858796;
        border-radius: 2px;
        cursor: pointer;
    }

    .column-key-search-btn[data-state="active"]
    {
        color: #ffffff;
        background-color: var( --simba-primary );
        border-radius: 2px;
        cursor: pointer;
    }

    .column-key-search-menu
    {
        max-width: 400px;
    }

    /*

    .column-key-search-menu[data-state="open"]
    {
        width: 100%;
        display: table;
        position: fixed;
        z-index: 1;
        background-color: #ffffff;
        border-radius: 2px;
    }

    */

    .column-key-search-menu[data-state="closed"]
    {
        display: none;
    }

    .column-key-search-menu ul
    {
        margin: 0;
        padding: 10px;
        list-style: none;
    }

    .column-key-search-menu .row .col
    {
        padding: 18px;
    }

    .active-filter-indicator
    {
        position: relative;
    }

    .active-filter-indicator span
    {
        margin-left: -4px;
        margin-bottom: -4px;
        position: absolute;
        left: 0;
        bottom: 0;
        font-size: 10px;
    }

    .selectable
    {
        padding: 0 10px;
    }

    .nodata-separator
    {
        padding-top: 20px;
        border-top: 1px solid var( --simba-dark-bg );
    }

</style>