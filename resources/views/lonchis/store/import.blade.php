@extends(config("app.views").'.layouts.app')

@section('content')
<div class="container-fluid" ng-controller="ExportsCtrl">
    <div class="row">
        <div class="col-md-8 col-md-offset-2"> 
            <div class="panel panel-default" ng-if="!changeMerchant">
                <div class="panel-heading">Negocio Activo</div>
                <div class="panel-body">
                    Id: <span class="type">@{{ activeMerchantObject.id}}</span><br/>
                    Name: <span class="type">@{{ activeMerchantObject.name}}</span><br/>
                    <button type="submit" class="btn btn-primary" ng-click="changeActiveMerchant()">
                        Cambiar
                    </button><br/><br/>
                    
                    <button type="submit" class="btn btn-primary" ng-click="startExport('products')">
                        Exportar Productos
                    </button>
                    <button type="submit" class="btn btn-primary" ng-click="startExport('quick')">
                        Exportar inventario
                    </button>
                    <button type="submit" class="btn btn-primary" ng-click="startExport('availabilities')">
                        Exportar Disponibilidad
                    </button>
                    <br/>
                    <br/>
                    <br/>
                    <h3>Exportar pedidos</h3>
                    <label for="from" >From</label>
                    <input type="text" id="from" ng-model="from" name="from">
                    <label for="to">to</label>
                    <input type="text" id="to" name="to" ng-model="to"><br/><br/>

                    <button type="submit" class="btn btn-primary" ng-click="startExport('orders')">
                        Exportar Ordenes
                    </button>
                </div>
            </div>
            <div class="panel panel-default" ng-if="changeMerchant">
                <div class="panel-heading">Seleccionar Negocio</div>
                <div class="panel-body">
                    <input type="text" name="search" ng-model="searchTerms" ng-if="showSearch"/>
                    <button type="submit" class="btn btn-primary" ng-click="getMerchants()" ng-if="showSearch">
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
                            <a href="javascript:;" ng-click="selectMerchant(item)" class="btn btn-primary">Select</a><br/>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">Cargas Masivas</div>
                <div class="panel-body">
                    <form class="form-horizontal" enctype="multipart/form-data" role="form" method="POST" action="{{ url('/admin/store/global')}}">
                        {{ csrf_field()}}

                        <div class="form-group{{ $errors->has('uploadfile') ? ' has-error' : ''}}">
                            <label for="uploadfile" class="col-md-4 control-label">Subir excel</label>

                            <div class="col-md-6">
                                <input id="uploadfile" type="file" class="form-control" name="uploadfile" >

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
@endsection
