@extends('serve.layouts.index')
@section('content')
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2 style="font-size: 18px;">Salud &mdash; Cronogramas y actividades</h2>
                <ul class="nav navbar-right panel_toolbox">
                    <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
                    <li><a class="close-link"><i class="fa fa-close"></i></a></li>
                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                @include('serve.salud.partials.flash')

                <div class="row m-b-15">
                    <div class="col-md-9 col-sm-12 col-xs-12">
                        <form method="GET" action="{{ route('salud.cronogramas.index') }}" class="form-inline">
                            <div class="form-group">
                                <select name="establecimiento_id" class="form-control">
                                    <option value="">Todos los establecimientos</option>
                                    @foreach($establecimientos as $establecimiento)
                                        <option value="{{ $establecimiento->id }}" {{ (string) request('establecimiento_id') === (string) $establecimiento->id ? 'selected' : '' }}>
                                            {{ $establecimiento->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <select name="tipo" class="form-control">
                                    <option value="">Todas las categorías</option>
                                    @foreach(\App\Salud\SaludCronogramaMensual::TIPOS_CORTOS as $clave => $etiqueta)
                                        <option value="{{ $clave }}" {{ (string) request('tipo') === (string) $clave ? 'selected' : '' }}>
                                            {{ $etiqueta }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <input type="month" name="mes" class="form-control" value="{{ request('mes') }}">
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Filtrar</button>
                            <a href="{{ route('salud.cronogramas.index') }}" class="btn btn-default">Limpiar</a>
                        </form>
                    </div>
                    <div class="col-md-3 col-sm-12 col-xs-12 text-right">
                        @can('salud.cronogramas')
                            <a href="{{ route('salud.cronogramas.create') }}" class="btn btn-success">
                                <i class="fa fa-plus"></i> Nuevo cronograma o actividad
                            </a>
                        @endcan
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Mes / Vigencia</th>
                                <th>Categoría</th>
                                <th>Establecimiento</th>
                                <th>Servicio</th>
                                <th>Título</th>
                                <th>Archivo</th>
                                <th class="center" style="min-width: 180px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cronogramas as $cronograma)
                                <tr>
                                    <td>
                                        <strong>{{ $cronograma->mes ? $cronograma->mes->format('m/Y') : '—' }}</strong>
                                        @if($cronograma->fecha_fin)
                                            <br><small class="text-muted">Hasta: {{ $cronograma->fecha_fin->format('d/m/Y') }}</small>
                                        @endif
                                    </td>
                                    <td><span class="label label-info">{{ $cronograma->tipo_corto }}</span></td>
                                    <td>{{ $cronograma->establecimiento ? $cronograma->establecimiento->nombre : '—' }}</td>
                                    <td>{{ $cronograma->servicio ? $cronograma->servicio->nombre : 'General' }}</td>
                                    <td>{{ $cronograma->titulo ?: '—' }}</td>
                                    <td style="min-width:200px;">
                                        @if($cronograma->archivo)
                                            @if($cronograma->archivo->esImagen())
                                                <a href="{{ $cronograma->archivo->urlPublica() }}" target="_blank" rel="noopener">
                                                    <img src="{{ $cronograma->archivo->urlThumb() ?: $cronograma->archivo->urlPublica() }}"
                                                        alt="{{ $cronograma->archivo->nombre_original }}"
                                                        style="width:64px;height:48px;object-fit:cover;border-radius:4px;border:1px solid #e5e7eb;">
                                                </a>
                                            @else
                                                <a href="{{ $cronograma->archivo->urlPublica() }}" target="_blank" rel="noopener">
                                                    <i class="fa fa-file-pdf-o text-danger"></i>
                                                    {{ $cronograma->archivo->nombre_original }}
                                                </a>
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="center">
                                        <a href="{{ route('salud.cronogramas.edit', $cronograma->id) }}" class="btn btn-sm btn-warning">
                                            <i class="fa fa-edit"></i> Editar
                                        </a>
                                        {!! Form::open(['route' => ['salud.cronogramas.destroy', $cronograma->id], 'method' => 'DELETE', 'style' => 'display:inline', 'onsubmit' => "return confirm('¿Eliminar este cronograma?');"]) !!}
                                            <button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i> Eliminar</button>
                                        {!! Form::close() !!}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No hay cronogramas registrados con los filtros seleccionados.</td>
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
