@extends(config("app.views").'.layouts.app')

@section('content')

<br/><br/>
<!--contact area start-->
<div class="contact_area">
    <div class="container">   
        <div class="row">
            <div class="col-lg-6 col-md-6">
                <div class="contact_message content">
                    <h3>Escríbenos</h3>    
                    <p>Nos encantaría conocerte y estamos para servirte. completa el formulario y uno de nuestros agentes se pondrá en contacto contigo</p>
                    <ul>
                        <li><a href="tel:+573103418432"><i class="fas fa-phone"></i>+57 310 341 8432</a></li>
                        <li><a href="mailto:servicioalcliente@petworld.net.co"><i class="far fa-envelope"></i>servicioalcliente@lonchis.com.co</a></li>
                    </ul>             
                </div> 
            </div>
            <div class="col-lg-6 col-md-6">
                <div class="contact_message form" ng-controller="LeadCtrl">
                    <h3>Tu Mensaje</h3>   
                    <form  id="contact-form" role="form" name="myForm" ng-submit="lead(myForm.$valid)" novalidate>
                        <input type="hidden" ng-model="data.type" value="contacto"/>
                        <p>  
                            <label>Nombre</label>
                            <input type="text" ng-model="data.firstName" class="form-control" name="firstName" value="{{ old('firstName')}}" required>
                            <span style="color:red" ng-show="(myForm.firstName.$dirty && myForm.firstName.$invalid) || submitted && myForm.firstName.$invalid">
                                <span ng-show="submitted && myForm.firstName.$error.required">Porfavor ingresa tu nombre</span></span>
                        </p>
                        <p>       
                            <label>Apellido</label>
                            <input type="text" ng-model="data.lastName" class="form-control" name="lastName" value="{{ old('lastName')}}" required>
                            <span style="color:red" ng-show="(myForm.lastName.$dirty && myForm.lastName.$invalid) || submitted && myForm.lastName.$invalid">
                                <span ng-show="submitted && myForm.lastName.$error.required">Porfavor ingresa tu apellido</span></span>
                        </p>
                        <p>          
                            <label>Correo</label>
                            <input type="email" ng-model="data.email" class="form-control" name="email" value="{{ old('email')}}" required>
                            <span style="color:red" ng-show="(myForm.email.$dirty && myForm.email.$invalid) || submitted && myForm.email.$invalid">
                                <span ng-show="submitted && myForm.email.$error.required">Porfavor ingresa tu correo</span>
                                    <span ng-show="submitted && myForm.email.$invalid">Porfavor verifica tu correo</span>
                            </span>
                        </p>    
                        <p>
                            <label>Celular</label>
                            <input type="text" ng-model="data.cellphone" class="form-control" name="cellphone" value="{{ old('cellphone')}}" required>
                            <span style="color:red" ng-show="(myForm.cellphone.$dirty && myForm.cellphone.$invalid) || submitted && myForm.cellphone.$invalid">
                                <span ng-show="submitted && myForm.cellphone.$error.required">Porfavor ingresa tu celular</span></span>
                        </p>
                        <div class="contact_textarea">
                            <label>Mensaje</label>
                            <textarea ng-model="data.message" name="message" id="message" cols="30" rows="10" class="form-control" required></textarea>
                            <span style="color:red" ng-show="(myForm.message.$dirty && myForm.message.$invalid) || submitted && myForm.message.$invalid">
                                <span ng-show="submitted && myForm.message.$error.required">Debes enviar un mensaje</span></span>  
                        </div>   
                        <button type="submit"> Enviar</button>  
                        <p class="form-messege"></p>
                    </form> 
                    <script>
                        var viewData = '@json($data)';
                    </script>
                </div> 
            </div>
        </div>
    </div>    
</div>

<!--contact area end-->

@endsection
