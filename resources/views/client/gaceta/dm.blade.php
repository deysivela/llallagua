@extends('client.layouts.index')
@section('title', 'Decretos Municipales | GAM Llallagua')
@section('content')
    @include('client.partials.page-banner', [
        'eyebrow' => 'Gaceta Municipal',
        'title' => 'Decretos Municipales',
        'subtitle' => 'Decretos emitidos por el Gobierno Autónomo Municipal de Llallagua',


    ])
    <section class="gam-inner-section">
        <div class="container">
            @include('client.partials.doc-list', [
                'documents' => $dm,
                'label' => 'Decreto municipal',
                'intro' => 'Decretos municipales ordenados por fecha de emisión.',
                'searchPlaceholder' => 'Buscar decreto por código, título o descripción...',
                'emptyText' => 'Todavía no hay decretos municipales publicados.',
            ])
            @include('client.partials.doc-nav')
        </div>
    </section>
@endsection
