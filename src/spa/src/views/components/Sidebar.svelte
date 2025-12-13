<svelte:window on:keydown={ onKeyDown }/>

<script>

    import { onMount } from 'svelte';


    let closed = false;



    // Returns [void]
    function toggleSidebar ()
    {
        // (Getting the value)
        closed = !closed;

        // (Setting the value)
        localStorage.setItem( 'sidebarClosed', JSON.stringify( closed ) );



        /*
        
        // (Iterating each entry)
        element.querySelectorAll('.page-section').forEach
        (
            function (el)
            {
                // (Setting the class)
                el.classList.remove('collapsed');



                // (Getting the value)
                const targetId = el.getAttribute('data-target').replace( /^\#/, '' );

                // (Setting the class)
                document.getElementById( targetId ).classList.remove('show');
            }
        )
        ;
        
        */
    }

    // Returns [void]
    function onKeyDown (event)
    {
        if ( document.activeElement.classList.contains('input') ) return;

        if ( document.activeElement.closest('.input') !== null ) return;

        switch ( event.key )
        {
            case 's':// (Sidebar controls)
                // (Calling the function)
                toggleSidebar();
            break;
        }
    }



    // (Listening for the event)
    onMount
    (
        function ()
        {
            // (Getting the value)
            closed = JSON.parse( localStorage.getItem('sidebarClosed') ?? false );



            // (Getting the value)
            const openSection = localStorage.getItem( 'openSection' );

            if ( openSection )
            {// Value found
                // (Triggering the event)
                jQuery(element).find(`.page-section[data-target="${ openSection }"]`).trigger('click');
            }
        }
    )
    ;



    let element;



    // Returns [void]
    function saveOpenSection (event)
    {
        // (Getting the value)
        const targetId = event.target.getAttribute('data-target');

        if ( targetId === null ) return;



        // (Getting the value)
        const id = targetId.replace( /^\#/, '' );

        // (Setting the item)
        localStorage.setItem( 'openSection', document.getElementById(id).classList.contains('show') ? null : targetId );
    }

</script>

<!-- Sidebar -->
<ul class="navbar-nav sidebar sidebar-dark accordion { closed ? 'closed' : '' }" id="accordionSidebar" bind:this={ element }>

    <!-- Divider -->
    <!--<hr class="sidebar-divider my-0">-->
    <hr class="sidebar-divider">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item active">
        <a class="nav-link" href="/">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Resources
    </div>

    <!-- Nav Item - Charts -->
    <li class="nav-item">
        <a class="nav-link" href="/resources/notes">
            <i class="fas fa-fw fa-note-sticky"></i>
            <span>Notes</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">



    <div class="d-none">
        <!-- Heading -->
        <div class="sidebar-heading">
            Tasks
        </div>

        <!-- Nav Item - Tasks -->
        <li class="nav-item">
            <a class="nav-link" href="/statuses">
                <i class="fa-solid fa-temperature-three-quarters"></i>
                <span>Status</span>
            </a>
        </li>

        <!-- Nav Item - Tasks -->
        <li class="nav-item">
            <a class="nav-link" href="/tasks">
                <i class="fa-solid fa-list-check"></i>
                <span>Task</span>
            </a>
        </li>

        <!-- Divider -->
        <hr class="sidebar-divider d-none d-md-block">
    </div>



    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="border-0" id="sidebarToggle" on:click={ toggleSidebar } title="Sidebar ON/OFF (S)"></button>
    </div>

</ul>
<!-- End of Sidebar -->

<style>

    .sidebar
    {
        padding-top: 70px;
        /*background-color: #212529;*/
        background-color: var( --simba-dark );
    }

    .sidebar.closed
    {
        width: 0 !important;
    }

    .sidebar #sidebarToggle
    {
        border-radius: 4px !important;
    }

    .sidebar.closed #sidebarToggle
    {
        width: 18px;
        margin-left: 4px;
        position: fixed;
        left: 0;
        z-index: 9999;
        background-color: var( --simba-dark );
        color: #ffffff;
        border-radius: 4px !important;
    }

    .sidebar.closed #sidebarToggle::after
    {
        content: '\f105';
    }

    .nav-item .nav-link i
    {
        width: 20px;
    }

</style>