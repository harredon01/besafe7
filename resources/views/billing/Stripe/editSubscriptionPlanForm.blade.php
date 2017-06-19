<div ng-controller="SubscriptionsCtrl" ng-init='config={sources:{!! $user->sources !!},gateway:"Stripe"}'>

    <div>
        Listed Subscriptions<br><br>
        <ul>

            <li id="subscription-@{{ subscription.id }}" ng-repeat="subscription in subscriptions">
                Type: <span class="type">@{{ subscription.type }}</span><br/>
                <span class="subscription_id" style="display:none">@{{ subscription.id }}</span><span class="firstName">@{{ subscription.firstName }}</span>, <span class="lastName">@{{ subscription.lastName }}</span>
                <br/><span class="phone">@{{ subscription.phone }}</span>, <span class="postal">@{{ subscription.postal }}</span>
                <br/><span class="subscription">@{{ subscription.subscription }}</span>, <span class="city">@{{ subscription.cityName }}</span><span class="city_id" style="display:none">@{{ subscription.city_id }}</span>
                <br/><span class="regionName">@{{ subscription.regionName }}</span>, <span class="countryName">@{{ subscription.countryName }}</span>
                <span class="region_id" style="display:none">@{{ subscription.region_id }}</span>, <span class="country_id"  style="display:none">@{{ subscription.country_id }}</span>
                <br/><a href="javascript:;" ng-click="editSubscription(subscription.id)" class="editar">Edit</a>
                <br/><a href="javascript:;" ng-click="deleteSubscription(subscription.id)" class="editar">Borrar</a>
            </li>

        </ul>
    </div>
    <p ng-show='showErrors'>@{{errors}}</p>
    <div ng-hide="sourceSelected">
        <form class="form-horizontal" role="form" name="myForm" ng-submit="save(myForm.$valid)" novalidate>
            <input type="hidden" name="_token" value="{{ csrf_token()}}">
            <input type="hidden" ng-model="data.subscription_id"  name="subscription_id">

            <div class="form-group">
                <label class="col-md-4 control-label">Plan</label>
                <div class="col-md-6">
                    <input type="text" ng-model="data.plan_id" class="form-control" name="plan_id" value="{{ old('plan_id')}}" required>
                    <span style="color:red" ng-show="(myFormSimple.plan_id.$dirty && myFormSimple.plan_id.$invalid) || submitted && myFormSimple.plan_id.$invalid">
                        <span ng-show="submitted && myFormSimple.plan_id.$error.required">please select a Plan</span>
                </div>
            </div>

            <div class="form-group">
                <div class="col-md-6 col-md-offset-4">
                    <button type="submit" class="btn btn-primary">Save</button>

                </div>
            </div>
        </form>
    </div>

</div>
