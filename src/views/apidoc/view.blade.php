@extends ('apidoc/layout.blade.php')

@section ('content')
    <div class="container py-5">
        <header class="mb-5 text-center">
            <h1 class="fw-bold">API Reference</h1>
            <p class="text-muted">Powered by SXF</p>
        </header>

        <select class="endpoint-select form-control mb-5">
            <option value="">Available Endpoints ( {{ count($endpoints) }} )</option>
            <option disabled></option>

            @foreach ( $endpoints as $endpoint_path => $endpoint )
                <option value="{{ md5( $endpoint_path ) }}">{{ $endpoint_path }}</option>
            @endforeach
        </select>

        @foreach ( $endpoints as $endpoint_path => $endpoint )
            <div class="tree-level endpoint-tree" id="endpoint_{{ md5( $endpoint_path ) }}">
                @foreach ( $endpoint['tree'] as $node )
                    @include ( 'apidoc/partials/tree_node.blade.php', [ 'node' => $node ] )
                @endforeach
            </div>
        @endforeach
    </div>

    <script>

        // (Listening for the event)
        document.querySelector( '.endpoint-select' ).addEventListener('change', function () {
            if ( !this.value ) return;



            // (Iterating each entry)
            document.querySelectorAll( '.endpoint-tree' ).forEach( (element) => { element.classList.add('d-none'); } );

            // (Adding the class)
            document.querySelector( '#endpoint_' + this.value ).classList.remove( 'd-none' );
        });

    </script>
@endsection