<form class="form-horizontal" role="form" name="myFormSimple" ng-submit="saveSimple(myFormSimple.$valid)" novalidate>
    <input type="hidden" name="_token" value="{{ csrf_token()}}">
    <input type="hidden" ng-model="data.source" name="source">
    <input type="hidden" ng-model="data.plan_id" name="plan_id" >

    <div class="form-group">
        <label class="col-md-4 control-label">Beneficiario</label>
        <div class="col-md-6">
            <input type="text" ng-model="data.object_id" class="form-control" name="object_id" value="{{ old('object_id')}}" required>
            <span style="color:red" ng-show="(myFormSimple.object_id.$dirty && myFormSimple.object_id.$invalid) || submitted && myFormSimple.object_id.$invalid">
                <span ng-show="submitted && myFormSimple.object_id.$error.required">Porfavor ingresa un Beneficiario</span>
        </div>
    </div>

    <div class="form-group">
        <div class="col-md-6 col-md-offset-4">
            <button type="submit" class="btn btn-primary">Save</button>

        </div>
    </div>
</form>
