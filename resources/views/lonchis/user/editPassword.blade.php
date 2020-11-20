@extends(config("app.views").'.layouts.app')

@section('content')

<div class="page-section sp-inner-page" ng-controller="UserPasswordCtrl">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="row">
                    <!-- My Account Tab Menu Start -->
                    @include(config("app.views").'.user.account_menu')
                    <!-- My Account Tab Menu End -->

                    <!-- My Account Tab Content Start -->
                    <div class="col-lg-9 col-12 mt--30 mt-lg-0">
                        <div class="tab-content" id="myaccountContent">
                            <div class="tab-pane" style="display:block" id="account-info" role="tabpanel">
                                <div class="myaccount-content">
                                    <h3>Mi Contraseña</h3>

                                    <div class="account-details-form">
                                        <form role="form" name="myForm" ng-submit="save(myForm.$valid)" novalidate>
                                            <div class="row">
                                                <div class="col-12 mb-30">
                                                    <label>Contraseña anterior</label>
                                                    <input type="password" ng-model="data.old_password" name="old_password" required>
                                                    <span style="color:red" ng-show="(myForm.old_password.$dirty && myForm.old_password.$invalid) || submitted && myForm.old_password.$invalid">
                                                        <span ng-show="submitted && myForm.old_password.$error.required">Porfavor ingresa tu contraseña anterior</span></span>
                                                </div>
                                                <div class="col-lg-6 col-12 mb-30">
                                                    <label>Nueva contraseña</label>
                                                    <input type="password" ng-model="data.password" name="password" required>
                                                    <span style="color:red" ng-show="(myForm.password.$dirty && myForm.password.$invalid) || submitted && myForm.password.$invalid">
                                                        <span ng-show="submitted && myForm.password.$error.required">Porfavor ingresa tu nueva contraseña</span></span>
                                                </div>
                                                <div class="col-lg-6 col-12 mb-30">
                                                    <label>Confirmar contraseña</label>
                                                    <input type="password" ng-model="data.password_confirmation" name="password_confirmation" required>
                                                    <span style="color:red" ng-show="(myForm.password_confirmation.$dirty && myForm.password_confirmation.$invalid) || submitted && myForm.password_confirmation.$invalid">
                                                        <span>Porfavor confirma tu nueva contraseña</span></span>
                                                    <span style="color:red" ng-show="(submitted && data.password != data.password_confirmation)">
                                                        <span>NO son iguales</span></span>
                                                </div>

                                                <div class="col-12">
                                                    <button class="theme-btn">Guardar</button>
                                                </div>

                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <!-- Single Tab Content End -->
                        </div>
                    </div>
                    <!-- My Account Tab Content End -->
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
