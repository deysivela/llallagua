@extends('serve.layouts.index')
@section('content')
<div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2 style="font-size: 18px;">Actualizar Usuario<small></small></h2>
        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        <ul class="nav navbar-right panel_toolbox">
          <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
          </li>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-wrench"></i></a>
            <ul class="dropdown-menu" role="menu">
              <li><a href="#">Settings 1</a>
              </li>
              <li><a href="#">Settings 2</a>
              </li>
            </ul>
          </li>
          <li><a class="close-link"><i class="fa fa-close"></i></a>
          </li>
        </ul>
        <div class="clearfix"></div>
      </div>
      <div class="x_content form-horizontal form-label-left">
        <br />
        {!! Form::model($user, ['route'=>['users.update', $user->id], 'method'=>'PUT']) !!}
          <div class="col-md-6 col-sm-6 col-xs-12">

  <div class="form-group">
    {{ Form::label('employee_id','Funcionario (Opcional)', ['class'=>'control-label col-md-4 col-sm-4 col-xs-12']) }}
    <div class="col-md-8 col-sm-8 col-xs-12{{ $errors->has('employee_id') ? ' has-error' : '' }}">
    {{ Form::select('employee_id', $employees, old('employee_id', $user->employee_id), ['id' => 'employee_id', 'class'=>'form-control gam-employee-select', 'placeholder'=>'-- Sin vincular (opcional) --', 'data-placeholder'=>'-- Sin vincular (opcional) --']) }}
    <span class="help-block">{{ $errors->first('employee_id') ?: 'Haga clic en el selector para buscar por nombre, apellido o cargo, o presione × para desvincular.' }}</span>
    </div>
  </div>

  <div class="form-group">
    <label class="control-label col-md-4 col-sm-4 col-xs-12">Estado</label>
    <div class="col-md-8 col-sm-8 col-xs-12{{ $errors->has('statu') ? ' has-error' : '' }}">
      <select class="form-control" name="statu" required>
        <option value="" disabled {{ old('statu', $user->statu) ? '' : 'selected' }} hidden>Seleccione su Estado</option>
        <option value="Activo" {{ old('statu', $user->statu) === 'Activo' ? 'selected' : '' }}>Activo</option>
        <option value="Inactivo" {{ old('statu', $user->statu) === 'Inactivo' ? 'selected' : '' }}>Inactivo</option>
      </select>
      <span class="help-block">{{ $errors->first('statu') }}</span>
    </div>
  </div>

</div>
  <div class="col-md-6 col-sm-6 col-xs-12">
    <div class="form-group">
    {{ Form::label('user','Usuario', ['class'=>'control-label col-md-4 col-sm-4 col-xs-12']) }}
    <div class="col-md-8 col-sm-8 col-xs-12{{ $errors->has('user') ? ' has-error' : '' }}">
    {{ Form::text('user',null,['class'=>'form-control','placeholder'=>'Ingrese su Nombre de Usuario']) }}
    <span class="help-block">{{ $errors->first('user') }}</span>
    </div>
  </div>
  <div class="form-group">
    {{ Form::label('password','Contraseña (Opcional)', ['class'=>'control-label col-md-4 col-sm-4 col-xs-12']) }}
    <div class="col-md-8 col-sm-8 col-xs-12{{ $errors->has('password') ? ' has-error' : '' }}">
    {{ Form::input('password','password',null,['class'=>'form-control','placeholder'=>'Dejar vacío para mantener la actual']) }}
    <span class="help-block">{{ $errors->first('password') }}</span>
    </div>
  </div>
  <div class="form-group">
    {{ Form::label('password_confirmation','Confirmar Contraseña', ['class'=>'control-label col-md-4 col-sm-4 col-xs-12']) }}
    <div class="col-md-8 col-sm-8 col-xs-12{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
    {{ Form::input('password','password_confirmation',null,['class'=>'form-control','placeholder'=>'Repita la nueva contraseña']) }}
    <span class="help-block">{{ $errors->first('password_confirmation') }}</span>
    </div>
  </div>

</div>


<div class="jumbotron" hidden="">
  <h3>Lista de roles</h3>
  <div class="form-group">
    <ul class="list-unstyled">
      @foreach($roles as $role)
      <li>
        <label>
          {{ Form::checkbox('roles[]',$role->id, null)}}
          {{ $role->name }}
          <em>({{ $role->description ?: 'Sin Descripcion'  }})</em>
        </label>
      </li>
      @endforeach
    </ul>
  </div>
</div>

  <div class="clearfix"></div>
  <div class="ln_solid"></div>
  <div class="form-group">
    <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-4">
      <a href="{{ route('users.index') }}" class="btn btn-primary">Cancelar</a>
      {{ Form::submit('Guardar', ['class'=>'btn btn-success']) }}
    </div>
  </div>
        {!! Form::close() !!}

      </div>
    </div>
  </div> 
</div>
@endsection