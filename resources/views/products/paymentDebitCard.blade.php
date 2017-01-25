<form ng-show="billingAddressSet" class="form-horizontal" role="form" name="myForm3" ng-submit="payDebitCard(myForm3.$valid)" novalidate>
    <input type="hidden" name="_token" value="{{ csrf_token()}}">
    <input type="hidden" ng-model="data3.order_id" name="order_id" value="">
    <div class="form-group" >
        <label class="col-md-4 control-label">Banco</label>
        <div class="col-md-6">
            <select ng-model="data3.financial_institution_code" name="financial_institution_code" required>
                <option ng-repeat="bank in banks" value="@{{bank.pseCode}}">@{{bank.description}}</option>
            </select>
            <span style="color:red" ng-show="(myForm3.financial_institution_code.$dirty && myForm3.financial_institution_code.$invalid) || submitted3 && myForm3.financial_institution_code.$invalid">
                <span ng-show="submitted3 && myForm3.financial_institution_code.$error.required">Porfavor Selecciona tu banco</span>
        </div>
    </div>
    <div class="form-group" >
        <label class="col-md-4 control-label">Tipo de cliente</label>
        <div class="col-md-6">
            <select ng-model="data3.user_type" name="user_type" required>
                <option value="N">Persona Natural</option>
                <option value="J">Persona Juridica</option>
            </select>
            <span style="color:red" ng-show="(myForm3.user_type.$dirty && myForm3.user_type.$invalid) || submitted3 && myForm3.user_type.$invalid">
                <span ng-show="submitted3 && myForm3.user_type.$error.required">Porfavor selecciona el tipo de cliente</span>
        </div>
    </div>
    <div class="form-group" >
        <label class="col-md-4 control-label">Tipo de documento</label>
        <div class="col-md-6">
            <select ng-model="data3.pse_reference2" name="pse_reference2" required>
                <option value="CC">Cédula de ciudadanía</option>
                <option value="CE">Cédula de extranjería</option>
                <option value="NIT">En caso de ser una empresa NIT</option>
                <option value="TI">Tarjeta de Identidad</option>
                <option value="PP">Pasaporte</option>
                <option value="IDC">Identificador único de cliente</option>
                <option value="CEL">Celular</option>
                <option value="RC">Registro civil de nacimiento</option>
                <option value="DE">Documento de identificación extranjero</option>
            </select>
            <span style="color:red" ng-show="(myForm3.pse_reference2.$dirty && myForm3.pse_reference2.$invalid) || submitted3 && myForm3.pse_reference2.$invalid">
                <span ng-show="submitted3 && myForm3.pse_reference2.$error.required">Porfavor selecciona el tipo de documento</span>
        </div>
    </div>

    <div class="form-group">
        <label class="col-md-4 control-label">Numero Documento</label>
        <div class="col-md-6">
            <input type="text" ng-model="data3.pse_reference3" class="form-control" name="pse_reference3" value="{{ old('pse_reference3')}}" required>
            <span style="color:red" ng-show="(myForm3.pse_reference3.$dirty && myForm3.pse_reference3.$invalid) || submitted3 && myForm3.pse_reference3.$invalid">
                <span ng-show="submitted3 && myForm3.pse_reference3.$error.required">Porfavor Ingresa el número de documento</span>
        </div>
    </div>

    <div class="form-group">
        <label class="col-md-4 control-label">Correo del propietario de la tarjeta</label>
        <div class="col-md-6">
            <input type="email" ng-model="data3.payer_email" class="form-control" name="payer_email" value="{{ old('payer_email')}}" required>
            <span style="color:red" ng-show="(myForm3.payer_email.$dirty && myForm3.payer_email.$invalid) || submitted3 && myForm3.payer_email.$invalid">
                <span ng-show="submitted3 && myForm3.payer_email.$error.required">Porfavor Ingresa el correo del propietario de la tarjeta</span>
        </div>
    </div>

    <div class="form-group">
        <div class="col-md-6 col-md-offset-4">
            <button type="submit" class="btn btn-primary">Save</button>
            <button ng-click="clean()" class="btn btn-primary">Clean</button>
            <a ng-click="fill2()" href="javascript:;">Fill</a>
        </div>
    </div>
</form>