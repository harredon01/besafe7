
<div class="container-fluid" ng-controller="SourcesPayuCtrl" ng-init='config={sources:{!! $user->sources !!},gateway:"PayU"}'>
    <div class="replace-address">
        @include(config("app.views").'.billing.PayU.sourceList')
    </div>


    <div >

        @include(config("app.views").'.billing.PayU.editSourceForm')

    </div>
</div>

