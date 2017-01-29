@extends('layouts.app')

@section('content')
<div class="container" ng-controller="MapCtrl">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                
                @if (isset($report))
                <div class="panel-heading">Título: <span class="name">{{ $report->name }}</span></div>
                
                
                <div class="images">
                    @foreach ($images as $image)
                    <img src="/images/reports/{{ $image->file }}" width="300" class="thumbs"/>
                    @endforeach
                </div>
                <div class="desc">
                    Descripción: <span class="description">
                    {{ $report->description }}
                </span><br/>Dirección: <span class="address">
                    {{ $report->address }}
                </span>
                <br/>Fecha: <span class="created_at">
                    {{ $report->created_at }}
                </span>
                </div>
                
                <span class="latitude hide">
                    {{ $report->lat }}
                </span>
                <span class="id hide">
                    {{ $report->id }}
                </span>
                <span class="longitude hide">
                    {{ $report->long }}
                </span>

                <div id="map" data-tap-disabled="true"></div>
                <div class="panel-body">

                </div>
                @else 
                <div class="panel-heading">No existe reporte con ese código


                    <div class="panel-body">

                    </div>

                    @endif
                </div>
            </div>
        </div>
    </div>

    @endsection
