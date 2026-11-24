@php
    $personas = isset($personas) ? $personas : (isset($persona) ? $persona : collect());
    if ($personas instanceof \App\Employee) {
        $personas = collect([$personas]);
    } else {
        $personas = collect($personas)->filter();
    }
    $etiqueta = $etiqueta ?? 'Responsable';
    $icono = $icono ?? 'icon-Administrator';
    $mostrarCargo = !empty($mostrarCargo);
@endphp

@foreach($personas as $persona)
    <li>
        @if($persona->photo)
            <img class="gam-subalc-fact-avatar" src="{{ asset($persona->photo) }}" alt="{{ $persona->full_name }}">
        @else
            <span class="gam-subalc-fact-icon" aria-hidden="true">
                <i class="{{ $icono }}"></i>
            </span>
        @endif
        <span class="gam-subalc-fact-copy">
            <span class="gam-side-label">{{ $etiqueta }}</span>
            <strong>{{ $persona->full_name }}</strong>
            @if ($mostrarCargo && $persona->charge_employee)
                <span class="gam-contacto-cargo">{{ $persona->charge_employee }}</span>
            @endif
        </span>
    </li>
@endforeach
