@extends(config("app.views").'.layouts.app')
@section('title', 'Petworld mascotas perdidas')
@section('meta_description', 'Si perdiste una mascota, reportala aca y te ayudaremos a encontrarla')
@section('content')
<section>
        <div class=" petmark-slick-slider  home-slider dot-position-1" data-slick-setting='{
             "autoplay": true,
             "autoplaySpeed": 6000,
             "slidesToShow": 1,
             "dots": true
             }'
             >
            <div class="single-slider home-content bg-image" data-bg="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/banner-mascotas-todo.jpg">
                <div class="container position-relative">
                    <div class="row">
                        <div class="col-lg-6">

                            <h3>Deja de buscar por acá y por alla</h3>
                            <h1 class="text-black">TODO PARA TU <br/>MASCOTA</h1>
                            <h4 class="mt--20">En un solo lugar</h4>
                            <p>Urgencias, citas, vacunación, farmacia,<br/> alimentos, accesorios, baño y peluquería,<br/>colegios, guarderías, adiestramiento, adopción,<br/>certificados y trámites, y mucho más!</p>    
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
                <form class="site-form " id="contact-form" role="form" name="myForm" ng-submit="lead(myForm.$valid,'perdidos')" novalidate>
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
                    <h2>CONTACT US</h2>
                </div>
                <div class="contact-right-description">
                    <article class="ct-article">
                        <h3 class="d-none sr-only">blog-article</h3>
                        <p>Claritas est etiam processus dynamicus, qui sequitur mutationem consuetudium lectorum. Mirum est notare quam
                            littera
                            gothica, quam nunc putamus parum claram anteposuerit litterarum formas human.</p>
                    </article>
                    <ul class="contact-list mb--35">
                        <li><i class="fas fa-fax"></i>Address : No 40 Baria Sreet 133/2 NewYork City</li>
                        <li><i class="fas fa-phone"></i>0(1234) 567 890</li>
                        <li><i class="far fa-envelope"></i>Info@roadthemes.com</li>
                    </ul>
                    <div class="working-hour">
                        <h3>Working hours</h3>
                        <p> <strong>Monday – Saturday</strong>: 08AM – 22PM</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
                var viewData = '@json($data)';
    </script>
</section>
@endsection
