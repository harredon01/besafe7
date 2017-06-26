
<div class="container-fluid" ng-controller="SourcesPayuCtrl" ng-init='config={sources:{!! $user->sources !!},gateway:"PayU"}'>
    <div class="replace-address">
        @include('billing.PayU.sourceList')
    </div>


    <div >

        @include('billing.PayU.editSourceForm')

    </div>
</div>

