<form class="form-horizontal" role="form" name="myFormSimple" ng-submit="saveSimple(myFormSimple.$valid)" novalidate>
    <input type="hidden" name="_token" value="{{ csrf_token()}}">
    <input type="hidden" ng-model="data.source" name="source">

    <div class="form-group">
        <label class="col-md-4 control-label">Beneficiario</label>
        <div class="col-md-6">
            <input type="text" ng-model="data.object_id" class="form-control" name="object_id" value="{{ old('object_id')}}" required>
            <span style="color:red" ng-show="(myFormSimple.object_id.$dirty && myFormSimple.object_id.$invalid) || submitted && myFormSimple.object_id.$invalid">
                <span ng-show="submitted && myFormSimple.object_id.$error.required">Porfavor ingresa un Beneficiario</span>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label">Plan</label>
        <div class="col-md-6">
            <input type="text" ng-model="data.plan_id" class="form-control" name="plan_id" value="{{ old('plan_id')}}" required>
            <span style="color:red" ng-show="(myFormSimple.plan_id.$dirty && myFormSimple.plan_id.$invalid) || submitted && myFormSimple.plan_id.$invalid">
                <span ng-show="submitted && myFormSimple.plan_id.$error.required">Porfavor selecciona un plan</span>
        </div>
    </div>

    <div class="form-group">
        <div class="col-md-6 col-md-offset-4">
            <button type="submit" class="btn btn-primary">Pagar</button>
            <a href="javascript:;" ng-click="getSources()">Mis tarjetas</a>
        </div>
    </div>
</form>
