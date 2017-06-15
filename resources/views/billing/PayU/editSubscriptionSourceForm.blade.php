<form class="form-horizontal" role="form" name="myForm" ng-submit="save(myForm.$valid)" novalidate>
    <input type="hidden" name="_token" value="{{ csrf_token()}}">
    <input type="hidden" ng-model="data.plan_id" name="plan_id" >
    <div class="form-group">
        <label class="col-md-4 control-label">Direccion</label>
        <div class="col-md-6">
            <input type="text" ng-model="data.line1" class="form-control" name="line1" value="{{ old('line1')}}" required>
            <span style="color:red" ng-show="(myForm.line1.$dirty && myForm.line1.$invalid) || submitted && myForm.line1.$invalid">
                <span ng-show="submitted && myForm.line1.$error.required">Porfavor ingresa tu direccion</span>                                
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label">Linea 2</label>
        <div class="col-md-6">
            <input type="text" ng-model="data.line2" class="form-control" name="line2" value="{{ old('line2')}}" >                             
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label">Linea 3</label>
        <div class="col-md-6">
            <input type="text" ng-model="data.line3" class="form-control" name="line3" value="{{ old('line3')}}" >                             
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label">Codigo Postal</label>
        <div class="col-md-6">
            <input type="text" ng-model="data.postalCode" class="form-control" name="postalCode" value="{{ old('postalCode')}}" required>
            <span style="color:red" ng-show="(myForm.postalCode.$dirty && myForm.postalCode.$invalid) || submitted && myForm.postalCode.$invalid">
                <span ng-show="submitted && myForm.postalCode.$error.required">Porfavor Ingresa el Codigo Postal</span>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label">Ciudad</label>
        <div class="col-md-6">
            <input type="text" ng-model="data.city" class="form-control" name="city" value="{{ old('city')}}" required>
            <span style="color:red" ng-show="(myForm.city.$dirty && myForm.city.$invalid) || submitted && myForm.city.$invalid">
                <span ng-show="submitted && myForm.city.$error.required">Porfavor ingresa la ciudad</span>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label">Departamento</label>
        <div class="col-md-6">
            <input type="text" ng-model="data.state" class="form-control" name="state" value="{{ old('state')}}" required>
            <span style="color:red" ng-show="(myForm.state.$dirty && myForm.state.$invalid) || submitted && myForm.state.$invalid">
                <span ng-show="submitted && myForm.state.$error.required">Porfavor ingresa un departamento</span>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label">Pais</label>
        <div class="col-md-6">
            <input type="text" ng-model="data.country" class="form-control" name="country" value="{{ old('country')}}" required>
            <span style="color:red" ng-show="(myForm.country.$dirty && myForm.country.$invalid) || submitted && myForm.country.$invalid">
                <span ng-show="submitted && myForm.country.$error.required">Porfavor ingresa un pais</span>
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
        <label class="col-md-4 control-label">Documento</label>
        <div class="col-md-6">
            <input type="text" ng-model="data.document" class="form-control" name="document" value="{{ old('document')}}" required>
            <span style="color:red" ng-show="(myForm.document.$dirty && myForm.document.$invalid) || submitted && myForm.document.$invalid">
                <span ng-show="submitted && myForm.phone.$error.required">Porfavor ingresa un numero de documento</span>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label">CC</label>
        <div class="col-md-6">
            <input type="text" ng-model="data.number" class="form-control" name="number" value="{{ old('number')}}" required>
            <span style="color:red" ng-show="(myForm.number.$dirty && myForm.number.$invalid) || submitted && myForm.number.$invalid">
                <span ng-show="submitted && myForm.number.$error.required">Porfavor ingresa un numero de tarjeta</span>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label">Exp Month</label>
        <div class="col-md-6">
            <select ng-model="data.expMonth" name="expMonth" required>
                <option ng-repeat="month in months" value="@{{month}}">@{{month}}</option>
            </select>
            <span style="color:red" ng-show="(myForm.expMonth.$dirty && myForm.expMonth.$invalid) || submitted && myForm.expMonth.$invalid">
                <span ng-show="submitted && myForm.expMonth.$error.required">Porfavor indica el mes de vencimiento</span>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label">Exp year</label>
        <div class="col-md-6">
            <select ng-model="data.expYear" name="expYear" required>
                <option ng-repeat="year in years" value="@{{year}}">@{{year}}</option>
            </select>
            <span style="color:red" ng-show="(myForm.expYear.$dirty && myForm.expYear.$invalid) || submitted && myForm.expYear.$invalid">
                <span ng-show="submitted && myForm.expYear.$error.required">Porfavor indica el año de vencimiento</span>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label">Branch</label>
        <div class="col-md-6">
            <select ng-model="data.branch" name="branch" >
                <option ng-repeat="branch in branches" value="@{{branch.value}}">@{{branch.name}}</option>
            </select>
            <span style="color:red" ng-show="(myForm.branch.$dirty && myForm.branch.$invalid) || submitted && myForm.branch.$invalid">
                <span ng-show="submitted && myForm.branch.$error.required">Porfavor Selecciona una franquisia</span>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label">Beneficiario</label>
        <div class="col-md-6">
            <input type="text" ng-model="data.object_id" class="form-control" name="object_id" value="{{ old('object_id')}}" required>
            <span style="color:red" ng-show="(myForm.object_id.$dirty && myForm.object_id.$invalid) || submitted && myForm.object_id.$invalid">
                <span ng-show="submitted && myForm.object_id.$error.required">Porfavor ingresa un Beneficiario</span>
        </div>
    </div>

    <div class="form-group">
        <div class="col-md-6 col-md-offset-4">
            <button type="submit" class="btn btn-primary">Guardar</button>
            <button ng-click="clean()" class="btn btn-primary">Limpiar</button>

        </div>
    </div>
</form>
