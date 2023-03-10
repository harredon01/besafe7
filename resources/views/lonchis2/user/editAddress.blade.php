@extends(config("app.views").'.layouts.app')

@section('content')
<section class="main_content_area" ng-controller="AddressesCtrl">
    <div class="container">   
        <div class="account_dashboard">
            <div class="row">
                @include(config("app.views").'.user.account_menu')
                <div class="col-sm-12 col-md-9 col-lg-9">
                    <!-- Tab panes -->
                    <div class="tab-content dashboard_content">
                        <div class="tab-pane fade  show active" id="dashboard">
                            <h4 class="billing-address">Direcciones</h4>
                            <div ng-repeat="address in addresses" ng-hide="editAddress">
                                <address>
                                    <p><strong>@{{ address.name}}</strong></p>
                                    <p>@{{ address.address}},@{{ address.notes}} <br>
                                        @{{ address.cityName}}, @{{ address.postal}}</p>
                                    <p>Tel: @{{ address.phone}}</p>
                                </address>

                                <a href="javascript:;" ng-click="editAddressObj(address)" class="theme-btn"><i class="fa fa-edit"></i>Editar</a>
                                <a href="javascript:;" ng-click="deleteAddress(address)" class="theme-btn"><i class="fa fa-trash"></i>Borrar</a>
                            </div>

                            <h3 ng-show="editAddress">Editar </h3>
                            <div class="login" ng-show="editAddress">
                                <div class="login_form_container">
                                    <div class="account_login_form">
                                        <form role="form" name="myForm" ng-submit="save(myForm.$valid)" novalidate>
                                            <input type="hidden" ng-model="data.address_id" name="address_id" value="">
                                            <input type="hidden" ng-model="data.section" name="section" value="profile">
                                            <input type="hidden" ng-model="data.lat" name="lat">
                                            <input type="hidden" ng-model="data.long" name="long">
                                            <label>Por quien Preguntar</label>
                                                    <input type="text" ng-model="data.name" name="name" value="{{ old('name')}}" required>
                                                    <span style="color:red" ng-show="(myForm.name.$dirty && myForm.name.$invalid) || submitted && myForm.name.$invalid">
                                                        <span ng-show="submitted && myForm.name.$error.required">Porfavor ingresa por quien Preguntar</span></span>  
                                                    
                                            <label>Direccion</label>
                                                    <input type="text" ng-model="data.address" name="address" value="{{ old('address')}}" required>
                                                    <span style="color:red" ng-show="(myForm.address.$dirty && myForm.address.$invalid) || submitted && myForm.address.$invalid">
                                                        <span ng-show="submitted && myForm.address.$error.required">Porfavor ingresa la direccion</span></span>
                                            <label>Indicaciones</label>
                                                    <input type="text" ng-model="data.notes" name="notes" value="{{ old('notes')}}" required>
                                                    <span style="color:red" ng-show="(myForm.notes.$dirty && myForm.notes.$invalid) || submitted && myForm.notes.$invalid">
                                                        <span ng-show="submitted && myForm.notes.$error.required">Porfavor Ingresa las Indicaciones</span></span>
                                            <label>Telefono</label>
                                                    <input type="text" ng-model="data.phone"  name="phone" value="{{ old('phone')}}" required>
                                                    <span style="color:red" ng-show="(myForm.phone.$dirty && myForm.phone.$invalid) || submitted && myForm.phone.$invalid">
                                                        <span ng-show="submitted && myForm.phone.$error.required">Porfavor ingresa un telefono</span></span>
                                            <label>Codigo Postal</label>
                                                    <input type="text" ng-model="data.postal" name="postal" value="{{ old('postal')}}" required>
                                                    <span style="color:red" ng-show="(myForm.postal.$dirty && myForm.postal.$invalid) || submitted && myForm.postal.$invalid">
                                                        <span ng-show="submitted && myForm.postal.$error.required">Porfavor ingresa un codigo postal</span></span>
                                            <div class="col-lg-6 col-12 mb-30" ng-show="regionVisible">
                                                    <label>Departamento</label><br/>
                                                    <select ng-model="data.region_id" class="nice-select" name="region_id" ng-change="selectRegion(data.region_id)" 
                                                            ng-options="region.id as region.name for region in regions" required>

                                                    </select>
                                                    <span style="color:red" ng-show="(myForm.region_id.$dirty && myForm.region_id.$invalid) || submitted && myForm.region_id.$invalid">
                                                        <span ng-show="submitted && myForm.region_id.$error.required">Porfavor Selecciona una Region</span></span>
                                                </div>

                                                <div class="col-lg-6 col-12 mb-30">
                                                    <label>Pais</label><br/>
                                                    <select ng-model="data.country_id" class="nice-select" name="country_id" ng-change="selectCountry(data.country_id)" 
                                                            ng-options="country.id as country.name for country in countries"  required>
                                                    </select>
                                                    <span style="color:red" ng-show="(myForm.country_id.$dirty && myForm.country_id.$invalid) || submitted && myForm.country_id.$invalid">
                                                        <span ng-show="submitted && myForm.country_id.$error.required">Porfavor Selecciona un pa??s</span></span>
                                                </div>
                                                <div class="col-lg-6 col-12 mb-30">
                                                    <label>Ciudad</label><br/>
                                                    <select ng-model="data.city_id" class="nice-select" name="city_id" ng-change="selectCountry(data.city_id)" 
                                                            ng-options="city.id as city.name for city in cities"  required>
                                                    </select>
                                                    <span style="color:red" ng-show="(myForm.city_id.$dirty && myForm.city_id.$invalid) || submitted && myForm.city_id.$invalid">
                                                        <span ng-show="submitted && myForm.city_id.$error.required">Porfavor Selecciona una ciudad</span></span>
                                                </div>
                                                    <br/>
                                            <div class="save_button primary_btn default_button">
                                                <button type="submit">Guardar</button>
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
</section>	
@endsection
