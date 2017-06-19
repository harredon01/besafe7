
<div ng-controller="SourcesCtrl" ng-init='config={sources:{!! $user->sources !!},gateway:"Stripe"}'>
    <div class="replace-address">
        @include('billing.Stripe.sourceList')
    </div>

    <div class="form-group">
        <div class="col-md-6 col-md-offset-4">
            <button ng-click="edit()" class="btn btn-primary">Add new</button>
        </div>
    </div>
    <div ng-show="editSource">

        @include('billing.Stripe.editSourceForm')
        <div class="col-md-6 col-md-offset-4">
            <button ng-click="edit()" class="btn btn-primary">Done</button>
        </div>
    </div>
</div>
