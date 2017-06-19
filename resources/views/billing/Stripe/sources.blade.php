
<div ng-controller="SourcesCtrl" ng-init='config={sources:{!! $user->sources !!},gateway:"Stripe"}'>
    <div class="replace-address">
        @include('billing.Stripe.sourceList')
    </div>


    <div >

        @include('billing.Stripe.editSourceForm')

    </div>
</div>
