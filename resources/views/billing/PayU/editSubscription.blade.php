<div ng-controller="SubscriptionPlansPayuCtrl" ng-init='config={sources:{!! $user->sources !!}}'>
    <div ng-hide="sourceSelected">
        @include('billing.PayU.sourceList')
        <div class="form-group" ng-hide="newCard">
            <div class="col-md-6 col-md-offset-4">

                <button ng-click="newCardTrigger()" class="btn btn-primary">Nueva Tarjera</button>

            </div>
        </div>
        <div ng-show="newCard">
            @include('billing.PayU.editSubscriptionSourceForm')
        </div>

    </div>

    <div ng-show="sourceSelected" ng-hide="newCard">
        @include('billing.PayU.editSubscriptionForm')
    </div>
</div>
