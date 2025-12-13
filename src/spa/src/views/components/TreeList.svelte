<script>

    import { browser } from '$app/environment';

    import { onMount } from 'svelte';

    import { createEventDispatcher } from 'svelte';



    const dispatch = createEventDispatcher();



    let element = null;



    export let resourceType = '';

    export let title        = null;

    export let records      = [];



    $:
        if ( resourceType )
        {// Value found
            // (Getting the value)
            title = `${ resourceType } (${ records.length })`;
        }
    
    $:
        if ( records )
        {// Value found
            // (Getting the value)
            title = `${ resourceType } (${ records.length })`;
        }

</script>

<div class="card shadow mb-4 component treelist-component" bind:this={ element }>
    <div class="card-header py-3 d-flex align-items-center" style="justify-content: space-between;">
        <h6 class="m-0 font-weight-bold text-primary">{ resourceType } ({ records.length })</h6>

        <slot name="fixed-controls"/>
    </div>

    <div class="card-body">
        { #if records.length > 0 }
            <div class="table-responsive">
                <div class="dataTables_wrapper dt-bootstrap4">
                    <div class="row">
                        <div class="col d-flex align-items-center" style="justify-content: space-between;">
                            <div class="controls-left">
                                <div class="num-results">( <b>{ records.filter( function (record) { return !record.hidden; } ).length }</b> )</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <table class="table table-bordered dataTable" width="100%" cellspacing="0" role="grid" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th class="selection text-center">
                                            <input type="checkbox" class="input treelist-input" value="all">
                                        </th>

                                        <th class="controls"></th>
                                    </tr>
                                </thead>

                                <tbody>
                                    { #each records as task, i }
                                        { #if !task['parent'] }
                                            <tr>
                                                <td class="selection text-center">
                                                    <input type="checkbox" class="input treelist-input" value="{ i }">
                                                </td>
                                                <td>
                                                    <ul>
                                                        <li>{ task['name'] }</li>

                                                        <ul>
                                                            { #each records.filter( function (r) { return r['parent'] === task['id']; } ) as subtask, j }
                                                                <li>{ subtask['name'] }</li>
                                                            { /each }
                                                        </ul>
                                                    </ul>
                                                </td>
                                            </tr>
                                        { /if }
                                    { /each }
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        { :else }
            <div class="row">
                <div class="col d-flex justify-content-center align-items-center" style="flex-direction: column;">
                    <span class="mb-3" style="font-size: 18px; font-weight: 800;">NO DATA</span>

                    <i class="fa-solid fa-box" style="font-size: 32px;"></i>
                </div>
            </div>
        { /if }
    </div>
</div>

<style>

    

</style>