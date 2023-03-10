<p ng-show='showErrors'>@{{errors}}</p>
<div ng-hide="sourceSelected">
    <form class="form-horizontal" role="form" name="myForm" ng-submit="save(myForm.$valid)" novalidate>
        <input type="hidden" name="_token" value="{{ csrf_token()}}">

        <div class="form-group">
            <label class="col-md-4 control-label">Plan</label>
            <div class="col-md-6">
                <input type="text" ng-model="data.plan_id" class="form-control" name="plan_id" value="{{ old('plan_id')}}" required>
                <span style="color:red" ng-show="(myFormSimple.plan_id.$dirty && myFormSimple.plan_id.$invalid) || submitted && myFormSimple.plan_id.$invalid">
                    <span ng-show="submitted && myFormSimple.plan_id.$error.required">please select a Plan</span>
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-4 control-label">Subscription</label>
            <div class="col-md-6">
                <input type="text" ng-model="data.subscription_id" class="form-control" name="subscription_id" value="{{ old('subscription_id')}}" required>
                <span style="color:red" ng-show="(myFormSimple.subscription_id.$dirty && myFormSimple.subscription_id.$invalid) || submitted && myFormSimple.subscription_id.$invalid">
                    <span ng-show="submitted && myFormSimple.subscription_id.$error.required">please select a Subscription</span>
            </div>
        </div>

        <div class="form-group">
            <div class="col-md-6 col-md-offset-4">
                <button type="submit" class="btn btn-primary">Save</button>

            </div>
        </div>
    </form>
</div>