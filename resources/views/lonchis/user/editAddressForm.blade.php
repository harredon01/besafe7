<form class="form-horizontal" role="form" name="myForm" ng-submit="save(myForm.$valid)" novalidate>
    <input type="hidden" ng-model="data.address_id" name="address_id" value="">
    <input type="hidden" ng-model="data.section" name="section" value="profile">
    <input type="hidden" ng-model="data.lat" name="lat">
    <input type="hidden" ng-model="data.long" name="long">
    <div class="form-group">
        <label class="col-md-4 control-label">Por quien Preguntar</label>
        <div class="col-md-6">
            <input type="text" ng-model="data.name" class="form-control" name="name" value="{{ old('name')}}" required>
            <span style="color:red" ng-show="(myForm.name.$dirty && myForm.name.$invalid) || submitted && myForm.name.$invalid">
                <span ng-show="submitted && myForm.name.$error.required">Porfavor ingresa por quien Preguntar</span>                                
        </div>
    </div>


    <div class="form-group">
        <label class="col-md-4 control-label">Direccion</label>
        <div class="col-md-6">
            <input type="text" ng-model="data.address" class="form-control" name="address" value="{{ old('address')}}" required>
            <span style="color:red" ng-show="(myForm.address.$dirty && myForm.address.$invalid) || submitted && myForm.address.$invalid">
                <span ng-show="submitted && myForm.address.$error.required">Porfavor ingresa la direccion</span>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label">Indicaciones</label>
        <div class="col-md-6">
            <input type="text" ng-model="data.notes" class="form-control" name="notes" value="{{ old('notes')}}" required>
            <span style="color:red" ng-show="(myForm.notes.$dirty && myForm.notes.$invalid) || submitted && myForm.notes.$invalid">
                <span ng-show="submitted && myForm.notes.$error.required">Porfavor Ingresa las Indicaciones</span>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label">Telefono</label>
        <div class="col-md-6">
            <input type="text" ng-model="data.phone" class="form-control" name="phone" value="{{ old('phone')}}" required>
            <span style="color:red" ng-show="(myForm.phone.$dirty && myForm.phone.$invalid) || submitted && myForm.phone.$invalid">
                <span ng-show="submitted && myForm.phone.$error.required">Porfavor ingresa un telefono</span>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label">Codigo Postal</label>
        <div class="col-md-6">
            <input type="text" ng-model="data.postal" class="form-control" name="postal" value="{{ old('postal')}}" required>
            <span style="color:red" ng-show="(myForm.postal.$dirty && myForm.postal.$invalid) || submitted && myForm.postal.$invalid">
                <span ng-show="submitted && myForm.postal.$error.required">Porfavor ingresa un codigo postal</span>
        </div>
    </div>
    <div class="form-group" ng-show="regionVisible">
        <label class="col-md-4 control-label">Region</label>
        <div class="col-md-6">
            <select ng-model="data.region_id" name="region_id" ng-change="selectRegion(data.region_id)" 
                    ng-options="region.id as region.name for region in regions" required>
                
            </select>
            <span style="color:red" ng-show="(myForm.region_id.$dirty && myForm.region_id.$invalid) || submitted && myForm.region_id.$invalid">
                <span ng-show="submitted && myForm.region_id.$error.required">Porfavor Selecciona una Region</span>
        </div>
    </div>

    <div class="form-group">
        <label class="col-md-4 control-label">Country</label>
        <div class="col-md-6">
            <select ng-model="data.country_id" name="country_id" ng-change="selectCountry(data.country_id)" 
                     ng-options="country.id as country.name for country in countries"  required>
            </select>
            <span style="color:red" ng-show="(myForm.country_id.$dirty && myForm.country_id.$invalid) || submitted && myForm.country_id.$invalid">
                <span ng-show="submitted && myForm.country_id.$error.required">Porfavor Selecciona un país</span>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label">Ciudad</label>
        <div class="col-md-6">
            <select ng-model="data.city_id" name="city_id" ng-change="selectCountry(data.city_id)" 
                     ng-options="city.id as city.name for city in cities"  required>
            </select>
            <span style="color:red" ng-show="(myForm.city_id.$dirty && myForm.city_id.$invalid) || submitted && myForm.city_id.$invalid">
                <span ng-show="submitted && myForm.city_id.$error.required">Porfavor Selecciona una ciudad</span>
        </div>
    </div>
    <div class="form-group">
        <div class="col-md-6 col-md-offset-4">
            <button type="submit" class="btn btn-primary">Save</button>
            <button ng-click="clean()" class="btn btn-primary">Clean</button>

        </div>
    </div>
</form>