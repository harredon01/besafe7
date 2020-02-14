<form class="form-horizontal" role="form" name="myForm" ng-submit="save(myForm.$valid)" novalidate>
    <input type="hidden" name="_token" value="{{ csrf_token()}}">
    <input type="hidden" ng-model="data.id" name="id" value="">
    <div class="form-group">
        <label class="col-md-4 control-label">Categories</label>
        <div class="col-md-12">
            <div class="checkbox-item" ng-repeat="cat in categories">
                <input type="checkbox" name="@{{cat.name}}" value="@{{cat.id}}">
                <label for="@{{cat.name}}">@{{cat.name}}</label><br>
            </div>
            <span style="color:red" ng-show="categoriesError">
                <span>Porfavor Selecciona minimo una categoria</span>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label">Nombre</label>
        <div class="col-md-6">
            <input type="text" ng-model="data.name" class="form-control" name="name" value="{{ old('name')}}" required>
            <span style="color:red" ng-show="(myForm.name.$dirty && myForm.name.$invalid) || submitted && myForm.name.$invalid">
                <span ng-show="submitted && myForm.name.$error.required">Porfavor ingresa nombre del negocio</span>                                
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label">Descripcion</label>
        <div class="col-md-6">
            <textarea ng-model="data.description" class="form-control" name="description" value="{{ old('description')}}" required></textarea>
            <span style="color:red" ng-show="(myForm.description.$dirty && myForm.description.$invalid) || submitted && myForm.description.$invalid">
                <span ng-show="submitted && myForm.description.$error.required">Porfavor nombre del local</span>                                
        </div>
    </div>


    <div class="form-group">
        <label class="col-md-4 control-label">Dirección</label>
        <div class="col-md-6">
            <input type="text" ng-model="data.address" class="form-control" name="address" value="{{ old('address')}}" required>
            <span style="color:red" ng-show="(myForm.address.$dirty && myForm.address.$invalid) || submitted && myForm.address.$invalid">
                <span ng-show="submitted && myForm.address.$error.required">Porfavor ingresa la direccion</span></span>
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
    <div class="form-group" ng-show="regionVisible">
        <label class="col-md-4 control-label">Departamento</label>
        <div class="col-md-6">
            <select ng-model="data.region_id" name="region_id" ng-change="selectRegion(data.region_id)" required>
                <option ng-repeat="region in regions" value="@{{region.id}}">@{{region.name}}</option>
            </select>
            <span style="color:red" ng-show="(myForm.region_id.$dirty && myForm.region_id.$invalid) || submitted && myForm.region_id.$invalid">
                <span ng-show="submitted && myForm.region_id.$error.required">Porfavor Selecciona un departamento</span>
        </div>
    </div>

    <div class="form-group">
        <label class="col-md-4 control-label">Pais</label>
        <div class="col-md-6">
            <select ng-model="data.country_id" name="country_id" ng-change="selectCountry(data.country_id)" >
                <option ng-repeat="country in countries" value="@{{country.id}}">@{{country.name}}</option>
            </select>
            <span style="color:red" ng-show="(myForm.country_id.$dirty && myForm.country_id.$invalid) || submitted && myForm.country_id.$invalid">
                <span ng-show="submitted && myForm.country_id.$error.required">Porfavor Selecciona un país</span>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label">Ciudad</label>
        <div class="col-md-6">
            <select ng-model="data.city_id" name="city_id" >
                <option ng-repeat="city in cities" value="@{{city.id}}">@{{city.name}}</option>
            </select>
            <span style="color:red" ng-show="(myForm.city_id.$dirty && myForm.city_id.$invalid) || submitted && myForm.city_id.$invalid">
                <span ng-show="submitted && myForm.city_id.$error.required">Porfavor Selecciona una ciudad</span>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label">Costo por hora de reserva</label>
        <div class="col-md-6">
            <input type="tel" ng-model="data.unit_cost" class="form-control" name="unit_cost" value="{{ old('unit_cost')}}" required>
            <span style="color:red" ng-show="(myForm.unit_cost.$dirty && myForm.unit_cost.$invalid) || submitted && myForm.unit_cost.$invalid">
                <span ng-show="submitted && myForm.unit_cost.$error.required">Porfavor ingresa un costo por hora de reserva</span>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label">Es necesario que tu confirmes la reserva antes de que el usuario pueda pagar?</label>
        <div class="col-md-6">
            <input type="checkbox" ng-model="data.booking_requires_auth" name="booking_requires_auth" value="{{ old('booking_requires_auth')}}" required>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label">Servicio 1</label>
        <div class="col-md-6">
            <input type="text" ng-model="data.service1" class="form-control" name="service1" value="{{ old('service1')}}" required>
            <span style="color:red" ng-show="(myForm.service1.$dirty && myForm.service1.$invalid) || submitted && myForm.service1.$invalid">
                <span ng-show="submitted && myForm.service1.$error.required">Porfavor ingresa un servicio que le diga a los clientes lo que ofreces</span>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label">Servicio 2</label>
        <div class="col-md-6">
            <input type="text" ng-model="data.service2" class="form-control" name="service2" value="{{ old('service2')}}" >
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label">Servicio 3</label>
        <div class="col-md-6">
            <input type="text" ng-model="data.service3" class="form-control" name="service3" value="{{ old('service3')}}" >
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label">Especialidad 1</label>
        <div class="col-md-6">
            <input type="text" ng-model="data.specialty1" class="form-control" name="specialty1" value="{{ old('specialty1')}}" required>
            <span style="color:red" ng-show="(myForm.specialty1.$dirty && myForm.specialty1.$invalid) || submitted && myForm.specialty1.$invalid">
                <span ng-show="submitted && myForm.specialty1.$error.required">Porfavor ingresa una de tus especialidades</span>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label">Especialidad 2</label>
        <div class="col-md-6">
            <input type="text" ng-model="data.specialty2" class="form-control" name="specialty2" value="{{ old('specialty2')}}" >
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label">Especialidad 3</label>
        <div class="col-md-6">
            <input type="text" ng-model="data.specialty3" class="form-control" name="specialty3" value="{{ old('specialty3')}}" >
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label">Experiencia 1</label>
        <div class="col-md-6">
            <input type="text" ng-model="data.experience1" class="form-control" name="experience1" value="{{ old('experience1')}}" required>
            <span style="color:red" ng-show="(myForm.experience1.$dirty && myForm.experience1.$invalid) || submitted && myForm.experience1.$invalid">
                <span ng-show="submitted && myForm.experience1.$error.required">Porfavor ingresa una previa experiencia</span>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label">Experiencia 2</label>
        <div class="col-md-6">
            <input type="text" ng-model="data.experience2" class="form-control" name="experience2" value="{{ old('experience2')}}" >
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label">Experiencia 3</label>
        <div class="col-md-6">
            <input type="text" ng-model="data.experience3" class="form-control" name="experience3" value="{{ old('experience3')}}" >
        </div>
    </div>

    <div class="form-group">
        <div class="col-md-6 col-md-offset-4">
            <button type="submit" class="btn btn-primary">Save</button>
            <button ng-click="clean()" class="btn btn-primary">Clean</button>

        </div>
    </div>
</form>