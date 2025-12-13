<span class="io-type-tag method-neuter">
    @switch ( $io['type'] )
        @case ('ArrayList')
            {{ $io['type'] }}< <code>{{ $io['template']['short_name'] }}</code> >
        @break

        @case ('ReadableStream')
            {{ $io['type'] }}
        @break

        @default
            {{ $io['type'] }}: <span class="color-primary">{{ $io['short_name'] }}</span>
    @endswitch
</span>

@if ( $io['type'] === 'Value' )
    <table class="table table-dark table-reflection d-none">
        <thead>
            <tr>
                <th>Scope</th>
                <th>Property</th>
                <th>Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach ( $io['properties'] as $prop )
                <tr>
                    <td><span class="badge badge-{{ strtolower( $prop['scope'] ) }}">{{ $prop['scope'] }}</span></td>
                    <td><code>{{ $prop['name'] }}</code></td>
                    <td>
                        @if( is_bool( $prop['value'] ) )
                            <span class="val-bool {{ $prop['value'] ? 'true' : 'false' }}">{{ $prop['value'] ? 'true' : 'false' }}</span>
                        @elseif( is_numeric( $prop['value'] ) )
                            <span class="val-num">{{ $prop['value'] }}</span>
                        @else
                            <span class="val-str">"{{ $prop['value'] ?? 'null' }}"</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @php
        $id = 'input_' . $io['short_name'] . '_' . uniqid();
    @endphp

    <div class="rules-container">
        <table class="table-reflection">
            <thead>
                <tr>
                    {{--<th>Scope</th>--}}
                    <th>Property</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                @foreach ( $io['properties'] as $prop )
                    <tr>
                        {{--<td><span class="badge badge-{{ strtolower( $prop['scope'] ) }}">{{ $prop['scope'] }}</span></td>--}}
                        <td><code>{{ $prop['name'] }}</code></td>
                        <td>
                            @if( is_bool( $prop['value'] ) )
                                <span class="val-bool {{ $prop['value'] ? 'true' : 'false' }}">{{ $prop['value'] ? 'true' : 'false' }}</span>
                            @elseif( is_numeric( $prop['value'] ) )
                                <span class="val-num">{{ $prop['value'] }}</span>
                            @elseif( is_null( $prop['value'] ) )
                                <span class="val-null">null</span>
                            @else
                                <span class="val-str">"{{ $prop['value'] }}"</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@elseif ( $io['type'] === 'DTO' )
    <div class="json-explorer">
        <div class="dto-structure dto-{{ $method_type }}">
            <div class="text-muted small mb-2">DTO: {{ $io['class'] }}</div>

            <div class="json-bracket">{</div>
            @foreach ( $io['properties'] as $property )
                @php
                    $prop_name = $property['name'] ?? $property['properties'][0]['value'] ?? $property['short_name'];
                    $id = 'prop_' . $prop_name . '_' . uniqid();
                @endphp

                <div class="json-row">
                    <div class="json-key-line collapsed" data-bs-toggle="collapse" data-bs-target="#{{ $id }}" aria-expanded="false">
                        <span class="collapse-indicator">▼</span>
                        <span class="json-key">"{{ $prop_name }}"</span>
                        <span class="json-colon">:</span>
                        <span class="type-badge">{{ $property['short_name'] ?? $property['type'] }}</span>
                    </div>
                    <div class="collapse" id="{{ $id }}" style="">
                        @include ( 'apidoc/partials/io.blade.php', [ 'io' => $property ] )
                    </div>
                </div>
            @endforeach
            <div class="json-bracket">}</div>
        </div>
    </div>
@elseif ( $io['type'] === 'ArrayList' )
    <div class="list-structure">
        @if ( isset( $io['template'] ) )
            @include ( 'apidoc/partials/io.blade.php', [ 'io' => $io['template'] ] )
        @endif
    </div>
@endif