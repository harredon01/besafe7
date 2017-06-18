<div ng-controller="CreateSourceStripeCtrl" ng-init="config={sources:{!! $user->sources !!},gateway:'Stripe'}">
    <div ng-hide="has_default">
        <form class="form-horizontal" role="form" name="myForm" ng-submit="save(myForm.$valid)" novalidate>
            <input type="hidden" name="_token" value="{{ csrf_token()}}">
            <input type="hidden" ng-model="data.plan_id" name="plan_id" >
            <input type="hidden" ng-model="data.source"  name="source">
            <div class="form-group">
                <label class="col-md-4 control-label">CC</label>
                <div class="col-md-6">
                    <input type="text" ng-model="data.number" class="form-control" name="number" value="{{ old('number')}}" required>
                    <span style="color:red" ng-show="(myForm.number.$dirty && myForm.number.$invalid) || submitted && myForm.number.$invalid">
                        <span ng-show="submitted && myForm.number.$error.required">Porfavor ingresa un numero de tarjeta</span>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-4 control-label">CVC</label>
                <div class="col-md-6">
                    <input type="text" ng-model="data.cvc" class="form-control" name="cvc" value="{{ old('cvc')}}" required>
                    <span style="color:red" ng-show="(myForm.cvc.$dirty && myForm.cvc.$invalid) || submitted && myForm.cvc.$invalid">
                        <span ng-show="submitted && myForm.cvc.$error.required">Porfavor ingresa el numero de seguridad</span>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-4 control-label">Postal Code</label>
                <div class="col-md-6">
                    <input type="text" ng-model="data.postal" class="form-control" name="postal" value="{{ old('postal')}}" required>
                    <span style="color:red" ng-show="(myForm.postal.$dirty && myForm.postal.$invalid) || submitted && myForm.postal.$invalid">
                        <span ng-show="submitted && myForm.postal.$error.required">Porfavor ingresa un codigo postal</span>
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
                <label class="col-md-4 control-label">Beneficiary</label>
                <div class="col-md-6">
                    <input type="text" ng-model="data.object_id" class="form-control" name="object_id" value="{{ old('object_id')}}" required>
                    <span style="color:red" ng-show="(myFormSimple.object_id.$dirty && myFormSimple.object_id.$invalid) || submitted && myFormSimple.object_id.$invalid">
                        <span ng-show="submitted && myFormSimple.object_id.$error.required">please select a beneficiary</span>
                </div>
            </div>

            <div class="form-group">
                <div class="col-md-6 col-md-offset-4">
                    <button type="submit" class="btn btn-primary">Save</button>

                </div>
            </div>
        </form>
    </div>
    <div ng-show="has_default">
        <form class="form-horizontal" role="form" name="myFormSimple" ng-submit="saveSimple(myFormSimple.$valid)" novalidate>
            <input type="hidden" name="_token" value="{{ csrf_token()}}">
            <input type="hidden" ng-model="data.plan_id" name="plan_id" >

            <div class="form-group">
                <label class="col-md-4 control-label">Beneficiary</label>
                <div class="col-md-6">
                    <input type="text" ng-model="data.object_id" class="form-control" name="object_id" value="{{ old('object_id')}}" required>
                    <span style="color:red" ng-show="(myFormSimple.object_id.$dirty && myFormSimple.object_id.$invalid) || submitted && myFormSimple.object_id.$invalid">
                        <span ng-show="submitted && myFormSimple.object_id.$error.required">please select a beneficiary</span>
                </div>
            </div>


            <div class="form-group">
                <div class="col-md-6 col-md-offset-4">
                    <button type="submit" class="btn btn-primary">One Click Pay</button>

                </div>
            </div>
        </form>
    </div>

</div>
