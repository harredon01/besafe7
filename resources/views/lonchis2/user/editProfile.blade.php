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
                                        <form role="form" name="myForm" ng-submit="save(myForm.$valid)" novalidate>
                                            <div class="input-radio">
                                                <span class="custom-radio"><input type="radio" ng-model="data.gender" value="M" name="id_gender"> Masculino</span>
                                                <span class="custom-radio"><input type="radio" ng-model="data.gender" value="F" name="id_gender"> Femenino</span>
                                            </div> <br>
                                            <label>Nombres</label>
                                            <input type="text" ng-model="data.firstName" name="firstName" required>
                                            <span style="color:red" ng-show="(myForm.firstName.$dirty && myForm.firstName.$invalid) || submitted && myForm.firstName.$invalid">
                                                <span ng-show="submitted && myForm.firstName.$error.required">Porfavor ingresa tu nombre</span></span>
                                            <label>Apellidos</label>
                                            <input type="text" ng-model="data.lastName" name="lastName" required>
                                                <span style="color:red" ng-show="(myForm.lastName.$dirty && myForm.lastName.$invalid) || submitted && myForm.lastName.$invalid">
                                                    <span ng-show="submitted && myForm.lastName.$error.required">Porfavor ingresa tu apellido</span></span>
                                            <label>Email</label>
                                            <input type="text" ng-model="data.email" name="email" required>
                                                <span style="color:red" ng-show="(myForm.email.$dirty && myForm.email.$invalid) || submitted && myForm.email.$invalid">
                                                    <span ng-show="submitted && myForm.email.$error.required">Porfavor ingresa tu correo</span></span>
                                            <label>Tipo Identificacion</label>
                                            <div class="input-radio">
                                                <span class="custom-radio"><input type="radio" ng-model="data.docType" value="CC" name="id_type"> Cédula de ciudadanía</span>
                                                <span class="custom-radio"><input type="radio" ng-model="data.docType" value="CE" name="id_type"> Cédula de extranjería</span><br/>
                                                <span class="custom-radio"><input type="radio" ng-model="data.docType" value="NIT" name="id_type"> En caso de ser una empresa NIT</span>
                                                <span class="custom-radio"><input type="radio" ng-model="data.docType" value="TI" name="id_type"> Tarjeta de Identidad</span><br/>
                                                <span class="custom-radio"><input type="radio" ng-model="data.docType" value="PP" name="id_type"> Pasaporte</span>
                                                <span class="custom-radio"><input type="radio" ng-model="data.docType" value="IDC" name="id_type"> Identificador único de cliente</span><br/>
                                                <span class="custom-radio"><input type="radio" ng-model="data.docType" value="CEL" name="id_type"> Celular</span>
                                                <span class="custom-radio"><input type="radio" ng-model="data.docType" value="RC" name="id_type"> Registro civil de nacimiento</span><br/>
                                                <span class="custom-radio"><input type="radio" ng-model="data.docType" value="DE" name="id_type"> Documento de identificación extranjero</span>
                                            </div> <br>
                                            <label>No. Identificacion</label>
                                                <input type="text" ng-model="data.docNum" name="docNum">
                                            <br>
                                            <!--span class="custom_checkbox">
                                                <input type="checkbox" value="1" name="optin">
                                                <label>Receive offers from our partners</label>
                                            </span>
                                            <br>
                                            <span class="custom_checkbox">
                                                <input type="checkbox" value="1" name="newsletter">
                                                <label>Sign up for our newsletter<br><em>You may unsubscribe at any moment. For that purpose, please find our contact info in the legal notice.</em></label>
                                            </span-->
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
