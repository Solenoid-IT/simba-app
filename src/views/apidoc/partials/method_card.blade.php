<div class="api-card">
    @php
        // (Getting the value)
        $method_id = 'method_' . uniqid();



        // (Setting the value)
        $method_type = null;

        switch ( $method['name'] )
        {
            case 'find':
            case 'list':
                // (Setting the value)
                $method_type = 'read';
            break;

            case 'update':
                // (Setting the value)
                $method_type = 'update';
            break;

            case 'insert':
                // (Setting the value)
                $method_type = 'insert';
            break;

            case 'delete':
                // (Setting the value)
                $method_type = 'delete';
            break;



            case 'upsert':
                // (Setting the value)
                $method_type = 'upsert';
            break;
        }

        if ( str_starts_with( $method['name'], 'set' ) )
        {// Match OK
            // (Setting the value)
            $method_type = 'set';
        }

        if ( !$method_type )
        {// Value not found
            // (Setting the value)
            $method_type = 'neuter';
        }

    @endphp

    <div class="api-header">
        <div>
            {{--<span class="text-muted small">public function</span>--}}
            <span class="method-name method-{{ $method_type }} ms-1">{{ $method['name'] }} ()</span>
        </div>
        <div class="d-flex align-items-center gap-3">
            @if ( $method['input'] )
                <button class="btn-collapse" type="button" data-bs-toggle="collapse" data-bs-target="#input_{{ $method_id }}" {{ $method['input'] ? '' : 'disabled' }}>
                    INPUT <i class="chevron"></i>
                </button>
            @endif

            @if ( $method['output'] )
                <button class="btn-collapse" type="button" data-bs-toggle="collapse" data-bs-target="#output_{{ $method_id }}" {{ $method['output'] ? '' : 'disabled' }}>
                    OUTPUT <i class="chevron"></i>
                </button>
            @endif

            @if ( $method['errors'] )
                <button class="btn-collapse" type="button" data-bs-toggle="collapse" data-bs-target="#error_{{ $method_id }}" {{ $method['errors'] ? '' : 'disabled' }}>
                    ERROR <i class="chevron"></i>
                </button>
            @endif
        </div>
    </div>

    @if ( $method['input'] )
        <div class="collapse" id="input_{{ $method_id }}">
            <div class="table-box">INPUT</div>

            @include ( 'apidoc/partials/io.blade.php', [ 'io' => $method['input'], 'method_type' => $method_type ] )
        </div>
    @endif

    @if ( $method['output'] )
        <div class="collapse" id="output_{{ $method_id }}">
            <div class="table-box">OUTPUT</div>

            @include ( 'apidoc/partials/io.blade.php', [ 'io' => $method['output'], 'method_type' => $method_type ] )
        </div>
    @endif

    @if ( $method['errors'] )
        <div class="collapse" id="error_{{ $method_id }}">
            <div class="table-box">ERROR</div>

            <div class="rules-container">
                <table class="table-reflection">
                    <thead>
                        <tr>
                            <th>app code</th>
                            <th>http code</th>
                            <th>type</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ( $method['errors'] as $error )
                            <tr>
                                <td><span class="val-num">{{ $error['code'] }}</span></td>
                                <td><code>{{ $error['http_code'] }}</code></td>
                                <td><span class="val-num">{{ $error['type'] }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>