<form class="form-horizontal" role="form" name="myForm" ng-submit="save(myForm.$valid)" novalidate>
    <input type="hidden" name="_token" value="{{ csrf_token()}}">
    <input type="hidden" ng-model="data.address_id" name="address_id" value="">
    <input type="hidden" ng-model="data.section" name="section" value="profile">
    <div class="form-group">
        <label class="col-md-4 control-label">Name</label>
        <div class="col-md-6">
            <input type="text" ng-model="data.name" class="form-control" name="name" value="{{ old('name')}}" required>
            <span style="color:red" ng-show="(myForm.name.$dirty && myForm.name.$invalid) || submitted && myForm.name.$invalid">
                <span ng-show="submitted && myForm.name.$error.required">Porfavor ingresa tu nombre</span>                                
        </div>
    </div>


    <div class="form-group">
        <label class="col-md-4 control-label">Address</label>
        <div class="col-md-6">
            <input type="text" ng-model="data.address" class="form-control" name="address" value="{{ old('address')}}" required>
            <span style="color:red" ng-show="(myForm.address.$dirty && myForm.address.$invalid) || submitted && myForm.address.$invalid">
                <span ng-show="submitted && myForm.address.$error.required">Porfavor ingresa la direccion</span>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label">City</label>
        <div class="col-md-6">
            <input type="text" ng-model="data.city" class="form-control" name="city" value="{{ old('city')}}" required>
            <span style="color:red" ng-show="(myForm.city.$dirty && myForm.city.$invalid) || submitted && myForm.city.$invalid">
                <span ng-show="submitted && myForm.city.$error.required">Porfavor Ingresa la ciudad</span>
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
            <select ng-model="data.region_id" name="region_id" ng-change="selectRegion(data.region_id)" required>
                <option ng-repeat="region in regions" value="@{{region.id}}">@{{region.name}}</option>
            </select>
            <span style="color:red" ng-show="(myForm.region_id.$dirty && myForm.region_id.$invalid) || submitted && myForm.region_id.$invalid">
                <span ng-show="submitted && myForm.region_id.$error.required">Porfavor Selecciona una Region</span>
        </div>
    </div>

    <div class="form-group">
        <label class="col-md-4 control-label">Country</label>
        <div class="col-md-6">
            <select ng-model="data.country_id" name="country_id" ng-change="selectCountry(data.country_id)" >
                <option ng-repeat="country in countries" value="@{{country.id}}">@{{country.name}}</option>
            </select>
            <span style="color:red" ng-show="(myForm.country_id.$dirty && myForm.country_id.$invalid) || submitted && myForm.country_id.$invalid">
                <span ng-show="submitted && myForm.country_id.$error.required">Porfavor Selecciona un país</span>
        </div>
    </div>

    <div class="form-group">
        <div class="col-md-6 col-md-offset-4">
            <button type="submit" class="btn btn-primary">Save</button>
            <button ng-click="clean()" class="btn btn-primary">Clean</button>

        </div>
    </div>
</form>