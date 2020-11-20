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
                                <a href="/a/merchants/vets" class="btn btn-outlined--primary btn-rounded">Ver</a>
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
                                <a href="/a/contact-us/sale-lead" class="btn btn-outlined--primary btn-rounded">Ver</a>
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
    <!-- Slider One / Normal Two Column Slider -->
    <section class="pt--50">
        <div class="container">
            <div class="block-title">
                <h2>NEW PRODUCTS</h2>
            </div>
            <div class="petmark-slick-slider border normal-two-column-slider"
                 data-slick-setting='{
                 "autoplaySpeed": 3000,
                 "slidesToShow": 2,
                 "arrows": true
                 }'
                 data-slick-responsive='[
                 {"breakpoint":991, "settings": {"slidesToShow": 1} },
                 {"breakpoint":575, "settings": {"slidesToShow": 1} }
                 ]'>
                <div class="single-slide">
                    <div class="pm-product product-type-list">
                        <a href="product-details.html" class="image">
                            <img src="image/product/home-1/product-1.jpg" alt="">
                        </a>
                        <div class="content">
                            <h3 class="font-weight-500"> <a href="">Convallis quam sit</a></h3>
                            <div class="price text-red mb-3" >
                                <span class="old">$200</span>
                                <span>$300</span>
                            </div>
                            <p >Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam fringilla augue nec est tristique auctor.</p>
                            <div class="count-down-block">
                                <div class="product-countdown" data-countdown="2020/05/01"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="single-slide">
                    <div class="pm-product product-type-list">
                        <a href="product-details.html" class="image">
                            <img src="image/product/home-1/product-2.jpg" alt="">
                        </a>
                        <div class="content">
                            <h3 class="font-weight-500"> <a href="">Convallis quam sit</a></h3>
                            <div class="price text-red mb-3" >
                                <span class="old">$200</span>
                                <span>$300</span>
                            </div>
                            <p >Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam fringilla augue nec est tristique auctor.</p>
                            <div class="count-down-block">
                                <div class="product-countdown" data-countdown="2020/05/01"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="single-slide">
                    <div class="pm-product product-type-list">
                        <a href="product-details.html" class="image">
                            <img src="image/product/home-1/product-3.jpg" alt="">
                        </a>
                        <div class="content">
                            <h3 class="font-weight-500"> <a href="">Convallis quam sit</a></h3>
                            <div class="price text-red mb-3" >
                                <span class="old">$200</span>
                                <span>$300</span>
                            </div>
                            <p >Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam fringilla augue nec est tristique auctor.</p>
                            <div class="count-down-block">
                                <div class="product-countdown" data-countdown="2020/05/01"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="single-slide">
                    <div class="pm-product product-type-list">
                        <a href="product-details.html" class="image">
                            <img src="image/product/home-1/product-4.jpg" alt="">
                        </a>
                        <div class="content">
                            <h3 class="font-weight-500"> <a href="">Convallis quam sit</a></h3>
                            <div class="price text-red mb-3" >
                                <span class="old">$200</span>
                                <span>$300</span>
                            </div>
                            <p >Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam fringilla augue nec est tristique auctor.</p>
                            <div class="count-down-block">
                                <div class="product-countdown" data-countdown="2020/05/01"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
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
                            <a href="/a/merchants/urgencias-24h" ng-click="goTo('merchant-nearby-list', $event)" class="icon">
                                <img src="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/urgencias.jpg" alt="">
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <div class="category-block-single">
                            <a href="/a/merchants/consultas" ng-click="goTo('merchant-list', $event)" class="icon">
                                <img src="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/citas.jpg" alt="">
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <div class="category-block-single">
                            <a href="/a/merchants/bano-y-peluqueria" ng-click="goTo('merchant-nearby-list', $event)" class="icon">
                                <img src="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/peluqueria.jpg" alt="">
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <div class="category-block-single">
                            <a href="/a/merchants/productos-para-perros" ng-click="goTo('merchant-coverage-products', $event)" class="icon">
                                <img src="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/perros.jpg" alt="">
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <div class="category-block-single">
                            <a href="/a/merchants/productos-para-gatos" ng-click="goTo('merchant-coverage-products', $event)" class="icon">
                                <img src="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/gatos.jpg" alt="">
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <div class="category-block-single">
                            <a href="/a/reports/mascotas-perdidas" ng-click="goTo('report-nearby-list', $event)" class="icon">
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
            <a class="promo-image overflow-image">
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
                    <a class="promo-image overflow-image  promo-small ">
                        <img src="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/mascotas-perdidas-mini.jpg" alt="">
                    </a>
                    <a class="promo-image overflow-image  promo-small ">
                        <img src="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/viaja-mascota.jpg" alt="">
                    </a>
                </div>
                <div class="col-lg-4 col-md-4">
                    <a class="promo-image overflow-image">
                        <img src="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/servicios-veterinarios-cuadrado.jpg" alt="">
                    </a>
                </div>
            </div>
        </div>
    </section>
    <br/><br/><br/>
</div>
@endsection
