{{-- ── HOJA PORTADA + SUMARIO (reutilizable) ─────────────────────────────
     Requiere: $gaceta con relaciones solicitudes.institucion y
     solicitudes.actos cargadas.

     Opcional: $actoUnico (por defecto false). Cuando es true se usa la
     variante con posiciones fijas en mm y el contenedor del acto dentro de
     la propia hoja, debajo de la línea negra.

     Esta parcial y el PDF final comparten hoja_portada*.blade.php, por lo que
     la "Vista Previa Legal" y la descarga se ven idénticas.
     ──────────────────────────────────────────────────────────────────── --}}
@if($actoUnico ?? false)
    @include('gacetas.partials.hoja_portada_acto_unico')
@else
    @include('gacetas.partials.hoja_portada')
@endif
