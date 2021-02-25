<form role="form" name="myForm" ng-submit="save(myForm.$valid)" ng-show="addAddress" novalidate>
    <input type="hidden" ng-model="data.address_id" name="address_id" value="">
    <input type="hidden" ng-model="data.section" name="section" value="profile">
    <input type="hidden" ng-model="data.lat" name="lat">
    <input type="hidden" ng-model="data.long" name="long">
    <div id="billing-form" class="mb-40">
        <h3>Direccion de envío</h3>
        <div class="row">
            <div class="col-12 mb--20">
                <label>Direccion</label>
                <input type="text" ng-model="data.address" name="address" value="{{ old('address')}}" required>
                <span style="color:red" ng-show="(myForm.address.$dirty && myForm.address.$invalid) || submitted && myForm.address.$invalid">
                    <span ng-show="submitted && myForm.address.$error.required">Porfavor ingresa la direccion</span></span>
            </div>
            <div class="col-12 mb--20">
                <label>Indicaciones (Casa, Apto)</label>
                <input type="text" ng-model="data.notes" name="notes" value="{{ old('notes')}}" required>
                <span style="color:red" ng-show="(myForm.notes.$dirty && myForm.notes.$invalid) || submitted && myForm.notes.$invalid">
                    <span ng-show="submitted && myForm.notes.$error.required">Porfavor Ingresa las Indicaciones</span></span>
            </div>
            <div class="col-md-6 col-12 mb--20">
                <label>Por quien Preguntar</label>
                <input type="text" ng-model="data.name" name="name" value="{{ old('name')}}" required>
                <span style="color:red" ng-show="(myForm.name.$dirty && myForm.name.$invalid) || submitted && myForm.name.$invalid">
                    <span ng-show="submitted && myForm.name.$error.required">Porfavor ingresa por quien Preguntar</span></span>
            </div>

            <div class="col-md-6 col-12 mb--20">
                <label>Telefono</label>
                <input type="text" ng-model="data.phone" name="phone" value="{{ old('phone')}}" required>
                <span style="color:red" ng-show="(myForm.phone.$dirty && myForm.phone.$invalid) || submitted && myForm.phone.$invalid">
                    <span ng-show="submitted && myForm.phone.$error.required">Porfavor ingresa un telefono</span></span>
            </div>
            <div class="col-md-6 col-12 mb--20">
                <label>Codigo Postal</label>
                <input type="text" ng-model="data.postal" name="postal" value="{{ old('postal')}}" required>
                <span style="color:red" ng-show="(myForm.postal.$dirty && myForm.postal.$invalid) || submitted && myForm.postal.$invalid">
                    <span ng-show="submitted && myForm.postal.$error.required">Porfavor ingresa un codigo postal</span></span>
            </div>
            <div class="col-md-6 col-12 mb--20">
                <label>Pais</label>
                <select ng-model="data.country_id" class="form-control nice-select" name="country_id" ng-change="selectCountry(data.country_id)" 
                        ng-options="country.id as country.name for country in countries"  required>
                </select>
                <span style="color:red" ng-show="(myForm.country_id.$dirty && myForm.country_id.$invalid) || submitted && myForm.country_id.$invalid">
                    <span ng-show="submitted && myForm.country_id.$error.required">Porfavor Selecciona un país</span></span>
            </div>
            <div class="col-md-6 col-12 mb--20" ng-show="regionVisible">
                <label>Region</label>
                <select ng-model="data.region_id" class="form-control nice-select" name="region_id" ng-change="selectRegion(data.region_id)" 
                        ng-options="region.id as region.name for region in regions" required>

                </select>
                <span style="color:red" ng-show="(myForm.region_id.$dirty && myForm.region_id.$invalid) || submitted && myForm.region_id.$invalid">
                    <span ng-show="submitted && myForm.region_id.$error.required">Porfavor Selecciona una Region</span></span>
            </div>

            
            <div class="col-md-6 col-12 mb--20">
                <label>Ciudad</label>
                <select ng-model="data.city_id" class="form-control nice-select" name="city_id" 
                        ng-options="city.id as city.name for city in cities"  required>
                </select>
                <span style="color:red" ng-show="(myForm.city_id.$dirty && myForm.city_id.$invalid) || submitted && myForm.city_id.$invalid">
                    <span ng-show="submitted && myForm.city_id.$error.required">Porfavor Selecciona una ciudad</span></span>
            </div>
            <br/><br/><br/>
            <div class="col-12 mb--20" style="margin-top:15px">
                <button ng-click="clean()" class="btn btn-dark">Limpiar</button>
                <button type="submit" style="float:right" class="btn btn-dark">Enviar</button> 
            </div>
        </div>
    </div>

</form>