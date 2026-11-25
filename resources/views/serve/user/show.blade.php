@extends('serve.layouts.index')
@section('content')
<div class="row">
	<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
		<div class="x_panel">
		  <div class="x_title">
			<h2 style="font-size: 18px;">Vista en detalle del Usuario<small></small></h2>
		  	@include('serve.alerts.alerts')
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
		  <div class="x_content">
			@php
				$employee = $user->employees;
				$avatarUrl = ($employee && $employee->photo)
					? asset($employee->photo)
					: asset('server/production/images/user.png');
			@endphp
			<div class="card" style="width: 100%;">
			  <img class="card-img-top center-block" src="{{ $avatarUrl }}" alt="img" style="width: 40%;">
			  <div class="card-body">
			   <p><strong>Usuario:</strong> {{ $user->user }}</p>
			   <p><strong>Funcionario:</strong> {{ $employee ? trim($employee->name . ' ' . $employee->last_name) : 'Sin funcionario vinculado' }}</p>
			   @if($employee && optional($employee->charges)->charge_employee)
			   <p><strong>Cargo:</strong> {{ $employee->charges->charge_employee }}</p>
			   @endif
			   <p><strong>Estado:</strong> {{ $user->statu }}</p> 
				<div class="clearfix"></div>
				<div class="ln_solid"></div>
				<div class="form-group">
					<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-4">
						<a href="{{ route('users.index') }}" class="btn btn-primary">Regresar</a>
					</div>
				</div>
			  </div>
			</div>
		  </div>
		</div>
	</div>
</div>
@endsection