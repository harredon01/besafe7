
<form class="form-horizontal" role="form" name="myFormSimple" ng-submit="saveSimple(myFormSimple.$valid)" novalidate>
    <input type="hidden" name="_token" value="{{ csrf_token()}}">
    <input type="hidden" ng-model="data.plan_id" name="plan_id" >
    <input type="hidden" ng-model="data.source" class="form-control" name="source" required>

    <div class="form-group">
        <label class="col-md-4 control-label">Beneficiary</label>
        <div class="col-md-6">
            <input type="hidden" ng-model="data.object_id" class="form-control" name="object_id" value="{{ old('object_id')}}" required>
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