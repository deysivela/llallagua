@extends('serve.layouts.index')
@section('content')
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2 style="font-size: 18px;">Salud &mdash; Directorio de establecimientos</h2>
                <ul class="nav navbar-right panel_toolbox">
                    <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
                    <li><a class="close-link"><i class="fa fa-close"></i></a></li>
                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                @include('serve.salud.partials.flash')

                <div class="row m-b-15">
                    <div class="col-md-8 col-sm-12 col-xs-12">
                        <form method="GET" action="{{ route('salud.index') }}" class="form-inline">
                            <div class="form-group">
                                <label class="sr-only" for="q">Buscar</label>
                                <input type="text" name="q" id="q" class="form-control" value="{{ request('q') }}"
                                    placeholder="Nombre, zona, dirección o responsable">
                            </div>
                            <div class="form-group">
                                <label class="sr-only" for="tipo">Tipo</label>
                                <select name="tipo" id="tipo" class="form-control">
                                    <option value="">Todos los tipos</option>
                                    <option value="hospital" {{ request('tipo') === 'hospital' ? 'selected' : '' }}>Hospital</option>
                                    <option value="centro_salud" {{ request('tipo') === 'centro_salud' ? 'selected' : '' }}>Centro de salud</option>
                                    <option value="rehabilitacion" {{ request('tipo') === 'rehabilitacion' ? 'selected' : '' }}>Rehabilitación</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Buscar</button>
                            <a href="{{ route('salud.index') }}" class="btn btn-default">Limpiar</a>
                        </form>
                    </div>
                    <div class="col-md-4 col-sm-12 col-xs-12 text-right">
                        @can('salud.edit')
                            <a href="{{ route('salud.create') }}" class="btn btn-success">
                                <i class="fa fa-plus"></i> Nuevo establecimiento
                            </a>
                        @endcan
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Establecimiento</th>
                                <th>Contacto</th>
                                <th class="center">Completitud</th>
                                <th class="center">Cronogramas</th>
                                <th class="center" style="width: 130px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($establecimientos as $establecimiento)
                                <tr>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:10px;">
                                            @if($establecimiento->fotoUrl())
                                                <img src="{{ $establecimiento->fotoUrl() }}" alt="{{ $establecimiento->nombre }}"
                                                    style="width:54px;height:54px;object-fit:cover;border-radius:6px;border:1px solid #e5e7eb;">
                                            @else
                                                <span style="display:inline-flex;align-items:center;justify-content:center;width:54px;height:54px;border-radius:6px;background:#eef6ee;color:#2f7d32;">
                                                    <i class="fa fa-hospital-o" style="font-size:20px;"></i>
                                                </span>
                                            @endif
                                            <div>
                                                <strong>{{ $establecimiento->nombre }}</strong><br>
                                                <small class="text-muted">
                                                    {{ $establecimiento->tipo_texto }}
                                                    @if($establecimiento->nivel) &middot; {{ $establecimiento->nivel }} @endif
                                                </small><br>
                                                <small class="text-muted">
                                                    {{ $establecimiento->zona ?: 'Zona sin registrar' }}
                                                    @if($establecimiento->direccion) &middot; {{ $establecimiento->direccion }} @endif
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($establecimiento->responsable)
                                            <div><i class="fa fa-user-md"></i> {{ $establecimiento->responsable }}</div>
                                        @endif
                                        @forelse($establecimiento->telefonos as $telefono)
                                            <div><i class="fa fa-phone"></i> {{ $telefono->numero }}
                                                <small class="text-muted">({{ $telefono->etiqueta }})</small>
                                            </div>
                                        @empty
                                            <small class="text-muted">Sin teléfonos registrados</small>
                                        @endforelse
                                    </td>
                                    <td class="center" style="min-width:160px;">
                                        <div class="progress" style="margin-bottom:5px;">
                                            <div class="progress-bar progress-bar-{{ $establecimiento->completitud >= 100 ? 'success' : ($establecimiento->completitud >= 50 ? 'warning' : 'danger') }}"
                                                role="progressbar" style="width: {{ $establecimiento->completitud }}%;">
                                                {{ $establecimiento->completitud }}%
                                            </div>
                                        </div>
                                        @if(count($establecimiento->pendientes))
                                            <small class="text-danger">Falta: {{ implode(', ', $establecimiento->pendientes) }}</small>
                                        @else
                                            <small class="text-success">Ficha completa</small>
                                        @endif
                                    </td>
                                    <td class="center">
                                        <span class="badge bg-green">{{ $establecimiento->cronogramas_publicados_count }}</span>
                                        <div class="m-t-5">
                                            <a href="{{ route('salud.cronogramas.index', ['establecimiento_id' => $establecimiento->id]) }}"
                                                class="btn btn-xs btn-info"><i class="fa fa-calendar"></i> Ver</a>
                                        </div>
                                    </td>
                                    <td class="center" style="white-space: nowrap;">
                                        <a href="{{ route('salud.public.show', $establecimiento->slug) }}" target="_blank" rel="noopener"
                                            class="btn btn-xs btn-default" title="Ver ficha pública"><i class="fa fa-external-link"></i></a>
                                        @can('salud.edit')
                                            <a href="{{ route('salud.edit', $establecimiento->id) }}"
                                                class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                            <a href="{{ route('salud.cronogramas.create', ['establecimiento_id' => $establecimiento->id]) }}"
                                                class="btn btn-xs btn-info" title="Subir cronograma"><i class="fa fa-upload"></i></a>
                                        @endcan
                                        @can('salud.destroy')
                                            {!! Form::open(['route' => ['salud.destroy', $establecimiento->id], 'method' => 'DELETE', 'style' => 'display:inline', 'onsubmit' => "return confirm('¿Eliminar este establecimiento y todos sus datos asociados?');"]) !!}
                                                <button type="submit" class="btn btn-xs btn-danger" title="Eliminar"><i class="fa fa-trash"></i></button>
                                            {!! Form::close() !!}
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        No se encontraron establecimientos de salud con los filtros seleccionados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
