@extends(config("app.views").'.layouts.app')

@section('content')
<section class="contact-page-section overflow-hidden"  ng-controller="LeadCtrl">
    <div class="row">
        <div class="col-md-6">
            <div class="ct-single-side">
                <div class="ct-section-title">
                    <h2>Escribenos</h2>
                </div>
                <form class="site-form " id="contact-form" role="form" name="myForm" ng-submit="send(myForm.$valid)" novalidate>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label>Tipo</label>
                                <select ng-model="data.type" class="nice-select" name="type" ng-change=""required>
                                    <option value="Preguntas">Preguntas</option>
                                    <option value="Quejas">Quejas</option>
                                    <option value="Trabaja">Trabaja con nosotros</option>
                                </select>
                                <span style="color:red" ng-show="(myForm.type.$dirty && myForm.type.$invalid) || submitted && myForm.type.$invalid">
                                    <span ng-show="submitted && myForm.type.$error.required">Porfavor Selecciona un tipo de comunicacion</span>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Nombre</label>
                                <input type="text" ng-model="data.firstName" class="form-control" name="firstName" value="{{ old('firstName')}}" required>
                                <span style="color:red" ng-show="(myForm.firstName.$dirty && myForm.firstName.$invalid) || submitted && myForm.firstName.$invalid">
                                    <span ng-show="submitted && myForm.firstName.$error.required">Porfavor ingresa tu nombre</span>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Apellido</label>
                                <input type="text" ng-model="data.lastName" class="form-control" name="lastName" value="{{ old('lastName')}}" required>
                                <span style="color:red" ng-show="(myForm.lastName.$dirty && myForm.lastName.$invalid) || submitted && myForm.lastName.$invalid">
                                    <span ng-show="submitted && myForm.lastName.$error.required">Porfavor ingresa tu apellido</span>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Correo</label>
                                <input type="email" ng-model="data.email" class="form-control" name="email" value="{{ old('email')}}" required>
                                <span style="color:red" ng-show="(myForm.email.$dirty && myForm.email.$invalid) || submitted && myForm.email.$invalid">
                                    <span ng-show="submitted && myForm.email.$error.required">Porfavor ingresa tu correo</span>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Celular</label>
                                <input type="text" ng-model="data.cellphone" class="form-control" name="cellphone" value="{{ old('cellphone')}}" required>
                                <span style="color:red" ng-show="(myForm.cellphone.$dirty && myForm.cellphone.$invalid) || submitted && myForm.cellphone.$invalid">
                                    <span ng-show="submitted && myForm.cellphone.$error.required">Porfavor ingresa tu celular</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Mensaje</label>
                                <textarea ng-model="data.message" name="message" id="message" cols="30" rows="10" class="form-control" required></textarea>
                                <span style="color:red" ng-show="(myForm.message.$dirty && myForm.message.$invalid) || submitted && myForm.message.$invalid">
                                    <span ng-show="submitted && myForm.message.$error.required">Debes enviar un mensaje</span>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="submit-btn">
                                <button type="submit" class="btn btn-black">Enviar</button>
                            </div>
                        </div>
                        <div class="form-messege"></div>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-md-6 bg-gray">
            <div class="ct-single-side">
                <div class="section-title mb--20">
                    <h2>Escr??benos</h2>
                </div>
                <div class="contact-right-description">
                    <article class="ct-article">
                        <h3 class="d-none sr-only">blog-article</h3>
                        <p>Estamos para servirte. completa el formulario y uno de nuestros agentes se pondr?? en contacto contigo</p>
                    </article>
                    <ul class="contact-list mb--35">
                        <li><a href="tel:+573103418432"><i class="fas fa-phone"></i>+57 310 341 8432</a></li>
                        <li><a href="mailto:servicioalcliente@petworld.net.co"><i class="far fa-envelope"></i>servicioalcliente@petworld.net.co</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <script>
                var viewData = '@json($data)';
    </script>
</section>
@endsection
