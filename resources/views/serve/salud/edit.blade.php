@extends('serve.layouts.index')
@section('content')
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2 style="font-size: 18px;">Salud &mdash; Editar {{ $establecimiento->nombre }}</h2>
                <ul class="nav navbar-right panel_toolbox">
                    <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
                    <li><a class="close-link"><i class="fa fa-close"></i></a></li>
                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                @include('serve.salud.partials.flash')
                {!! Form::model($establecimiento, ['route' => ['salud.update', $establecimiento->id], 'method' => 'PUT', 'class' => 'form-horizontal form-label-left', 'id' => 'salud-form']) !!}
                    @include('serve.salud.partials.form')
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection
