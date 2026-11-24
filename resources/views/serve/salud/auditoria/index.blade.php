@extends('serve.layouts.index')
@section('content')
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2 style="font-size: 18px;">Salud &mdash; Auditoría</h2>
                <ul class="nav navbar-right panel_toolbox">
                    <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
                    <li><a class="close-link"><i class="fa fa-close"></i></a></li>
                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                @include('serve.salud.partials.flash')

                <form method="GET" action="{{ route('salud.auditoria.index') }}" class="form-inline m-b-15">
                    <div class="form-group">
                        <select name="entidad" class="form-control">
                            <option value="">Todas las entidades</option>
                            @foreach($entidades as $entidad)
                                <option value="{{ $entidad }}" {{ request('entidad') === $entidad ? 'selected' : '' }}>{{ ucfirst($entidad) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <select name="accion" class="form-control">
                            <option value="">Todas las acciones</option>
                            @foreach($acciones as $accion)
                                <option value="{{ $accion }}" {{ request('accion') === $accion ? 'selected' : '' }}>{{ ucfirst($accion) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="sr-only" for="desde">Desde</label>
                        <input type="date" name="desde" id="desde" class="form-control" value="{{ request('desde') }}">
                    </div>
                    <div class="form-group">
                        <label class="sr-only" for="hasta">Hasta</label>
                        <input type="date" name="hasta" id="hasta" class="form-control" value="{{ request('hasta') }}">
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Filtrar</button>
                    <a href="{{ route('salud.auditoria.index') }}" class="btn btn-default">Limpiar</a>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Usuario</th>
                                <th>Acción</th>
                                <th>Entidad</th>
                                <th>ID</th>
                                <th>Detalle</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($registros as $registro)
                                <tr>
                                    <td>{{ $registro->fecha ? $registro->fecha->format('d/m/Y H:i') : '—' }}</td>
                                    <td>
                                        @if($registro->usuario)
                                            {{ $registro->usuario->user }}
                                        @else
                                            <span class="text-muted">Sistema</span>
                                        @endif
                                    </td>
                                    <td><span class="label label-info">{{ $registro->accionTexto() }}</span></td>
                                    <td>{{ $registro->entidad }}</td>
                                    <td>{{ $registro->entidad_id }}</td>
                                    <td>
                                        @if($registro->despues)
                                            <details>
                                                <summary>Ver datos</summary>
                                                <pre style="max-height:220px;overflow:auto;font-size:11px;">{{ json_encode($registro->despues, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            </details>
                                        @elseif($registro->antes)
                                            <details>
                                                <summary>Ver datos anteriores</summary>
                                                <pre style="max-height:220px;overflow:auto;font-size:11px;">{{ json_encode($registro->antes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            </details>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No hay registros de auditoría.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="text-center">
                    {{ $registros->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
