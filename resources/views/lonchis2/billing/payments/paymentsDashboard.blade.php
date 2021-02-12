@extends(config("app.views").'.layouts.app')

@section('content')
<section class="main_content_area" ng-controller="UserProfileCtrl">
    <div class="container">   
        <div class="account_dashboard">
            <div class="row">
                @include(config("app.views").'.user.account_menu')
                <div class="col-sm-12 col-md-9 col-lg-9">
                    <!-- Tab panes -->
                    <div class="tab-content dashboard_content">
                        <div class="tab-pane fade  show active" id="dashboard"> 
                            <h3>Account details </h3>
                            <div class="login">
                                <div class="login_form_container">
                                    <div class="account_login_form">
                                        <div ng-controller="PaymentsCtrl">
                                            <div class="replace-address">
                                                @include(config("app.views").'.billing.payments.paymentsList')
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>  
    </div>        	
</section>	

@endsection
