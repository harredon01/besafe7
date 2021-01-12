@extends(config("app.views").'.layouts.app')
@section('title', 'Petworld Inscripcion veterinarios')
@section('meta_description', 'Si eres veterinario y te gustaría vender tus servicios con nosotros, inscribete aca')
@section('content')
<section>
        <div class=" petmark-slick-slider  home-slider dot-position-1" data-slick-setting='{
             "autoplay": true,
             "autoplaySpeed": 6000,
             "slidesToShow": 1,
             "dots": true
             }'
             >
            <div class="single-slider home-content bg-image" data-bg="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/Banner-veterinarios.png">
                <div class="container position-relative">
                    <div class="row">
                        <div class="col-lg-6" style="background: rgba(255,255,255,.5);">
                            <h3>Brinda un mejor servicio y aumenta tus ventas.</h3>
                            <h1 class="text-black">¡Veterinario es tiempo de digitalizarte!</h1>
                            <h4 class="mt--20">Tu enfócate en el bienestar de las mascotas, que nuestra tecnología te ayudará a optimizar el resto.</h4>
                            
                        </div>
                    </div>

                </div>
                <span class="herobanner-progress"></span>
            </div>
        </div>
    </section>
<section class="contact-page-section overflow-hidden"  ng-controller="LeadCtrl">
    <div class="row">
        <div class="col-md-6">
            <div class="ct-single-side">
                <div class="ct-section-title">
                    <h2>Escribenos</h2>
                </div>
                <form class="site-form " id="contact-form" role="form" name="myForm" ng-submit="lead(myForm.$valid,'veterinarios')" novalidate>
                    <div class="row">
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
                                    <span ng-show="submitted && myForm.message.$error.required">Debes ingresar un mensaje</span>
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
                    <h2>Escríbenos</h2>
                </div>
                <div class="contact-right-description">
                    <article class="ct-article">
                        <h3 class="d-none sr-only">Si eres veterinari@ profesional y prestas servicios en Colombia,</h3>
                        <p>Inscribe tu negocio en Pet world y ofrece tus productos y servicios a nuestra comunidad. 
Aquí podrás acceder a beneficios para aumentar tus ventas y digitalizar tu negocio: tienda en línea, sistema de reservas, métodos de pagos online, servicio de domicilio para envíos a toda Bogotá y mucho más.</p>
                        <p>¡No lo dejes pasar! Hoy en día tener presencia digital es una oportunidad, mañana va a ser una necesidad. y los primeros siempre tendrán la ventaja. empieza ya.</p>
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
