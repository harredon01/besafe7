<form class="form-horizontal" role="form" name="myForm" ng-submit="save(myForm.$valid)" novalidate>
    <input type="hidden" name="_token" value="{{ csrf_token()}}">
    <input type="hidden" ng-model="data.source" class="form-control" name="source" value="{{ old('source')}}" required>
    <input type="hidden" ng-model="data.plan_id" name="plan_id" >
    <div class="form-group">
        <label class="col-md-4 control-label">Beneficiary</label>
        <div class="col-md-6">
            <input type="text" ng-model="data.object_id" class="form-control" name="object_id" value="{{ old('object_id')}}" required>
            <span style="color:red" ng-show="(myForm.object_id.$dirty && myForm.object_id.$invalid) || submitted && myForm.object_id.$invalid">
                <span ng-show="submitted && myForm.object_id.$error.required">please select a beneficiary</span>
        </div>
    </div>

    <div class="form-group">
        <div class="col-md-6 col-md-offset-4">
            <button type="submit" class="btn btn-primary">Save</button>
            <button ng-click="clean()" class="btn btn-primary">Clean</button>

        </div>
    </div>
</form>