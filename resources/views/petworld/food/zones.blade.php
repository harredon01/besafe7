@extends(config("app.views").'.layouts.app')

@section('content')
<div class="container" ng-controller="ZonesCtrl">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Negocio Activo</div>
                <div class="panel-body">
                    <!--button type="submit" class="btn btn-primary" ng-click="changeActiveMerchant()">
                        Cambiar
                    </button><br/><br/-->
                    Proveedor
                    <select ng-model="activeMerchant" ng-change="selectMerchantObject()">
                        <option ng-repeat="merchant in merchants" value="@{{ merchant.id}}">@{{ merchant.name}}</option>
                    </select><br/><br/><br/>
                    <button type="submit" class="btn btn-primary" ng-click="createItem()">
                        Crear nuevo Poligono
                    </button>
                    <br/>
                </div>
            </div>
            <!--div class="panel panel-default" ng-if="changeMerchant">
                <div class="panel-heading">Cambiar Negocio</div>
                <div class="panel-body">
                    <input type="text" name="search" ng-model="searchTerms"/>
                    <button type="submit" class="btn btn-primary" ng-click="searchMerchants()">
                        Buscar
                    </button>
                    <br/>
                    <button type="submit" class="btn btn-primary" ng-click="cancelChangeMerchant()">
                        Cancelar
                    </button>
                    <br/>
                    <ul>
                        <li id="item-@{{ item.id}}" ng-repeat="item in merchants">
                            Id: <span class="type">@{{ item.id}}</span><br/>
                            Name: <span class="type">@{{ item.name}}</span>
                            <a href="javascript:;" ng-click="selectMerchant(item)" class="editar">Select</a><br/>
                        </li>
                    </ul>
                </div>
            </div-->

            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="replace-address">
                        @include(config("app.views").'.food.zonesList')
                    </div>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">Cargar Zonas</div>
                <div class="panel-body">
                    <form class="form-horizontal" role="form" enctype="multipart/form-data" method="POST" action="{{ url('/admin/zones')}}">
                        {{ csrf_field()}}

                        <div class="form-group{{ $errors->has('firstName') ? ' has-error' : ''}}">
                            <label for="firstName" class="col-md-4 control-label">Subir excel</label>

                            <div class="col-md-6">
                                <input id="firstName" type="file" class="form-control" name="uploadfile" >

                                @if ($errors->has('uploadfile'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('uploadfile')}}</strong>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/angular-google-maps/2.3.2/angular-google-maps.min.js" async></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCOlc_3d8ygnNCMRzfEpmvSNsYtmbowtYo"></script>
@endsection
