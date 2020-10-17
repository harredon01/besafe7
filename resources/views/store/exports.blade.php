@extends('layouts.app')

@section('content')
<div class="container-fluid" ng-controller="ExportsCtrl">
    <div class="row">
        <div class="col-md-8 col-md-offset-2"> 
            <div class="panel panel-default">
                <div class="panel-body">
                    <select ng-model="typeExp" >
                        <option value="availabilities">Horarios de reservas</option>
                        <option value="products">Productos</option>
                        <option value="quick">Inventario</option>
                        <option value="orders">Ordenes</option>
                    </select><br/>
                    <label for="from" ng-if="typeExp == 'orders'">From</label>
                    <input type="text" id="from" ng-model="from" name="from"  ng-if="typeExp == 'orders'">
                    <label for="to"  ng-if="typeExp == 'orders'">to</label>
                    <input type="text" id="to" name="to" ng-model="to"  ng-if="typeExp == 'orders'"><br/><br/>
                    <div class="panel panel-default" ng-if="!changeMerchant">
                        <div class="panel-heading">Negocio Activo</div>
                        <div class="panel-body">
                            Id: <span class="type">@{{ activeMerchantObject.id}}</span><br/>
                            Name: <span class="type">@{{ activeMerchantObject.name}}</span><br/>
                            <button type="submit" class="btn btn-primary" ng-click="changeActiveMerchant()">
                                Cambiar
                            </button><br/><br/>
                            Proveedor Envios
                            <select ng-model="activeProvider" ng-change="changeScenario()">
                                <option ng-repeat="provider in providers" value="@{{ provider.value}}">@{{ provider.name}}</option>
                            </select><br/><br/><br/>
                            <button type="submit" class="btn btn-primary" ng-click="createItem()">
                                Crear nuevo Poligono
                            </button>
                            <br/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-default" ng-if="changeMerchant">
                <div class="panel-heading">Cambiar Negocio</div>
                <div class="panel-body">
                    <input type="text" name="search" ng-model="searchTerms"/>
                    <button type="submit" class="btn btn-primary" ng-click="getMerchants()">
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
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">Cargar Alertas</div>
                <div class="panel-body">
                    <form class="form-horizontal" enctype="multipart/form-data" role="form" method="POST" action="{{ url('/admin/store/global') }}">
                        {{ csrf_field() }}

                        <div class="form-group{{ $errors->has('uploadfile') ? ' has-error' : '' }}">
                            <label for="uploadfile" class="col-md-4 control-label">Subir excel</label>

                            <div class="col-md-6">
                                <input id="uploadfile" type="file" class="form-control" name="uploadfile" >

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
