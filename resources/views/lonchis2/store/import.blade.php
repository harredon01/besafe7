@extends(config("app.views").'.layouts.app')

@section('content')
<section class="main_content_area" ng-controller="UserProfileCtrl">
    <div class="container">   
        <div class="account_dashboard">
            <div class="row">
                @include(config("app.views").'.user.account_menu')
                <div class="col-sm-12 col-md-9 col-lg-9">
                    <!-- Tab panes -->
                    <div class="tab-content dashboard_content">
                        <div class="tab-pane fade  show active" id="dashboard">
                            <h3>Account details </h3>
                            <div class="login">
                                <div class="login_form_container"  ng-controller="ExportsCtrl">
                                    <div class="account_login_form">
                                        <h3>Exportar y importar</h3>

                                    <div class="panel panel-default">
                                        <div class="panel-body">
                                            <p>Porfavor selecciona un negocio para exportar</p>
                                            <select ng-model="activeMerchant" ng-change="selectMerchantObject()">
                                                <option ng-repeat="merchant in merchants" value="@{{ merchant.id}}">@{{ merchant.name}}</option>
                                            </select><br/><br/><br/>
                                            <div  ng-show="activeMerchant">
                                                <div>
                                                    <h2>Productos</h2>
                                                    <p>Con este archivo puedes actualizar todo el contenido de tus productos y sus opciones de compra. Desde sus atributos, hasta sus imagenes y categorías</p>
                                                    <p>Descargalo, haz los cambios sobre él y lo vuelves a subir</p>
                                                    <button type="submit" class="btn btn-primary" ng-click="startExport('products')">
                                                        Exportar Productos
                                                    </button>
                                                </div>
                                                <br/>
                                                <br/>
                                                <div>
                                                    <h2>Inventario</h2>
                                                    <p>Con este archivo puedes hacer actualizacion de Precios, inventario, o numero de referencia</p>
                                                    <p>Descargalo, haz los cambios sobre él y lo vuelves a subir</p>
                                                    <button type="submit" class="btn btn-primary" ng-click="startExport('quick')">
                                                        Exportar inventario
                                                    </button>
                                                </div>
                                                <br/>
                                                <br/>
                                                <div>
                                                    <h2>Disponibilidad</h2>
                                                    <p>Con este archivo puedes editar la disponibilidad para recibir citas o informacion general de tu negocio</p>
                                                    <p>Descargalo, haz los cambios sobre él y lo vuelves a subir</p>
                                                    <button type="submit" class="btn btn-primary" ng-click="startExport('availabilities')">
                                                        Exportar Disponibilidad
                                                    </button>
                                                </div>

                                                <br/>
                                                <br/>
                                                <br/>
                                                <h3>Exportar pedidos</h3>
                                                <p>Aca puedes descargar un reporte con la informacion de ventas de tu negocio. </p>
                                                <label for="from" >From</label>
                                                <input type="text" id="from" ng-model="from" name="from">
                                                <label for="to">to</label>
                                                <input type="text" id="to" name="to" ng-model="to"><br/><br/>

                                                <button type="submit" class="btn btn-primary" ng-click="startExport('orders')">
                                                    Exportar Ordenes
                                                </button>
                                            </div>

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
                        </div>
                    </div>
                </div>
            </div>
        </div>  
    </div>        	
</section>

@endsection
