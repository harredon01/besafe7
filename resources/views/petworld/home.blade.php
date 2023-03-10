@extends('petworld.layouts.app')

@section('content')
<link rel="preload" as="image" href="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/Banner-servicios-vets.webp">
<link rel="preload" as="image" href="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/Bannerfarmacia-vet.webp">
<link rel="preload" as="image" href="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/Banner-hotel.webp">
<link rel="preload" as="image" href="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/Banner-spa.webp">
<link rel="preload" as="image" href="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/todo-perro-clean.webp">
<link rel="preload" as="image" href="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/comida_perro-clean.webp">
<link rel="preload" as="image" href="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/comida_perro-clean.webp">
<div ng-controller="HomeCtrl">
    <section id="main-gallery">
        <div class=" petmark-slick-slider  home-slider dot-position-1" data-slick-setting='{
             "autoplay": true,
             "autoplaySpeed": 6000,
             "slidesToShow": 1,
             "dots": true
             }'
             >
            
            <div class="single-slider home-content bg-image" data-bg="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/Banner-servicios-vets.webp">
                <div class="container position-relative">
                    <div class="row">
                        <div class="col-lg-6">
<br/><br/><br/><br/><br/><br/><br/><br/>
                            <div class="slider-btn mt--30">
                                <a href="/a/merchants/consultas" class="btn btn-outlined--primary btn-rounded">Ver</a>
                            </div>    
                        </div>
                    </div>

                </div>
                <span class="herobanner-progress"></span>
            </div>
            <div class="single-slider home-content bg-image" data-bg="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/Bannerfarmacia-vet.webp">
                <div class="container position-relative">
                    <div class="row">
                        <div class="col-lg-6">
<br/><br/><br/><br/><br/><br/><br/><br/>
                            <div class="slider-btn mt--30">
                                <a href="/a/merchants/farmacia" class="btn btn-outlined--primary btn-rounded" style="border:2px solid white !important;color:white">Ver</a>
                            </div>

                        </div>
                    </div>
                </div>
                <span class="herobanner-progress"></span>
            </div>
            <div class="single-slider home-content bg-image" data-bg="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/Banner-hotel.webp">
                <div class="container position-relative">
                    <div class="row">
                        <div class="col-lg-6">
<br/><br/><br/><br/><br/><br/><br/><br/>
                            <div class="slider-btn mt--30">
                                <a href="/a/merchants/colegios-hoteles-y-guarderias" class="btn btn-outlined--primary btn-rounded">Ver</a>
                            </div>

                        </div>
                    </div>
                </div>
                <span class="herobanner-progress"></span>
            </div>
            <div class="single-slider home-content bg-image" data-bg="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/Banner-spa.webp">
                <div class="container position-relative">
                    <div class="row">
                        <div class="col-lg-6">
<br/><br/><br/><br/><br/><br/><br/><br/>
                            <div class="slider-btn mt--30">
                                <a href="/a/merchants/bano-y-peluqueria" class="btn btn-outlined--primary btn-rounded">Ver</a>
                            </div>

                        </div>
                    </div>
                </div>
                <span class="herobanner-progress"></span>
            </div>
            

        </div>
    </section>
    <div class="container pt--50 pb--50" id="shipping-info">
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
                <div class="col-lg-6 col-sm-6">
                    <div class="policy-block-single">
                        <div class="icon">
                            <span class="ti-credit-card"></span>
                        </div>
                        <div class="text">
                            <h3>Tarjeta de Credio</h3>
                            <p>Nequi</p>
                        </div>
                    </div>
                </div>
                <!--div class="col-lg-3 col-sm-6">
                    <div class="policy-block-single">
                        <div class="icon">
                            <span class="ti-gift"></span>
                        </div>
                        <div class="text">
                            <h3>Free Gift Box</h3>
                            <p>Buy a Gift</p>
                        </div>
                    </div>
                </div>
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
                </div-->
            </div>
        </div>
    </div>
    <section class="pt--50 space-db--30 show-responsive">
        <h2 class="d-none">Promotion Block
        </h2> 
        <div class="container">
            <div class="block-title">
                <h2>Servicios de salud para mascotas</h2>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-4 col-md-4">
                    <a class="promo-image overflow-image" href="/a/merchants/servicios-veterinarias-a-domicilio" ng-click="goTo('merchant-coverage', $event, true)">
                        <img data-src="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/banner-quad-servicios-vets.webp" class="lazyload" alt="">
                    </a>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-4 col-md-4">
                    <a class="promo-image overflow-image" href="/a/merchants/farmacia" ng-click="goTo('merchant-nearby', $event, true)">
                        <img data-src="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/banner-quad-farmacia.webp" class="lazyload" alt="">
                    </a>
                </div>
            </div>
        </div>
    </section>
    <div class="pt--50">
        <div class="container vet-container">
            <div class="row">
                <div class="col-lg-12 pt--50 pt-lg-0">
                    <div class="block-title">
                        <h2>Necesitas un veterinario?</h2>
                    </div>
                    <!--Two Row One Column Slider -->
                    @foreach ($vets as $merchant)
                        <div class="single-slide">
                            <div class="pm-product product-type-list">
                                <a href="/a/merchant/{{$merchant['slug']}}/products" ng-click="goTo('merchant-coverage', $event, false)" style="width:12%;padding:0" class="image">
                                    <img class="lazyload" data-src="{{ $merchant['icon']}}" alt="" style="width:100%">
                                </a>
                                <div class="content vets">
                                    <h3><a href="/a/merchant/{{$merchant['slug']}}/products" ng-click="goTo('merchant-coverage', $event, false)">{{ $merchant['name']}}</a></h3>
                                    <div class="price">
                                        <span>Direccion: {{ $merchant['address']}}</span><br/>
                                        <span>Telefono: <a href="tel:{{ $merchant['telephone']}}" class="text-primary">{{ $merchant['telephone']}}</a></span><br/>
                                        <span>Correo: <a href="mailto:{{ $merchant['email']}}" class="text-primary">{{ $merchant['email']}}</a></span>
                                    </div>
                                    <div class="rating-widget mt--20">
                                        <a href="" class="single-rating"><i class="fas fa-star"></i></a>
                                        <a href="" class="single-rating"><i class="fas fa-star"></i></a>
                                        <a href="" class="single-rating"><i class="fas fa-star"></i></a>
                                        <a href="" class="single-rating"><i class="fas fa-star"></i></a>
                                        <a href="" class="single-rating"><i class="fas fa-star-half-alt"></i></a>

                                    </div>
                                    <p class="show-responsive">{{ $merchant['description']}}</p>
                                </div>
                                <div class="hide-responsive" style="padding: 0 10px 10px;width: 38%;">
                                    <p>{{ $merchant['description']}}</p>
                                    </div>
                                <div class="btn-block" style="padding:10px; width: 20%">
                                    <a href="/a/merchant/{{$merchant['slug']}}/products" ng-click="goTo('merchant-coverage', $event, false)" class="btn btn-outlined" style="width:100%;margin-top:20px">Ver</a>
                                    </div>
                            </div>
                        </div>
                        @endforeach
                </div>
            </div>
        </div>
    </div>
    <section class="space-db--30">
        <h2 class="d-none">Promotion Block
        </h2> 
        <div class="container">
            <div class="block-title hide-responsive">
                <h2>Encuentra en nuestras tiendas</h2>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-4 col-md-4">
                    <a class="promo-image overflow-image" href="/a/merchants/alimentos-para-perros" ng-click="goTo('merchant-list', $event, true)">
                        <img src="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/comida_perro-clean.webp" alt="">
                    </a>
                </div>
                
                <div class="col-lg-4 col-md-4">
                    <a class="promo-image overflow-image  promo-small " ng-click="goTo('merchant-list', $event, true)" href="/a/merchants/todo-para-perros">
                        <img data-src="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/todo-perro-clean.webp" class="lazyload" alt="">
                    </a>
                    <a class="promo-image overflow-image  promo-small " ng-click="goTo('merchant-list', $event, true)" href="/a/merchants/todo-para-gatos">
                        <img data-src="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/todo-gato-clean.webp" class="lazyload" alt="">
                    </a>
                </div>
                <div class="col-lg-4 col-md-4">
                    <a class="promo-image overflow-image" href="/a/merchants/alimentos-para-gatos" ng-click="goTo('merchant-list', $event, true)">
                        <img data-src="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/comida_gato-clean.webp" class="lazyload" alt="">
                    </a>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Promotion Block 2 -->
    <section class="pt--50 space-db--30">
        <h2 class="d-none">Promotion Block
        </h2> 
        <div class="container">
            <div class="block-title">
                <h2>Encuentra en nuestros veterinarios</h2>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-4 col-md-4">
                    <a class="promo-image overflow-image" ng-click="goTo('merchant-nearby', $event, true)" href="/a/merchants/urgencias">
                        <img data-src="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/urgencias-clean.webp" class="lazyload" alt="">
                    </a>
                </div>
                <div class="col-lg-4 col-md-4">
                    <a class="promo-image overflow-image  promo-small " ng-click="goTo('merchant-nearby', $event, true)" href="/a/merchants/consultas">
                        <img data-src="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/veternarias-cerca.webp" class="lazyload" alt="">
                    </a>
                    <a class="promo-image overflow-image  promo-small " ng-click="goTo('merchant-list', $event, true)" href="/a/merchants/vacunacion">
                        <img data-src="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/vacunacion-clean.webp" class="lazyload" alt="">
                    </a>
                </div>
                <div class="col-lg-4 col-md-4">
                    <a class="promo-image overflow-image" href="/a/merchants/colegios-hoteles-y-guarderias" ng-click="goTo('merchant-list', $event, true)">
                        <img data-src="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/banner-quad-hotel.webp" class="lazyload" alt="">
                    </a>
                </div>
            </div>
        </div>
    </section>
    
    
    <div class="pt--50">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="block-title">
                        <h2>Lo ??ltimo</h2>
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
                                    <a href="/a/product-detail/collar-hojas-verdes?merchant_id=14" ng-click="goTo('merchant-coverage', $event, false)"><img data-src="https://s3.us-east-2.amazonaws.com/gohife/public/pets-products/14/Artboard-7.webp" class="lazyload" alt=""></a>
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
                                        <a href="/a/product-detail/collar-hojas-verdes?merchant_id=14" ng-click="goTo('merchant-coverage', $event, false)" class="btn btn-outlined btn-rounded">Ver</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="single-slide">
                            <div class="pm-product">
                                <div class="image">
                                    <a href="/a/product-detail/diamond-naturals-cat-indoor?merchant_id=6" ng-click="goTo('merchant-coverage', $event, false)"><img class="lazyload" data-src="https://s3.us-east-2.amazonaws.com/gohife/public/pets-products/6/DNindoorcat_rev-600x600.webp" alt=""></a>
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
                                        <a href="/a/product-detail/diamond-naturals-cat-indoor?merchant_id=6" ng-click="goTo('merchant-coverage', $event, false, false)" class="btn btn-outlined btn-rounded">Ver</a>
                                        <a href="/a/products/gatos?merchant_id=6" ng-click="goTo('merchant-coverage', $event, false)" style="background:none;color:#56a700">Otros para gatos</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="single-slide">
                            <div class="pm-product">
                                <div class="image">
                                    <a href="/a/product-detail/cama-ziu?merchant_id=5" ng-click="goTo('merchant-coverage', $event, false)"><img class="lazyload" data-src="https://s3.us-east-2.amazonaws.com/gohife/public/pets-products/5/ziu.webp" alt=""></a>
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
                                        <a href="/a/product-detail/cama-ziu?merchant_id=5" ng-click="goTo('merchant-coverage', $event, false)" class="btn btn-outlined btn-rounded">Ver</a>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="single-slide">
                            <div class="pm-product">
                                <div class="image">
                                    <a href="/a/product-detail/arena-fofi-cat?merchant_id=6" ng-click="goTo('merchant-coverage', $event, false)"><img class="lazyload" data-src="https://s3.us-east-2.amazonaws.com/gohife/public/pets-products/6/ARENA-FOFI-CAT-OPCION-2-1.webp" alt=""></a>
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
                                        <a href="/a/product-detail/arena-fofi-cat?merchant_id=6" ng-click="goTo('merchant-coverage', $event, false)" class="btn btn-outlined btn-rounded">Ver</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="single-slide">
                            <div class="pm-product">
                                <div class="image">
                                    <a href="/a/product-detail/galico?merchant_id=10" ng-click="goTo('merchant-coverage', $event, false)"><img class="lazyload" data-src="https://s3.us-east-2.amazonaws.com/gohife/public/pets-products/10/galico.webp" alt=""></a>
                                    <div class="hover-conents">
                                        <ul class="product-btns">
                                            <li><a href="wishlist.html"><i class="ion-ios-heart-outline"></i></a></li>
                                        </ul>
                                    </div>
                                    <span class="onsale-badge">Sale!</span>
                                </div>
                                <div class="content">
                                    <h3>Placa G??lico</h3>
                                    <div class="price text-red">
                                        <span class="old">$34,000.00</span>
                                        <span>$30,600.00</span>
                                    </div>
                                    <div class="btn-block">
                                        <a href="/a/product-detail/galico?merchant_id=10" ng-click="goTo('merchant-coverage', $event, false)" class="btn btn-outlined btn-rounded">Ver</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="single-slide">
                            <div class="pm-product">
                                <div class="image">
                                    <a href="/a/product-detail/hills-dog-adult-sensitive-stomach-skin?merchant_id=6" ng-click="goTo('merchant-coverage', $event, false)"><img class="lazyload" data-src="https://s3.us-east-2.amazonaws.com/gohife/public/pets-products/6/comida-perro-hills-adult-sensitive-stomach-skin-4lb-2.webp" alt=""></a>
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
                                        <a href="/a/product-detail/hills-dog-adult-sensitive-stomach-skin?merchant_id=6" ng-click="goTo('merchant-coverage', $event, false)" class="btn btn-outlined btn-rounded">Ver</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="single-slide">
                            <div class="pm-product">
                                <div class="image">
                                    <a href="/a/product-detail/consulta-general?merchant_id=13" ng-click="goTo('merchant-list', $event, false)"><img class="lazyload" data-src="https://s3.us-east-2.amazonaws.com/gohife/public/pets-products/12/consultageneral.webp" alt=""></a>
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
                                        <a href="/a/product-detail/consulta-general?merchant_id=13" ng-click="goTo('merchant-list', $event, false)" class="btn btn-outlined btn-rounded">Ver</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="single-slide">
                            <div class="pm-product">
                                <div class="image">
                                    <a href="/a/product-detail/refuerzo-anual-canino-basico?merchant_id=13" ng-click="goTo('merchant-list', $event, false)"><img class="lazyload" data-src="https://s3.us-east-2.amazonaws.com/gohife/public/pets-products/12/vacperrdom.webp" alt=""></a>
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
                                        <a href="/a/product-detail/refuerzo-anual-canino-basico?merchant_id=13" ng-click="goTo('merchant-list', $event, false)" class="btn btn-outlined btn-rounded">Ver</a>
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
                                <a href="/a/product-detail/cita-a-domicilio?merchant_id=7" ng-click="goTo('merchant-coverage', $event, false)" class="image">
                                    <img class="lazyload" data-src="https://s3.us-east-2.amazonaws.com/gohife/public/pets-products/7/cita-vet.webp" alt="">
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
                                        <a href="/a/product-detail/cita-a-domicilio?merchant_id=7" ng-click="goTo('merchant-coverage', $event, false)" class="btn btn-outlined btn-rounded btn-mid">Ver</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="single-slide">
                            <div class="pm-product product-type-list">
                                <a href="/a/product-detail/refuerzo-anual-gatos-adultos?merchant_id=7" ng-click="goTo('merchant-coverage', $event, false)" class="image">
                                    <img class="lazyload" data-src="https://s3.us-east-2.amazonaws.com/gohife/public/pets-products/7/vacgatdom.webp" alt="">
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
                                        <a href="/a/product-detail/refuerzo-anual-gatos-adultos?merchant_id=7" ng-click="goTo('merchant-coverage', $event, false)" class="btn btn-outlined btn-rounded btn-mid">Ver</a>
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

    
    <!-- Promotion Block 3 -->
    <section class="pt--50 space-db--30">
        <h2 class="d-none">Promotion Block
        </h2>
        <div class="container">
            <a class="promo-image overflow-image" href="/a/reports/adopcion" ng-click="goTo('report-nearby', $event, true)">
                <img class="lazyload" data-src="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/adoptame-largo.webp" alt="">
            </a>
        </div>
    </section>

    
    
    <br/><br/><br/>
    <div class="pt--50 pb--50">
        <div class="container">
            <div class="block-title">
                <h2>Nuestras Marcas</h2>
            </div>
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
                    <a href="/a/merchant/amadera-colombia/products" ng-click="goTo('merchant-coverage', $event, false)" class="overflow-image brand-image">
                        <img class="lazyload" data-src="https://s3.us-east-2.amazonaws.com/gohife/public/pets-merchants/amadera-col.webp" alt="">
                    </a>
                </div>
                <div class="single-slide">
                    <a href="/a/merchant/la-mascota-que-rie/products" ng-click="goTo('merchant-coverage', $event, false)" class="overflow-image brand-image">
                        <img class="lazyload" data-src="https://s3.us-east-2.amazonaws.com/gohife/public/pets-merchants/la_mascota_que_rie.webp" alt="">
                    </a>
                </div>
                <div class="single-slide">
                    <a href="/a/merchant/mini-me-mascotas/products" ng-click="goTo('merchant-coverage', $event, false)" class="overflow-image brand-image">
                        <img class="lazyload" data-src="https://s3.us-east-2.amazonaws.com/gohife/public/pets-merchants/mini-me.webp" alt="">
                    </a>
                </div>
                <div class="single-slide">
                    <a href="/a/merchant/vetivet-clinica-veterinaria/products" ng-click="goTo('merchant-list', $event, false)" class="overflow-image brand-image">
                        <img class="lazyload" data-src="https://s3.us-east-2.amazonaws.com/gohife/public/pets-merchants/vetivet.webp" alt="">
                    </a>
                </div>
                <div class="single-slide">
                    <a href="/a/merchant/feroz/products" ng-click="goTo('merchant-coverage', $event, false)" class="overflow-image brand-image">
                        <img class="lazyload" data-src="https://s3.us-east-2.amazonaws.com/gohife/public/pets-merchants/feroz.webp" alt="">
                    </a>
                </div>
                <div class="single-slide">
                    <a href="/a/merchant/aviomar-pets-relocation" ng-click="goTo('merchant-list', $event, false)" class="overflow-image brand-image">
                        <img class="lazyload" data-src="https://s3.us-east-2.amazonaws.com/gohife/public/pets-merchants/aviomar.webp" alt="">
                    </a>
                </div>
                <div class="single-slide">
                    <a href="/a/merchant/mision-canina-colombia" ng-click="goTo('merchant-list', $event, false)" class="overflow-image brand-image">
                        <img class="lazyload" data-src="https://s3.us-east-2.amazonaws.com/gohife/public/pets-merchants/logo_mision_canina.webp" alt="">
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
