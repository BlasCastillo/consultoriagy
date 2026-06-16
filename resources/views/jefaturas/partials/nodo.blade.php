<ul>
    <li>
        <div class="node-card">
            <span class="nivel-badge">Nivel {{ $nodo->nivel }}</span>
            <span class="node-title">{{ $nodo->nombre }}</span>
        </div>
        
        @if($nodo->childrenRecursive->count() > 0)
            @foreach($nodo->childrenRecursive as $hijo)
                @include('jefaturas.partials.nodo', ['nodo' => $hijo])
            @endforeach
        @endif
    </li>
</ul>
