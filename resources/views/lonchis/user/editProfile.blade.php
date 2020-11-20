@extends(config("app.views").'.layouts.app')

@section('content')

<div class="page-section sp-inner-page" ng-controller="UserProfileCtrl">
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
                                    <h3>Mi cuenta</h3>

                                    <div class="account-details-form">
                                        <form role="form" name="myForm" ng-submit="save(myForm.$valid)" novalidate>
                                            <div class="row">
                                                <div class="col-lg-6 col-12 mb-30">
                                                    <label>Nombres</label>
                                                    <input type="text" ng-model="data.firstName" name="firstName" required>
                                                    <span style="color:red" ng-show="(myForm.firstName.$dirty && myForm.firstName.$invalid) || submitted && myForm.firstName.$invalid">
                                                        <span ng-show="submitted && myForm.firstName.$error.required">Porfavor ingresa tu nombre</span></span>
                                                </div>
                                                <div class="col-lg-6 col-12 mb-30">
                                                    <label>Apellidos</label>
                                                    <input type="text" ng-model="data.lastName" name="lastName" required>
                                                    <span style="color:red" ng-show="(myForm.lastName.$dirty && myForm.lastName.$invalid) || submitted && myForm.lastName.$invalid">
                                                        <span ng-show="submitted && myForm.lastName.$error.required">Porfavor ingresa tu apellido</span></span>
                                                </div>
                                                <div class="col-lg-6 col-12 mb-30">
                                                    <label>Email</label>
                                                    <input type="text" ng-model="data.email" name="email" required>
                                                    <span style="color:red" ng-show="(myForm.email.$dirty && myForm.email.$invalid) || submitted && myForm.email.$invalid">
                                                        <span ng-show="submitted && myForm.email.$error.required">Porfavor ingresa tu correo</span></span>
                                                </div>
                                                <div class="col-lg-6 col-12 mb-30">
                                                    <label>Celular</label>
                                                    <input type="text" ng-model="data.cellphone" name="cellphone" required>
                                                    <span style="color:red" ng-show="(myForm.cellphone.$dirty && myForm.cellphone.$invalid) || submitted && myForm.cellphone.$invalid">
                                                        <span ng-show="submitted && myForm.cellphone.$error.required">Porfavor ingresa tu celular</span></span>
                                                </div>
                                                <div class="col-lg-6 col-12 mb-30">
                                                    <label>Genero</label><br/>
                                                    <select ng-model="data.gender"  class="form-control nice-select"  name="gender" >
                                                        <option value="M">Masculino</option>
                                                        <option value="F">Femenino</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-6 col-12 mb-30">
                                                    <label>Tipo Identificacion</label>
                                                    <select ng-model="data.docType"  class="form-control nice-select"  name="docType" >
                                                        <option value="CC">Cédula de ciudadanía</option>
                                                        <option value="CE">Cédula de extranjería</option>
                                                        <option value="NIT">En caso de ser una empresa NIT</option>
                                                        <option value="TI">Tarjeta de Identidad</option>
                                                        <option value="PP">Pasaporte</option>
                                                        <option value="IDC">Identificador único de cliente</option>
                                                        <option value="CEL">Celular</option>
                                                        <option value="RC">Registro civil de nacimiento</option>
                                                        <option value="DE">Documento de identificación extranjero</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-6 col-12 mb-30">
                                                    <label>No. Identificacion</label>
                                                    <input type="text" ng-model="data.docNum" name="docNum">
                                                    
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
