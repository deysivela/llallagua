@extends('serve.layouts.index')
@section('content')
@php
    $employee = $user->employees;
    $avatarUrl = ($employee && $employee->photo)
        ? asset($employee->photo)
        : asset('server/production/images/user.png');
    $fullName = ($employee && trim($employee->name . ' ' . $employee->last_name) !== '')
        ? trim($employee->name . ' ' . $employee->last_name)
        : $user->user;
    $chargeName = optional(optional($employee)->charges)->charge_employee;
@endphp

<div class="container">
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2 style="font-size: 18px;"><i class="fa fa-user"></i> Mi Perfil y Credenciales de Acceso</h2>
                    <ul class="nav navbar-right panel_toolbox">
                        <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
                    </ul>
                    <div class="clearfix"></div>
                </div>

                <div class="x_content">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade in" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                            <i class="fa fa-check-circle"></i> {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade in" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                            <i class="fa fa-exclamation-triangle"></i> {{ session('error') }}
                        </div>
                    @endif

                    @if (session('info'))
                        <div class="alert alert-info alert-dismissible fade in" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                            <i class="fa fa-info-circle"></i> {{ session('info') }}
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-4 col-sm-4 col-xs-12 profile_left">
                            <div class="profile_img text-center">
                                <div id="crop-avatar">
                                    <img class="img-responsive avatar-view center-block" src="{{ $avatarUrl }}" alt="Avatar" style="max-height: 220px; border-radius: 6px;">
                                </div>
                            </div>

                            <h3 class="text-center">{{ $fullName }}</h3>

                            <ul class="list-unstyled user_data">
                                <li>
                                    <i class="fa fa-user user-profile-icon"></i>
                                    <strong>Usuario actual:</strong> <code>{{ $user->user }}</code>
                                </li>
                                <li>
                                    <i class="fa fa-id-card user-profile-icon"></i>
                                    <strong>Personal vinculado:</strong>
                                    @if ($employee)
                                        {{ trim($employee->name . ' ' . $employee->last_name) }}
                                    @else
                                        <span class="text-muted">Sin vincular</span>
                                    @endif
                                </li>
                                @if ($employee && $employee->address)
                                    <li>
                                        <i class="fa fa-map-marker user-profile-icon"></i> {{ $employee->address }}
                                    </li>
                                @endif
                                @if ($chargeName)
                                    <li>
                                        <i class="fa fa-briefcase user-profile-icon"></i> {{ $chargeName }}
                                    </li>
                                @endif
                                @if ($user->roles && $user->roles->count())
                                    <li>
                                        <i class="fa fa-shield user-profile-icon"></i>
                                        <strong>Rol(es):</strong>
                                        @foreach ($user->roles as $role)
                                            <span class="label label-info">{{ $role->name }}</span>
                                        @endforeach
                                    </li>
                                @endif
                                @if ($user->establecimientosSalud && $user->establecimientosSalud->count())
                                    <li>
                                        <i class="fa fa-hospital-o user-profile-icon"></i>
                                        <strong>Establecimiento(s) de Salud:</strong>
                                        <ul style="padding-left: 18px; margin-top: 4px;">
                                            @foreach ($user->establecimientosSalud as $est)
                                                <li>{{ $est->nombre }}</li>
                                            @endforeach
                                        </ul>
                                    </li>
                                @endif
                            </ul>

                            @if ($employee)
                                <form action="{{ route('profile.avatar') }}" method="POST" role="form" enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-group{{ $errors->has('photo') ? ' has-error' : '' }} text-center">
                                        <label class="btn btn-round btn-default btn-sm btn-file">
                                            <i class="fa fa-cloud-upload"></i> Seleccionar foto
                                            <input type="file" name="photo" id="photo" accept="image/*" style="display: none;">
                                        </label>
                                        <button type="submit" class="btn btn-round btn-success btn-sm">
                                            <i class="fa fa-save"></i> Guardar foto
                                        </button>
                                        @if ($errors->has('photo'))
                                            <span class="help-block"><strong>{{ $errors->first('photo') }}</strong></span>
                                        @endif
                                    </div>
                                </form>
                            @endif
                        </div>

                        <div class="col-md-8 col-sm-8 col-xs-12">
                            <!-- Vincular con Personal / Funcionario (Opcional) -->
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title" style="font-size: 16px;">
                                        <i class="fa fa-address-card-o"></i> Personal / Funcionario Vinculado <small class="text-muted">(Opcional)</small>
                                    </h4>
                                </div>
                                <div class="panel-body">
                                    <p class="text-muted" style="margin-bottom: 15px;">
                                        Puede vincular su cuenta a un funcionario registrado, cambiarlo o dejar la cuenta sin vincular.
                                    </p>

                                    <form class="form-horizontal form-label-left" method="POST" action="{{ route('profile.update.employee') }}">
                                        @method('PUT')
                                        @csrf

                                        <div class="form-group{{ $errors->has('employee_id') ? ' has-error' : '' }}">
                                            <label for="profile_employee_id" class="col-md-4 col-sm-4 col-xs-12 control-label">Funcionario</label>
                                            <div class="col-md-8 col-sm-8 col-xs-12">
                                                <select id="profile_employee_id" name="employee_id" class="form-control gam-employee-select" data-placeholder="-- Sin vincular (opcional) --">
                                                    <option value="">-- Sin vincular (opcional) --</option>
                                                    @foreach ($employees as $empId => $empFullName)
                                                        <option value="{{ $empId }}" {{ (string) old('employee_id', $user->employee_id) === (string) $empId ? 'selected' : '' }}>
                                                            {{ $empFullName }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <span class="help-block">Haga clic en el selector para buscar por nombre, apellido o cargo, o presione <strong>×</strong> para desvincular.</span>
                                                @if ($errors->has('employee_id'))
                                                    <span class="help-block"><strong>{{ $errors->first('employee_id') }}</strong></span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="form-group" style="margin-bottom: 0;">
                                            <div class="col-md-8 col-sm-8 col-xs-12 col-md-offset-4 col-sm-offset-4">
                                                <button type="submit" class="btn btn-success">
                                                    <i class="fa fa-save"></i> Guardar Vinculación
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Cambiar Contraseña -->
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title" style="font-size: 16px;">
                                        <i class="fa fa-lock"></i> Cambiar Contraseña
                                    </h4>
                                </div>
                                <div class="panel-body">
                                    <p class="text-muted" style="margin-bottom: 15px;">
                                        Por seguridad, utilice una contraseña de al menos 6 caracteres y no la comparta con terceros.
                                    </p>

                                    <form class="form-horizontal form-label-left" method="POST" action="{{ route('profile.update.password') }}">
                                        @method('PUT')
                                        @csrf

                                        <div class="form-group{{ $errors->has('current-password') ? ' has-error' : '' }}">
                                            <label for="current-password" class="col-md-4 col-sm-4 col-xs-12 control-label">Contraseña Actual</label>
                                            <div class="col-md-8 col-sm-8 col-xs-12">
                                                <div class="input-group">
                                                    <input id="current-password" type="password" class="form-control" name="current-password" placeholder="Contraseña Actual" required>
                                                    <span class="input-group-btn">
                                                        <button type="button" class="btn btn-default toggle-password" data-target="#current-password" title="Mostrar / Ocultar">
                                                            <i class="fa fa-eye"></i>
                                                        </button>
                                                    </span>
                                                </div>
                                                @if ($errors->has('current-password'))
                                                    <span class="help-block"><strong>{{ $errors->first('current-password') }}</strong></span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="form-group{{ $errors->has('new-password') ? ' has-error' : '' }}">
                                            <label for="new-password" class="col-md-4 col-sm-4 col-xs-12 control-label">Contraseña Nueva</label>
                                            <div class="col-md-8 col-sm-8 col-xs-12">
                                                <div class="input-group">
                                                    <input id="new-password" type="password" class="form-control" name="new-password" minlength="6" placeholder="Mínimo 6 caracteres" required>
                                                    <span class="input-group-btn">
                                                        <button type="button" class="btn btn-default toggle-password" data-target="#new-password" title="Mostrar / Ocultar">
                                                            <i class="fa fa-eye"></i>
                                                        </button>
                                                    </span>
                                                </div>
                                                @if ($errors->has('new-password'))
                                                    <span class="help-block"><strong>{{ $errors->first('new-password') }}</strong></span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="new-password-confirm" class="col-md-4 col-sm-4 col-xs-12 control-label">Confirmar Contraseña</label>
                                            <div class="col-md-8 col-sm-8 col-xs-12">
                                                <div class="input-group">
                                                    <input id="new-password-confirm" type="password" class="form-control" name="new-password_confirmation" minlength="6" placeholder="Repita la nueva contraseña" required>
                                                    <span class="input-group-btn">
                                                        <button type="button" class="btn btn-default toggle-password" data-target="#new-password-confirm" title="Mostrar / Ocultar">
                                                            <i class="fa fa-eye"></i>
                                                        </button>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group" style="margin-bottom: 0;">
                                            <div class="col-md-8 col-sm-8 col-xs-12 col-md-offset-4 col-sm-offset-4">
                                                <button type="submit" class="btn btn-success">
                                                    <i class="fa fa-key"></i> Actualizar Contraseña
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    (function ($) {
        $(document).on('click', '.toggle-password', function () {
            var $input = $($(this).data('target'));
            var $icon = $(this).find('i');
            if ($input.attr('type') === 'password') {
                $input.attr('type', 'text');
                $icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                $input.attr('type', 'password');
                $icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });
    })(jQuery);
</script>
@endsection
