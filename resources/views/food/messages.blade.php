@extends('layouts.app')

@section('content')
<div class="container" ng-controller="FoodMessagesCtrl">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="replace-address">
                        @include('food.messagesList')
                    </div>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">Cargar Alertas</div>
                <div class="panel-body">
                    <form class="form-horizontal" role="form" method="POST" action="{{ url('/food/messages') }}">
                        {{ csrf_field() }}

                        <div class="form-group{{ $errors->has('firstName') ? ' has-error' : '' }}">
                            <label for="uploadfile" class="col-md-4 control-label">Subir excel</label>

                            <div class="col-md-6">
                                <input id="firstName" type="file" class="form-control" name="uploadfile" >

                                @if ($errors->has('uploadfile'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('uploadfile') }}</strong>
                                    </span>
                                @endif 
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    Cargar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
