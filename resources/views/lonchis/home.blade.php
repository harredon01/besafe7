@extends('lonchis.layouts.app')

@section('content')
<div ng-controller="HomeCtrl">
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
            <div class="single-slider home-content bg-image" data-bg="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/banner-veterinarios.jpg">
                <div class="container position-relative">
                    <div class="row">
                        <div class="col-lg-6">

                            <h1 class="text-black">Consultas, Vacunación<br/> y muchos más</h1>
                            <h4 class="mt--20">servicios veterinarios A <strong>DOMICILIO</strong></h4>

                            <div class="slider-btn mt--30">
                                <a href="/a/merchants/consultas" class="btn btn-outlined--primary btn-rounded">Ver</a>
                            </div>

                        </div>
                    </div>
                </div>
                <span class="herobanner-progress"></span>
            </div>
            <div class="single-slider home-content bg-image" data-bg="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/banner-nuevos-miembros.jpg">
                <div class="container position-relative">
                    <div class="row">
                        <div class="col-lg-6">

                            <h1 class="text-black">¿NUEVOS MIEMBROS<br/>EN LA FAMILIA?</h1>
                            <h4 class="mt--20">Encuentra las familias ideales para<br/>ellos y dejalos en buenas manos.</h4>

                            <div class="slider-btn mt--30">
                                <a href="/a/contact-us/sale" class="btn btn-outlined--primary btn-rounded">Ver</a>
                            </div>

                        </div>
                    </div>
                </div>
                <span class="herobanner-progress"></span>
            </div>

        </div>
    </section>
    <div class="container pt--50">
        <div class="policy-block border-four-column">
            <div class="row">
                <div class="col-lg-6 col-sm-6">
                    <div class="policy-block-single">
                        <div class="icon">
                            <span class="ti-truck"></span>
                        </div>
                        <div class="text">
                            <h3>Cobertura Nacional</h3>
                            <p>En productos y medicinas</p>
                        </div>
                    </div>
                </div>
                <!--div class="col-lg-3 col-sm-6">
                    <div class="policy-block-single">
                        <div class="icon">
                            <span class="ti-credit-card"></span>
                        </div>
                        <div class="text">
                            <h3>Cod</h3>
                            <p>Cash on Delivery</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="policy-block-single">
                        <div class="icon">
                            <span class="ti-gift"></span>
                        </div>
                        <div class="text">
                            <h3>Free Gift Box</h3>
                            <p>Buy a Gift</p>
                        </div>
                    </div>
                </div-->
                <div class="col-lg-6 col-sm-6">
                    <div class="policy-block-single">
                        <div class="icon">
                            <span class="ti-headphone-alt"></span>
                        </div>
                        <div class="text">
                            <h3>Soporte 24/7</h3>
                            <p>Escribenos a cualquier hora</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="pt--50">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="block-title">
                        <h2>Lo último</h2>
                    </div>
                    <!-- Two row Three Column Slider -->
                    <div class="petmark-slick-slider border grid-column-slider" data-slick-setting='{
                         "autoplay": true,
                         "autoplaySpeed": 3000,
                         "slidesToShow": 3,
                         "rows" :2,
                         "arrows": true
                         }'
                         data-slick-responsive='[
                         {"breakpoint":991, "settings": {"slidesToShow": 3} },
                         {"breakpoint":768, "settings": {"slidesToShow": 2} },
                         {"breakpoint":480, "settings": {"slidesToShow": 1,"rows" :1} }
                         ]'>
                        <div class="single-slide">
                            <div class="pm-product">
                                <div class="image">
                                    <a href="/a/product-detail/collar-hojas-verdes?merchant_id=14" ng-click="goTo('merchant-coverage', $event,false)"><img src="https://s3.us-east-2.amazonaws.com/gohife/public/pets-products/14/Artboard-7.jpg" alt=""></a>
                                    <div class="hover-conents">
                                        <ul class="product-btns">
                                            <li><a href="wishlist.html"><i class="ion-ios-heart-outline"></i></a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="content">
                                    <h3>Collar Hojas Verdes</h3>
                                    <div class="price text-red">
                                        <span>$62,000.00</span>
                                    </div>
                                    <div class="btn-block">
                                        <a href="/a/product-detail/collar-hojas-verdes?merchant_id=14" ng-click="goTo('merchant-coverage', $event,false)" class="btn btn-outlined btn-rounded">Ver</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="single-slide">
                            <div class="pm-product">
                                <div class="image">
                                    <a href="/a/product-detail/diamond-naturals-cat-indoor?merchant_id=6" ng-click="goTo('merchant-coverage', $event,false)"><img src="https://s3.us-east-2.amazonaws.com/gohife/public/pets-products/6/DNindoorcat_rev-600x600.png" alt=""></a>
                                    <div class="hover-conents">
                                        <ul class="product-btns">
                                            <li><a href="wishlist.html"><i class="ion-ios-heart-outline"></i></a></li>

                                        </ul>
                                    </div>

                                </div>
                                <div class="content">
                                    <h3>Arena Diamond Naturals Cat Indoor</h3>
                                    <div class="price text-red">
                                        <span>$16,700.00</span>
                                    </div>
                                    <div class="btn-block">
                                        <a href="/a/product-detail/diamond-naturals-cat-indoor?merchant_id=6" ng-click="goTo('merchant-coverage', $event,false,false)" class="btn btn-outlined btn-rounded">Ver</a>
                                        <a href="/a/products/gatos?merchant_id=6" ng-click="goTo('merchant-coverage', $event,false)" style="background:none;color:#56a700">Otros para gatos</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="single-slide">
                            <div class="pm-product">
                                <div class="image">
                                    <a href="/a/product-detail/cama-ziu?merchant_id=5" ng-click="goTo('merchant-coverage', $event,false)"><img src="https://s3.us-east-2.amazonaws.com/gohife/public/pets-products/5/ziu.JPG" alt=""></a>
                                    <div class="hover-conents">
                                        <ul class="product-btns">
                                            <li><a href="wishlist.html"><i class="ion-ios-heart-outline"></i></a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="content">
                                    <h3>Cama Ziu</h3>
                                    <div class="price text-red">
                                        <span>$150,000.00</span>
                                    </div>
                                    <div class="btn-block">
                                        <a href="/a/product-detail/cama-ziu?merchant_id=5" ng-click="goTo('merchant-coverage', $event,false)" class="btn btn-outlined btn-rounded">Ver</a>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="single-slide">
                            <div class="pm-product">
                                <div class="image">
                                    <a href="/a/product-detail/arena-fofi-cat?merchant_id=6" ng-click="goTo('merchant-coverage', $event,false)"><img src="https://s3.us-east-2.amazonaws.com/gohife/public/pets-products/6/ARENA-FOFI-CAT-OPCION-2-1.jpg" alt=""></a>
                                    <div class="hover-conents">
                                        <ul class="product-btns">
                                            <li><a href="wishlist.html"><i class="ion-ios-heart-outline"></i></a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="content">
                                    <h3>Arena Fofi Cat</h3>
                                    <div class="price text-red">
                                        <span>$19,500.00</span>
                                    </div>
                                    <div class="btn-block">
                                        <a href="/a/product-detail/arena-fofi-cat?merchant_id=6" ng-click="goTo('merchant-coverage', $event,false)" class="btn btn-outlined btn-rounded">Ver</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="single-slide">
                            <div class="pm-product">
                                <div class="image">
                                    <a href="/a/product-detail/galico?merchant_id=10" ng-click="goTo('merchant-coverage', $event,false)"><img src="https://s3.us-east-2.amazonaws.com/gohife/public/pets-products/10/galico.JPG" alt=""></a>
                                    <div class="hover-conents">
                                        <ul class="product-btns">
                                            <li><a href="wishlist.html"><i class="ion-ios-heart-outline"></i></a></li>
                                        </ul>
                                    </div>
                                    <span class="onsale-badge">Sale!</span>
                                </div>
                                <div class="content">
                                    <h3>Placa Gálico</h3>
                                    <div class="price text-red">
                                        <span class="old">$34,000.00</span>
                                        <span>$30,600.00</span>
                                    </div>
                                    <div class="btn-block">
                                        <a href="/a/product-detail/galico?merchant_id=10" ng-click="goTo('merchant-coverage', $event,false)" class="btn btn-outlined btn-rounded">Ver</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="single-slide">
                            <div class="pm-product">
                                <div class="image">
                                    <a href="/a/product-detail/hills-dog-adult-sensitive-stomach-skin?merchant_id=6" ng-click="goTo('merchant-coverage', $event,false)"><img src="https://s3.us-east-2.amazonaws.com/gohife/public/pets-products/6/comida-perro-hills-adult-sensitive-stomach-skin-4lb-2.jpg" alt=""></a>
                                    <div class="hover-conents">
                                        <ul class="product-btns">
                                            <li><a href="wishlist.html"><i class="ion-ios-heart-outline"></i></a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="content">
                                    <h3>Hills Dog Adult Sensitive Stomach & Skin</h3>
                                    <div class="price text-red">
                                        <span>$55,500.00</span>
                                    </div>
                                    <div class="btn-block">
                                        <a href="/a/product-detail/hills-dog-adult-sensitive-stomach-skin?merchant_id=6" ng-click="goTo('merchant-coverage', $event,false)" class="btn btn-outlined btn-rounded">Ver</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="single-slide">
                            <div class="pm-product">
                                <div class="image">
                                    <a href="/a/product-detail/consulta-general?merchant_id=13" ng-click="goTo('merchant-list', $event,false)"><img src="https://s3.us-east-2.amazonaws.com/gohife/public/pets-products/12/consultageneral.jpg" alt=""></a>
                                    <div class="hover-conents">
                                        <ul class="product-btns">
                                            <li><a href="wishlist.html"><i class="ion-ios-heart-outline"></i></a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="content">
                                    <h3>Consulta General</h3>
                                    <div class="price text-red">
                                        <span>$30,000.00</span>
                                    </div>
                                    <div class="btn-block">
                                        <a href="/a/product-detail/consulta-general?merchant_id=13" ng-click="goTo('merchant-list', $event,false)" class="btn btn-outlined btn-rounded">Ver</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="single-slide">
                            <div class="pm-product">
                                <div class="image">
                                    <a href="/a/product-detail/refuerzo-anual-canino-basico?merchant_id=13" ng-click="goTo('merchant-list', $event,false)"><img src="https://s3.us-east-2.amazonaws.com/gohife/public/pets-products/12/vacperrdom.jpg" alt=""></a>
                                    <div class="hover-conents">
                                        <ul class="product-btns">
                                            <li><a href="wishlist.html"><i class="ion-ios-heart-outline"></i></a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="content">
                                    <h3>Refuerzo anual canino basico</h3>
                                    <div class="price text-red">
                                        <span>$30,000.00</span>
                                    </div>
                                    <div class="btn-block">
                                        <a href="/a/product-detail/refuerzo-anual-canino-basico?merchant_id=13" ng-click="goTo('merchant-list', $event,false)" class="btn btn-outlined btn-rounded">Ver</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 pt--50 pt-lg-0">
                    <div class="block-title">
                        <h2>A domicilio</h2>
                    </div>
                    <!--Two Row One Column Slider -->
                    <div class="petmark-slick-slider border one-column-slider two-row" data-slick-setting='{
                         "autoplaySpeed": 3000,
                         "slidesToShow": 1,
                         "rows" :2,
                         "arrows": true
                         }'
                         data-slick-responsive='[
                         {"breakpoint":991, "settings": {"slidesToShow": 1} },
                         {"breakpoint":575, "settings": {"slidesToShow": 1} }
                         ]'>
                        <div class="single-slide">
                            <div class="pm-product product-type-list">
                                <a href="/a/product-detail/cita-a-domicilio?merchant_id=7" ng-click="goTo('merchant-coverage', $event,false)" class="image">
                                    <img src="https://s3.us-east-2.amazonaws.com/gohife/public/pets-products/7/cita-vet.jpg" alt="">
                                </a>
                                <div class="content">
                                    <h3>Cita a domicilio</h3>
                                    <div class="price text-red">
                                        <span>$55,000.00</span>
                                    </div>
                                    <div class="rating-widget mt--20">
                                        <a href="" class="single-rating"><i class="fas fa-star"></i></a>
                                        <a href="" class="single-rating"><i class="fas fa-star"></i></a>
                                        <a href="" class="single-rating"><i class="fas fa-star"></i></a>
                                        <a href="" class="single-rating"><i class="fas fa-star"></i></a>
                                        <a href="" class="single-rating"><i class="fas fa-star-half-alt"></i></a>
                                        
                                    </div>
                                    <div class="btn-block">
                                        <a href="/a/product-detail/cita-a-domicilio?merchant_id=7" ng-click="goTo('merchant-coverage', $event,false)" class="btn btn-outlined btn-rounded btn-mid">Ver</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="single-slide">
                            <div class="pm-product product-type-list">
                                <a href="/a/product-detail/refuerzo-anual-gatos-adultos?merchant_id=7" ng-click="goTo('merchant-coverage', $event,false)" class="image">
                                    <img src="https://s3.us-east-2.amazonaws.com/gohife/public/pets-products/7/vacgatdom.jpg" alt="">
                                </a>
                                <div class="content">
                                    <h3>Refuerzo anual gatos adultos</h3>
                                    <div class="price text-red">
                                        <span>$55,000.00</span>
                                    </div>
                                    <div class="rating-widget mt--20">
                                        <a href="" class="single-rating"><i class="fas fa-star"></i></a>
                                        <a href="" class="single-rating"><i class="fas fa-star"></i></a>
                                        <a href="" class="single-rating"><i class="fas fa-star"></i></a>
                                        <a href="" class="single-rating"><i class="fas fa-star"></i></a>
                                        <a href="" class="single-rating"><i class="fas fa-star-half-alt"></i></a>
                                        
                                    </div>
                                    <div class="btn-block">
                                        <a href="/a/product-detail/refuerzo-anual-gatos-adultos?merchant_id=7" ng-click="goTo('merchant-coverage', $event,false)" class="btn btn-outlined btn-rounded btn-mid">Ver</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Slider One / Normal Two Column Slider -->

    <!-- Category Section -->
    <section class="category-section pt--50">
        <div class="container">
            <div class="block-title">
                <h2>Conoce nuestras categorias</h2>
            </div>
            <div class="category-block">
                <div class="row no-gutters">
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <div class="category-block-single">
                            <a href="/a/merchants/urgencias" ng-click="goTo('merchant-nearby-list', $event,true)" class="icon">
                                <img src="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/urgencias.jpg" alt="">
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <div class="category-block-single">
                            <a href="/a/merchants/consultas" ng-click="goTo('merchant-list', $event,true)" class="icon">
                                <img src="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/citas.jpg" alt="">
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <div class="category-block-single">
                            <a href="/a/merchants/bano-y-peluqueria" ng-click="goTo('merchant-nearby-list', $event,true)" class="icon">
                                <img src="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/peluqueria.jpg" alt="">
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <div class="category-block-single">
                            <a href="/a/merchants/perros" ng-click="goTo('merchant-coverage-products', $event,true)" class="icon">
                                <img src="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/perros.jpg" alt="">
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <div class="category-block-single">
                            <a href="/a/merchants/gatos" ng-click="goTo('merchant-coverage-products', $event,true)" class="icon">
                                <img src="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/gatos.jpg" alt="">
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <div class="category-block-single">
                            <a href="/a/reports/mascotas-perdidas" ng-click="goTo('report-nearby-list', $event,true)" class="icon">
                                <img src="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/mascotas-perdidas.jpg" alt="">
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Promotion Block 1 -->
    <section class="pt--50 space-db--30">
        <h2 class="d-none">Promotion Block
        </h2>
        <div class="container">
            <a class="promo-image overflow-image" href="/a/reports/adopcion" ng-click="goTo('report-nearby', $event,true)">
                <img src="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/adoptame-largo.jpg" alt="">
            </a>
        </div>
    </section>

    <!-- Promotion Block 2 -->
    <section class="pt--50 space-db--30">
        <h2 class="d-none">Promotion Block
        </h2> 
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-4 col-md-4">
                    <a class="promo-image overflow-image">
                        <img src="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/todo-perro-cuadrado.jpg" alt="">
                    </a>
                </div>
                <div class="col-lg-4 col-md-4">
                    <a class="promo-image overflow-image  promo-small " ng-click="goTo('report-nearby', $event,true)" href="/a/reports/mascotas-perdidas">
                        <img src="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/mascotas-perdidas-mini.jpg" alt="">
                    </a>
                    <a class="promo-image overflow-image  promo-small " ng-click="goTo('merchant-list', $event,true)">
                        <img src="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/viaja-mascota.jpg" alt="">
                    </a>
                </div>
                <div class="col-lg-4 col-md-4">
                    <a class="promo-image overflow-image" href="/a/merchants/servicios-veterinarias-a-domicilio" ng-click="goTo('merchant-coverage', $event,true)">
                        <img src="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/servicios-veterinarios-cuadrado.jpg" alt="">
                    </a>
                </div>
            </div>
        </div>
    </section>
    <br/><br/><br/>
    <div class="pt--50 pb--50">
        <div class="container">

            <div class="petmark-slick-slider brand-slider  border normal-slider grid-border-none" data-slick-setting='{
                 "autoplay": true,
                 "autoplaySpeed": 3000,
                 "slidesToShow": 5,
                 "arrows": true
                 }'
                 data-slick-responsive='[
                 {"breakpoint":991, "settings": {"slidesToShow": 4} },
                 {"breakpoint":768, "settings": {"slidesToShow": 3} },
                 {"breakpoint":480, "settings": {"slidesToShow": 2} },
                 {"breakpoint":320, "settings": {"slidesToShow": 1} }
                 ]'>

                <div class="single-slide">
                    <a href="/a/merchant/amadera-colombia/products" ng-click="goTo('merchant-coverage', $event,false)" class="overflow-image brand-image">
                        <img src="https://s3.us-east-2.amazonaws.com/gohife/public/pets-merchants/amadera-col.jpg" alt="">
                    </a>
                </div>
                <div class="single-slide">
                    <a href="/a/merchant/la-mascota-que-rie/products" ng-click="goTo('merchant-coverage', $event,false)" class="overflow-image brand-image">
                        <img src="https://s3.us-east-2.amazonaws.com/gohife/public/pets-merchants/la_mascota_que_rie.JPG" alt="">
                    </a>
                </div>
                <div class="single-slide">
                    <a href="/a/merchant/mini-me-mascotas/products" ng-click="goTo('merchant-coverage', $event,false)" class="overflow-image brand-image">
                        <img src="https://s3.us-east-2.amazonaws.com/gohife/public/pets-merchants/mini-me.jpg" alt="">
                    </a>
                </div>
                <div class="single-slide">
                    <a href="/a/merchant/vetivet-clinica-veterinaria/products" ng-click="goTo('merchant-list', $event,false)" class="overflow-image brand-image">
                        <img src="https://s3.us-east-2.amazonaws.com/gohife/public/pets-merchants/vetivet.JPG" alt="">
                    </a>
                </div>
                <div class="single-slide">
                    <a href="/a/merchant/feroz/products" ng-click="goTo('merchant-coverage', $event,false)" class="overflow-image brand-image">
                        <img src="https://s3.us-east-2.amazonaws.com/gohife/public/pets-merchants/feroz.png" alt="">
                    </a>
                </div>
                <div class="single-slide">
                    <a href="/a/merchant/mision-canina-colombia" ng-click="goTo('merchant-list', $event,false)" class="overflow-image brand-image">
                        <img src="https://s3.us-east-2.amazonaws.com/gohife/public/pets-merchants/logo_mision_canina.png" alt="">
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
