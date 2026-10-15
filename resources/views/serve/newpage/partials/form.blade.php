@php
	$tipo = old('tipo', (isset($newpage) && $newpage->facebookLink() && !$newpage->coverImage()) ? 'enlace' : 'completa');
@endphp
{{ Form::hidden('user_id', auth()->user()->id) }}

<div class="col-md-12 col-sm-12 col-xs-12">
	<div class="form-group">
		<label class="control-label">Tipo de noticia</label>
		<div class="gam-news-type">
			<label class="gam-news-type-option">
				<input type="radio" name="tipo" value="completa" {{ $tipo === 'completa' ? 'checked' : '' }}>
				<span>
					<strong>Noticia completa</strong>
					<small>Título, fotografía y contenido</small>
				</span>
			</label>
			<label class="gam-news-type-option">
				<input type="radio" name="tipo" value="enlace" {{ $tipo === 'enlace' ? 'checked' : '' }}>
				<span>
					<strong>Enlace de Facebook</strong>
					<small>Título y enlace o código para incrustar</small>
				</span>
			</label>
		</div>
		@if ($errors->has('tipo'))
			<span class="error" role="alert"><strong>{{ $errors->first('tipo') }}</strong></span>
		@endif
	</div>
	<div class="form-group">
		{{ Form::label('titulo','Título de la noticia') }}
		{{ Form::textarea('titulo',null,['class'=>'form-control','rows'=>'2','placeholder'=>'Ejemplo Campaña de vacunación en Llallagua']) }}
		@if ($errors->has('titulo'))
			<span class="error" role="alert"><strong>{{ $errors->first('titulo') }}</strong></span>
		@endif
	</div>
</div>

<div id="news-block-completa">
	<div class="col-md-8 col-sm-8 col-xs-12">
		<div class="form-group">
			{{ Form::label('contenido','Contenido (opcional)') }}
			{{ Form::textarea('contenido',null,['class'=>'form-control','rows'=>'8','maxlength'=>10000,'placeholder'=>'Escriba el texto de la noticia']) }}
			<small>Opcional. Si lo deja vacío, se mostrará el título y la fotografía.</small>
			@if ($errors->has('contenido'))
				<span class="error" role="alert"><strong>{{ $errors->first('contenido') }}</strong></span>
			@endif
		</div>
	</div>
	<div class="col-md-4 col-sm-4 col-xs-12">
		<div class="form-group">
			{{ Form::label('photo', 'Fotografía') }}
			{{ Form::file('photo', ['class'=>'form-control','accept'=>'.jpg,.jpeg,.png,image/jpeg,image/png']) }}
			<small>JPG o PNG · máximo 5MB.</small><br>
			@if(isset($newpage) && $newpage->photo)
				<small>Actual: <a href="{{ asset($newpage->photo) }}" target="_blank" rel="noopener">ver imagen</a></small><br>
			@endif
			@if ($errors->has('photo'))
				<span class="error" role="alert"><strong>{{ $errors->first('photo') }}</strong></span>
			@endif
		</div>
	</div>
</div>

<div id="news-block-enlace" class="col-md-12 col-sm-12 col-xs-12">
	<div class="form-group">
		{{ Form::label('facebook_url','Enlace o código de Facebook') }}
		{{ Form::textarea('facebook_url', null, ['class'=>'form-control','id'=>'facebook-url-input','rows'=>'4','placeholder'=>'Pegue el enlace o el iframe de Incrustar']) }}
		<small>
			Puede pegar el <strong>enlace</strong> (<em>/posts/</em>, <em>/photos/</em>, <em>/videos/</em>) o el <strong>código iframe</strong> (tres puntos → Incrustar).
		</small>
		<div id="facebook-preview" class="gam-fb-preview" hidden></div>
		@if ($errors->has('facebook_url'))
			<span class="error" role="alert"><strong>{{ $errors->first('facebook_url') }}</strong></span>
		@endif
	</div>
</div>

<style>
	.gam-news-type { display: flex; flex-wrap: wrap; gap: 12px; margin: 6px 0 16px; }
	.gam-news-type-option {
		flex: 1 1 240px;
		display: flex;
		align-items: flex-start;
		gap: 10px;
		margin: 0;
		padding: 12px 14px;
		border: 1px solid #d9dee4;
		border-radius: 8px;
		cursor: pointer;
		font-weight: 400;
	}
	.gam-news-type-option input { margin-top: 4px; }
	.gam-news-type-option strong { display: block; }
	.gam-news-type-option small { display: block; color: #6a737d; }
	.gam-fb-preview {
		margin-top: 12px;
		padding: 12px;
		background: #f0f2f5;
		border: 1px solid #dddfe2;
		border-radius: 8px;
		min-height: 80px;
		overflow: auto;
		max-width: 100%;
	}
	.gam-fb-preview iframe, .gam-fb-preview .fb-post, .gam-fb-preview .fb-video { max-width: 100% !important; }
	.gam-fb-help { color: #3d4a5c; font-size: 13px; line-height: 1.5; }
	.gam-fb-help p { margin: 0 0 8px; }
</style>
<script>
(function () {
	function selectedTipo() {
		var checked = document.querySelector('input[name="tipo"]:checked');
		return checked ? checked.value : 'completa';
	}
	function syncNewsType() {
		var tipo = selectedTipo();
		var completa = document.getElementById('news-block-completa');
		var enlace = document.getElementById('news-block-enlace');
		var fb = document.getElementById('facebook-url-input');
		if (completa) completa.style.display = tipo === 'completa' ? '' : 'none';
		if (enlace) enlace.style.display = tipo === 'enlace' ? '' : 'none';
		if (fb) fb.disabled = tipo !== 'enlace';
	}
	var radios = document.querySelectorAll('input[name="tipo"]');
	for (var i = 0; i < radios.length; i++) {
		radios[i].addEventListener('change', syncNewsType);
	}
	syncNewsType();
})();
</script>

<div class="clearfix"></div>
<div class="ln_solid"></div>

<div class="form-group">
	<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-4">
		<a href="{{ route('newpages.index') }}" class="btn btn-primary">Cancelar</a>
		{{ Form::submit(isset($newpage) ? 'Actualizar' : 'Guardar',['class'=>'btn btn-success']) }}
	</div>
</div>
