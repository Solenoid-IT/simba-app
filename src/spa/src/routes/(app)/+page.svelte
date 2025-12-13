<svelte:head>
    <title>{ entityType }</title>
</svelte:head>

<script>

    import { envs } from '@/envs.js';

    import * as Time from '@/modules/Time.js';

    import { Client } from '@/modules/Client.ts';

    import { appData } from '@/stores/appData.js';

    import { onMount } from 'svelte';

    import Chart from '@/views/components/Chart.svelte';



    const subject = {};



    const entityType = 'Dashboard';



    const client = new Client( '/api/user' );



    // Returns [Promise:bool]
    subject.calcReport = async function ()
    {
        // (Getting the values)
        const { code, headers, body } = await client.run( `${ entityType }.calc_report` );

        if ( code !== 200 )
        {// (Request failed)
            // Returning the value
            return false;
        }



        // (Getting the value)
        $appData = body;



        // (Getting the value)
        loginUptime = $appData['login_uptime'];



        // (Clearing the interval)
        clearInterval( interval );

        // (Setting the interval)
        interval = setInterval
        (
            function ()
            {
                // (Incrementing the value)
                loginUptime += 1;
            },
            1000
        )
        ;



        // (Building the chart)
        yearlyChart.build
        (
            {
                'type': 'bar',
                'data':
                {
                    'labels':   Object.keys( $appData['current_year_logins'] ).map( function (value) { return value.toString().padStart( 2, '0' ); } ),
                    'datasets':
                    [
                        {
                            'label':           'Frequency',
                            'data':            Object.values( $appData['current_year_logins'] ),
                            'borderWidth':     1,
                            //'backgroundColor': `hsl(${ hue } 100% 50% / .6)`,
                            'backgroundColor': '#078e6e',
                            'fill':            true,
                            //'tension':         0.1
                        }
                    ]
                },
                'options':
                {
                    'scales':
                    {
                        'y':
                            {
                                'beginAtZero': true
                            }
                    }
                }
            }
        )
        ;



        // (Building the chart)
        monthlyChart.build
        (
            {
                'type': 'bar',
                'data':
                {
                    'labels':   Object.keys( $appData['current_month_logins'] ).map( function (value) { return value.toString().padStart( 2, '0' ); } ),
                    'datasets':
                    [
                        {
                            'label':           'Frequency',
                            'data':            Object.values( $appData['current_month_logins'] ),
                            'borderWidth':     1,
                            //'backgroundColor': `hsl(${ hue } 100% 50% / .6)`,
                            'backgroundColor': '#078e6e',
                            'fill':            true,
                            //'tension':         0.1
                        }
                    ]
                },
                'options':
                {
                    'scales':
                    {
                        'y':
                            {
                                'beginAtZero': true
                            }
                    }
                }
            }
        )
        ;



        // (Building the chart)
        browserChart.build
        (
            {
                'type': 'bar',
                'data':
                {
                    'labels':   Object.keys( $appData['login_browsers'] ),
                    'datasets':
                    [
                        {
                            'label':           'Frequency',
                            'data':            Object.values( $appData['login_browsers'] ),
                            'borderWidth':     1,
                            //'backgroundColor': `hsl(${ hue } 100% 50% / .6)`,
                            'backgroundColor': '#078e6e',
                            'fill':            true,
                            //'tension':         0.1
                        }
                    ]
                },
                'options':
                {
                    'scales':
                    {
                        'y':
                            {
                                'beginAtZero': true
                            }
                    }
                }
            }
        )
        ;



        // Returning the value
        return true;
    }



    // (Listening for the event)
    onMount
    (
        async function ()
        {
            // (Calculating the report)
            await subject.calcReport();
        }
    )
    ;



    let interval;
    let loginUptime;



    let yearlyChart;
    let monthlyChart;

    let browserChart;

</script>

<!-- Content Row -->
<div class="row">
    { #if $appData }
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Users
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{ $appData['num_users'] }</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Groups
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{ $appData['num_groups'] }</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Active Sessions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{ $appData['num_active_sessions'] }</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-right-to-bracket fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Session Uptime
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{ Time.formatSeconds( loginUptime, 'H:M:S' ) }</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    { /if }
</div>

<!-- Content Row -->

<div class="row">
    <div class="col-xl-6">
        <div class="card shadow mb-4">
            <!-- Card Header - Dropdown -->
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Logins (Current Year)</h6>
                <div class="dropdown no-arrow d-none">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                        aria-labelledby="dropdownMenuLink">
                        <div class="dropdown-header">Dropdown Header:</div>
                        <a class="dropdown-item" href="#">Action</a>
                        <a class="dropdown-item" href="#">Another action</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#">Something else here</a>
                    </div>
                </div>
            </div>
            <!-- Card Body -->
            <div class="card-body">
                <div class="chart-area">
                    <Chart bind:api={ yearlyChart } width="1200px"/>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card shadow mb-4">
            <!-- Card Header - Dropdown -->
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Logins (Current Month)</h6>
                <div class="dropdown no-arrow d-none">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                        aria-labelledby="dropdownMenuLink">
                        <div class="dropdown-header">Dropdown Header:</div>
                        <a class="dropdown-item" href="#">Action</a>
                        <a class="dropdown-item" href="#">Another action</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#">Something else here</a>
                    </div>
                </div>
            </div>
            <!-- Card Body -->
            <div class="card-body">
                <div class="chart-area">
                    <Chart bind:api={ monthlyChart } width="1200px"/>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content Row -->
<div class="row">

    <!-- Content Column -->
    <div class="col-lg-6 mb-4">

        <!-- Browser Chart -->
        <div class="card shadow mb-4">
            <!-- Card Header - Dropdown -->
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Logins (Browser)</h6>
                <div class="dropdown no-arrow d-none">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                        aria-labelledby="dropdownMenuLink">
                        <div class="dropdown-header">Dropdown Header:</div>
                        <a class="dropdown-item" href="#">Action</a>
                        <a class="dropdown-item" href="#">Another action</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#">Something else here</a>
                    </div>
                </div>
            </div>
            <!-- Card Body -->
            <div class="card-body">
                <div class="chart-area">
                    <Chart bind:api={ browserChart } width="1200px"/>
                </div>
            </div>
        </div>

    </div>

    <div class="col-lg-6 mb-4">

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Support</h6>
            </div>
            <div class="card-body">
                <p>
                    May <b>AI</b> help you ?
                </p>
                <a target="_blank" rel="nofollow" href="/help">ChatHelper &rarr;</a>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Documentation</h6>
            </div>
            <div class="card-body">
                <p>
                    Visit our wikicenter for more information
                </p>
                <a target="_blank" rel="nofollow" href="/wiki">WikiCenter &rarr;</a>
            </div>
        </div>

    </div>
</div>