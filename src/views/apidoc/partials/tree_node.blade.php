@if ( $node['type'] === 'folder' )
    <div class="folder-node">
        @php
            $id = slug( $node['name'] ) . '_' . bin2hex( random_bytes( 4 ) );
        @endphp

        <div class="folder-title collapsed" data-bs-toggle="collapse" data-bs-target="#{{ $id }}">
            {{ $node['name'] }}
        </div>
        <div class="collapse" id="{{ $id }}">
            <div class="tree-level">
                @foreach ( $node['content'] as $child )
                    @include ( 'apidoc/partials/tree_node.blade.php', [ 'node' => $child ] )
                @endforeach
            </div>
        </div>
    </div>
@else
    <div class="class-node">
        @php
            $id = slug( $node['name'] ) . '_' . bin2hex( random_bytes( 4 ) );
        @endphp

        <div class="class-title collapsed" data-bs-toggle="collapse" data-bs-target="#{{ $id }}">
            {{ pathinfo( $node['name'], PATHINFO_FILENAME ) }}
        </div>
        <div class="collapse" id="{{ $id }}">
            @foreach ( $node['methods'] as $method )
                @php
                    $errors = [];

                    foreach ( $method['errors'] as $error_code )
                    {// Processing each entry
                        // (Getting the value)
                        $error = error_find( $error_code );

                        if ( !$error ) continue;
                        if ( !$error['exposed'] ) continue;



                        // (Deleting the elements)
                        unset( $error['exposed'] );
                        unset( $error['notifiable'] );



                        // (Appending the value)
                        $errors[] = $error;
                    }



                    // (Getting the value)
                    $method['errors'] = $errors;
                @endphp

                @include ( 'apidoc/partials/method_card.blade.php', [ 'method' => $method ] )
            @endforeach
        </div>
    </div>
@endif