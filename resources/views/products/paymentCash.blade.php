<form  class="form-horizontal" role="form" name="myForm4" ng-submit="payCash(myForm4.$valid)" novalidate>
    <input type="hidden" name="_token" value="{{ csrf_token()}}">
    <input type="hidden" ng-model="data4.order_id" name="order_id" value="">
   
    <div class="form-group">
        <label class="col-md-4 control-label">Correo</label>
        <div class="col-md-6">
            <input type="email" ng-model="data4.payer_email" class="form-control" name="payer_email" value="{{ old('payer_email')}}" required>
            <span style="color:red" ng-show="(myForm4.payer_email.$dirty && myForm4.payer_email.$invalid) || submitted4 && myForm4.payer_email.$invalid">
                <span ng-show="submitted4 && myForm4.payer_email.$error.required">Porfavor Ingresa el correo para la confirmacion</span>
        </div>
    </div>

    <div class="form-group">
        <div class="col-md-6 col-md-offset-4">
            <button type="submit" class="btn btn-primary">Save</button>
            <button ng-click="clean()" class="btn btn-primary">Clean</button>
            <a ng-click="fill3()" href="javascript:;">Fill</a>
        </div>
    </div>
</form>