

<form ng-show="billingAddressSet" class="form-horizontal" role="form" name="myForm2" ng-submit="payCreditCard(myForm2.$valid)" novalidate>
    <input type="hidden" name="_token" value="{{ csrf_token()}}">
    <input type="hidden" ng-model="data2.order_id" name="order_id" value="">
    <div class="form-group" >
        <label class="col-md-4 control-label">Franquisia</label>
        <div class="col-md-6">
            <select ng-model="data2.cc_branch" name="cc_branch" required>
                <option value="VISA">Visa</option>
                <option value="MASTERCARD">Master Card</option>
                <option value="AMEX">Amex</option>
                <option value="DINERS">Diners</option>
            </select>
            <span style="color:red" ng-show="(myForm2.cc_branch.$dirty && myForm2.cc_branch.$invalid) || submitted2 && myForm2.cc_branch.$invalid">
                <span ng-show="submitted2 && myForm2.cc_branch.$error.required">Porfavor Selecciona la franquisia</span>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label">Nombre En la tarjeta</label>
        <div class="col-md-6">
            <input type="text" ng-model="data2.cc_name" class="form-control" name="cc_name" value="{{ old('cc_name')}}" required>
            <span style="color:red" ng-show="(myForm2.cc_name.$dirty && myForm2.cc_name.$invalid) || submitted2 && myForm2.cc_name.$invalid">
                <span ng-show="submitted2 && myForm2.cc_name.$error.required">Porfavor ingresa el nombre que aparece en la tarjeta</span>                                
        </div>
    </div>

    <div class="form-group">
        <label class="col-md-4 control-label">Número de tarjeta</label>
        <div class="col-md-6">
            <input type="text" ng-model="data2.cc_number" class="form-control" name="cc_number" value="{{ old('cc_number')}}" required>
            <span style="color:red" ng-show="(myForm2.cc_number.$dirty && myForm2.cc_number.$invalid) || submitted2 && myForm2.cc_number.$invalid">
                <span ng-show="submitted2 && myForm2.cc_number.$error.required">Porfavor Ingresa el número de la tarjeta</span>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label">Codigo de Seguridad</label>
        <div class="col-md-6">
            <input type="text" ng-model="data2.cc_security_code" class="form-control" name="cc_security_code" value="{{ old('cc_security_code')}}" required>
            <span style="color:red" ng-show="(myForm2.cc_security_code.$dirty && myForm2.cc_security_code.$invalid) || submitted2 && myForm2.cc_security_code.$invalid">
                <span ng-show="submitted2 && myForm2.cc_security_code.$error.required">Porfavor ingresa el número de seguridad</span>
        </div>
    </div>

    <div class="form-group">
        <label class="col-md-4 control-label">Fecha de vencimiento</label>
        <div class="col-md-6">

        <label class="col-md-1 control-label">Año</label>
        <div class="col-md-2">
            <select ng-model="data2.cc_expiration_year" name="cc_expiration_year" required>
                <option ng-repeat="year in years" value="@{{year}}">@{{year}}</option>
            </select>
            <span style="color:red" ng-show="(myForm2.cc_expiration_year.$dirty && myForm2.cc_expiration_year.$invalid) || submitted2 && myForm2.cc_expiration_year.$invalid">
                <span ng-show="submitted2 && myForm2.cc_expiration_year.$error.required">Porfavor Selecciona año de vencimiento</span>
        </div>
        <label class="col-md-1 control-label">Mes</label>
        <div class="col-md-2">
            <select ng-model="data2.cc_expiration_month" name="cc_expiration_month" required>
                <option ng-repeat="month in months" value="@{{month}}">@{{month}}</option>
            </select>
            <span style="color:red" ng-show="(myForm2.cc_expiration_month.$dirty && myForm2.cc_expiration_month.$invalid) || submitted2 && myForm2.cc_expiration_month.$invalid">
                <span ng-show="submitted2 && myForm2.cc_expiration_month.$error.required">Porfavor Selecciona mes de vencimiento</span>
        </div>
    </div>
                           
        </div>

    <div class="form-group">
        <label class="col-md-4 control-label">Correo del propietario de la tarjeta</label>
        <div class="col-md-6">
            <input type="email" ng-model="data2.payer_email" class="form-control" name="payer_email" value="{{ old('payer_email')}}" required>
            <span style="color:red" ng-show="(myForm2.payer_email.$dirty && myForm2.payer_email.$invalid) || submitted2 && myForm2.payer_email.$invalid">
                <span ng-show="submitted2 && myForm2.payer_email.$error.required">Porfavor Ingresa el correo del propietario de la tarjeta</span>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label">Cédula del propietario de la tarjeta</label>
        <div class="col-md-6">
            <input type="text" ng-model="data2.payer_id" class="form-control" name="payer_id" value="{{ old('payer_id')}}" required>
            <span style="color:red" ng-show="(myForm2.payer_id.$dirty && myForm2.payer_id.$invalid) || submitted2 && myForm2.payer_id.$invalid">
                <span ng-show="submitted2 && myForm2.payer_id.$error.required">Porfavor ingresa la cédula del propietario de la tarjeta</span>
        </div>
    </div>


    <div class="form-group">
        <div class="col-md-6 col-md-offset-4">
            <button type="submit" class="btn btn-primary">Save</button>
            <button ng-click="clean2()" class="btn btn-primary">Clean</button>
            <a ng-click="fill()" href="javascript:;">Fill</a>

        </div>
    </div>
</form>