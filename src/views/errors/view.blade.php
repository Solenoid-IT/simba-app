<!DOCTYPE html>
<html lang="it" data-bs-theme="dark">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>SXF - Error Map</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                background-color: #0b0c0d; /* Un nero leggermente più profondo del default */
            }
            .card {
                border-color: #2d2f31;
            }
            .text-purple {
                color: #bb86fc; /* Tocco di colore per il look dark/developer */
            }
        </style>
    </head>
    <body>
        {{--
        <nav class="navbar navbar-expand-lg border-bottom border-secondary">
            <div class="container">
                
                <a class="navbar-brand fw-bold" href="#"><span class="text-purple">Solenoid</span> \ X</a>
                
                <div class="d-flex">
                    <span class="badge bg-danger">v2026.02</span>
                </div>
            </div>
        </nav>
        --}}

        <main class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="text-center mb-5">
                        <h1 class="display-4 fw-bold">Error Map</h1>
                        <p class="lead text-secondary">List of available errors</p>
                    </div>

                    <div class="card shadow">
                        {{--
                        <div class="card-header bg-dark border-bottom border-secondary py-3">
                            <h5 class="m-0 text-purple">List</h5>
                        </div>
                        --}}
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-dark table-hover m-0">
                                    <thead>
                                        <tr>
                                            <tr>
                                                @foreach ( array_keys( $records[0] ) as $key )
                                                    <th>{{ $key }}</th>
                                                @endforeach
                                            </tr>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ( $records as $record )
                                            <tr>
                                                @foreach ( $record as $value )
                                                    <td>{{ $value }}</td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 text-center text-secondary small">
                        <p>Copyright © <b>Solenoid-IT</b> {{ date('Y') }}</p>
                    </div>
                </div>
            </div>
        </main>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>