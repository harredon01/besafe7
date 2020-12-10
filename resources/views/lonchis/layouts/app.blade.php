<!DOCTYPE html>
<html lang="en" ng-app="besafe" ng-strict-di>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel')}}</title>

        <!-- Styles -->
        <link href="/css/app.css" rel="stylesheet">
        <link href="/css/style.css" rel="stylesheet">
        <link rel="stylesheet" href="/css/plugins.css" />
        <link rel="stylesheet" href="/css/main.css" />
        <link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/angular_material/1.1.0/angular-material.min.css">
        <!-- Scripts 
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-sortablejs@latest/jquery-sortable.js"></script>-->

        <script src="/js/app.js"></script>
        <script src="/js/plugins.js"></script>

        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.0/angular.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.0/angular-cookies.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.0/angular-route.js"></script>
        <script src="http://ajax.googleapis.com/ajax/libs/angular_material/1.1.0/angular-material.min.js"></script>
        <script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.5.5/angular-animate.min.js"></script>
        <script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.5.5/angular-aria.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/angular-google-maps/2.3.2/angular-google-maps.min.js"></script>
        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCOlc_3d8ygnNCMRzfEpmvSNsYtmbowtYo"></script>
        <script type="text/javascript" src="https://platform-api.sharethis.com/js/sharethis.js#property=5fa5a79fcc85000012ec2cee&product=inline-share-buttons" async="async"></script>
        <script src="/js/all.js"></script>
        <script src="https://www.google.com/recaptcha/api.js?render={{ env('GOOGLE_CAPTCHA_PUBLIC')}}"></script>

    </head>
    <body>
        <div id="fb-root"></div>
<script async defer crossorigin="anonymous" src="https://connect.facebook.net/es_LA/sdk.js#xfbml=1&version=v9.0&appId=340442469993643&autoLogAppEvents=1" nonce="8NzAZP9u"></script>
        <div class="site-wrapper">
            <header class="header petmark-header-1">
                <div class="header-wrapper">
                    <!-- Site Wrapper Starts -->
                    <div class="header-top bg-ash">
                        <div class="container">
                            <div class="row align-items-center">
                                <div class="col-sm-6 text-center text-sm-left">
                                    <p class="font-weight-300">Bienvenido a Petworld</p>
                                </div>
                                <div class="col-sm-6">
                                    <div class="header-top-nav right-nav">
                                        <div class="nav-item slide-down-wrapper" ng-show="shippingAddress">
                                            <span></span><a class="slide-down--btn" href="javascript:;" ng-click="changeShippingHeader()" role="button">
                                                @{{shippingAddress.address}}<i class="ion-ios-arrow-down"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="header-middle">
                        <div class="container">
                            <div class="row align-items-center justify-content-center">
                                <!-- Template Logo -->
                                <div class="col-lg-3 col-md-12 col-sm-4">
                                    <div class="site-brand  text-center text-lg-left">
                                        <a href="/" class="brand-image">
                                            <img src="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/main-logo2.png" alt="">
                                        </a>
                                    </div>
                                </div>
                                <!-- Category With Search -->
                                <div class="col-lg-5 col-md-7 order-3 order-md-2" ng-controller="SearchCtrl">
                                    <form class="category-widget" ng-submit="search()">
                                        <input type="text" name="search" ng-model="searchText" placeholder="Buscar">
                                        <div class="search-form__group search-form__group--select">
                                            <select name="category " id="searchCategory" ng-model="category" class="search-form__select nice-select">
                                                <option value="">Categoria</option>
                                                <optgroup label="Negocios">
                                                    <option value="merchants|0|list">Todos</option>
                                                    <option value="merchants|24|list">Veterinarias</option>
                                                    <option value="merchants|25|coverage">Tiendas</option>
                                                    <option value="merchants|27|list">Otros servicios</option>
                                                </optgroup>
                                                <optgroup label="Productos">
                                                    <option value="products|0|coverage">Productos Todos</option>
                                                    <option value="products|7|coverage">Perros</option>
                                                    <option value="products|8|coverage">Gatos</option>
                                                    <option value="products|5|coverage">Farmacia</option>
                                                </optgroup>
                                                <optgroup label="Publicaciones">
                                                    <option value="reports|0|list">Todos</option>
                                                    <option value="reports|11|list">Adopcion</option>
                                                    <option value="reports|12|list">Mascotas perdidas</option>
                                                    <option value="reports|13|list">Compra</option>
                                                </optgroup>
                                            </select>

                                        </div>
                                        <button class="search-submit"><i class="fas fa-search"></i></button>
                                    </form>
                                    <span style="color:red" ng-show="showError">
                                        <span>Porfavor Selecciona una categoría</span></span>
                                </div>
                                <!-- Call Login & Track of Order -->
                                <div class="col-lg-4 col-md-5 col-sm-8 order-2 order-md-3">
                                    <div class="header-widget-2 text-center text-sm-right ">
                                        <div class="call-widget">
                                            <p><a href="tel:3103418432">Llamanos: <i class="icon ion-ios-telephone"></i><span class="font-weight-mid">+57-310
                                                        341 8432</span></a></p>
                                        </div>
                                        <ul class="header-links">
                                            <li ng-hide="true"><a href="cart.html"><i class="fas fa-car-alt"></i> Track Your Order</a></li>
                                            <li ng-hide="user"><a href="/login"><i class="fas fa-user"></i> Ingresa</a></li>
                                            <li ng-show="user"><a href="{{ url('user/editProfile')}}"><i class="fas fa-user"></i> Hola @{{user.firstName}}  </a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="header-nav-wrapper" id="mobile-anchor" ng-controller="SitemapCtrl">
                    <div class="container">
                        <div class="header-bottom-inner">
                            <div class="row no-gutters">
                                <!-- Category Nav -->

                                <!-- Main Menu -->
                                <div class="col-lg-10 d-none d-lg-block">
                                    {!!$menu!!}
                                </div>
                                <!-- Cart block-->
                                @include(config("app.views").'.products.cart')
                                <!-- Mobile Menu -->
                                <div class="col-12 d-flex d-lg-none order-2 mobile-absolute-menu">
                                    <!-- Main Mobile Menu Start -->
                                    <div class="mobile-menu"></div>
                                    <!-- Main Mobile Menu End -->
                                </div>
                            </div>
                        </div>


                        <div class="row">

                        </div>
                    </div>
                    <div class="fixed-header sticky-init">
                        <div class="container">
                            <div class="row align-items-center">
                                <div class="col-lg-3">
                                    <!-- Sticky Logo Start -->
                                    <a class="sticky-logo" href="/">
                                        <img src="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/main-logo2.png" alt="logo">
                                    </a>
                                    <!-- Sticky Logo End -->
                                </div>
                                <div class="col-lg-9"> 
                                    <!-- Sticky Mainmenu Start -->
                                    {!!$sticky!!}
                                    <!-- Sticky Mainmenu End -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            @yield('content')
            <footer class="site-footer">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-4 col-md-6">
                            <div class="single-footer contact-block">
                                <h3 class="footer-title">Contacto</h3>
                                <div class="single-footer-content">
                                    <p class="text-italic">Somos una empresa de tecnologia que desarrolla productos innovadores para modernizar industrias.</p>
                                    <p class="font-weight-500 text-white"><span class="d-block">Contacto:</span>
                                        <a href="mailto:servicioalcliente@petworld.net.co">servicioalcliente@petworld.net.co</a></p>
                                    <p class="social-icons">
                                        <a href="https://www.facebook.com/petworldCol"><i class="fab fa-facebook-f"></i></a>
                                        <!--a href=""><i class="fab fa-twitter"></i></a-->
                                        <a href="https://www.instagram.com/petworld_colombia/"><i class="fab fa-instagram"></i></a>
                                        <a href="https://www.linkedin.com/company/pet-world-colombia/"><i class="fab fa-linkedin-in"></i></a>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6">
                            <div class="single-footer contact-block">
                                <h3 class="footer-title">Informacion</h3>
                                <div class="single-footer-content">
                                    <ul class="footer-list">
                                        <li><a href="/a/faq">Preguntas frecuentes</a></li>
                                        <!--li><a href="/a/about-us">Acerca de </a></li-->
                                        <li><a href="/a/contact-us/bla">Contactanos</a></li>
                                        <li><a href="/a/terms">Terminos y condiciones</a></li>
                                        <li><a href="/a/icons">Iconos</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="single-footer contact-block">
                                <div class="fb-page" data-href="https://www.facebook.com/petworldCol" data-tabs="timeline" data-width="" data-height="400" data-small-header="false" data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="true"><blockquote cite="https://www.facebook.com/petworldCol" class="fb-xfbml-parse-ignore"><a href="https://www.facebook.com/petworldCol">Pet World</a></blockquote></div>
                                <!--h3 class="footer-title">SUBSCRIBE TO OUR NEWSLETTER</h3>
                                <div class="single-footer-content">
                                    <p>
                                        Subscribe to the Petmark mailing list to receive updates on new arrivals, special offers and other discount information.
                                    </p>
                                    <div class="pt-2">
                                        <div class="input-box-with-icon">
                                            <input type="text" placeholder="Enter Your email">
                                            <button><i class="fas fa-envelope"></i></button>
                                        </div>
                                    </div>
                                </div--> 
                            </div>
                        </div>
                    </div>
                    <div class="footer-block-2 text-center">
                        <!--ul class="footer-list list-inline justify-content-center">
                            <li><a href="">Online Shopping</a></li>

                            <li><a href="">Promotions</a></li>

                            <li><a href=""> My Orders</a></li>

                            <li><a href="">Help</a></li>

                            <li><a href="">Customer Service</a></li>

                            <li><a href="">Support</a></li>

                            <li><a href=""> Most Populars</a></li>

                            <li><a href="">New Arrivals</a></li>

                            <li><a href="">Special Products</a></li>

                            <li><a href="">Manufacturers</a></li>

                            <li><a href="">Our Stores</a></li>
                        </ul>
                        <ul class="footer-list list-inline justify-content-center">
                            <li><a href="">Shipping</a></li>

                            <li><a href="">Payments</a></li>

                            <li><a href="">Warantee</a></li>

                            <li><a href="">Refunds</a></li>

                            <li><a href="">Checkout</a></li>

                            <li><a href="">Discount</a></li>

                            <li><a href="">Terms & Conditions</a></li>

                            <li><a href=""> Policy</a></li>

                            <li><a href="">Special Products</a></li>

                            <li><a href="">Manufacturers</a></li>

                            <li><a href="">Our Stores</a></li>
                        </ul-->
                        <div class="payment-block pt-3">
                            <img src="image/icon-logo/payment-icons.png" alt="">
                        </div>
                    </div>
                </div>
                <div class="footer-copyright">
                    <p>Copyright © 2020 <a href="#">Petworld.com</a>. All Rights Reserved</p>
                </div>
            </footer>
        </div>
        <script src="/js/custom.js"></script>
        <!-- Scripts --j>
        <script src="/js/app.js"></script>-->
    </body>
</html>
