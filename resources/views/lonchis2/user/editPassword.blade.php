@extends(config("app.views").'.layouts.app')

@section('content')
<section class="main_content_area" ng-controller="UserPasswordCtrl">
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
                                        <form role="form" name="myForm" ng-submit="save(myForm.$valid)" novalidate>
                                            <label>Contraseña anterior</label>
                                                    <input type="password" ng-model="data.old_password" name="old_password" required>
                                                    <span style="color:red" ng-show="(myForm.old_password.$dirty && myForm.old_password.$invalid) || submitted && myForm.old_password.$invalid">
                                                        <span ng-show="submitted && myForm.old_password.$error.required">Porfavor ingresa tu contraseña anterior</span></span>
                                            <label>Nueva contraseña</label>
                                                    <input type="password" ng-model="data.password" name="password" required>
                                                    <span style="color:red" ng-show="(myForm.password.$dirty && myForm.password.$invalid) || submitted && myForm.password.$invalid">
                                                        <span ng-show="submitted && myForm.password.$error.required">Porfavor ingresa tu nueva contraseña</span></span>
                                            <label>Confirmar contraseña</label>
                                                    <input type="password" ng-model="data.password_confirmation" name="password_confirmation" required>
                                                    <span style="color:red" ng-show="(myForm.password_confirmation.$dirty && myForm.password_confirmation.$invalid) || submitted && myForm.password_confirmation.$invalid">
                                                        <span>Porfavor confirma tu nueva contraseña</span></span>
                                                    <span style="color:red" ng-show="(submitted && data.password != data.password_confirmation)">
                                                        <span>NO son iguales</span></span>
                                            
                                            <div class="save_button primary_btn default_button">
                                                <button type="submit">Guardar</button>
                                            </div>
                                            
                                        </form>
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
