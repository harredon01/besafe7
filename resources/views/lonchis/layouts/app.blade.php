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
        <!--script src="/js/all.js"></script-->
        <script src="{{ asset('/js/app_1.js')}}"></script>
        <script src="{{ asset('/js/constants.js')}}"></script>
        <script src="{{ asset('/js/controllers/userctrl.js')}}"></script>
        <script src="{{ asset('/js/controllers/mapctrl.js')}}"></script>
        <script src="{{ asset('/js/controllers/mapdashctrl.js')}}"></script>
        <script src="{{ asset('/js/controllers/productctrl.js')}}"></script>
        <script src="{{ asset('/js/controllers/cartctrl.js')}}"></script>
        <script src="{{ asset('/js/controllers/checkoutctrl.js')}}"></script>
        <script src="{{ asset('/js/controllers/accessctrl.js')}}"></script>
        <script src="{{ asset('/js/controllers/sourcesctrl.js')}}"></script>
        <script src="{{ asset('/js/controllers/sitemapctrl.js')}}"></script>
        <script src="{{ asset('/js/controllers/addressctrl.js')}}"></script>
        <script src="{{ asset('/js/controllers/routesctrl.js')}}"></script>
        <script src="{{ asset('/js/controllers/ordersctrl.js')}}"></script>
        <script src="{{ asset('/js/controllers/exportsctrl.js')}}"></script>
        <script src="{{ asset('/js/controllers/paymentsctrl.js')}}"></script>
        <script src="{{ asset('/js/controllers/groupsctrl.js')}}"></script>
        <script src="{{ asset('/js/controllers/menuctrl.js')}}"></script>
        <script src="{{ asset('/js/controllers/merchantsctrl.js')}}"></script>
        <script src="{{ asset('/js/controllers/reportsctrl.js')}}"></script>
        <script src="{{ asset('/js/controllers/leadsctrl.js')}}"></script>
        <script src="{{ asset('/js/controllers/deliveriesctrl.js')}}"></script>
        <script src="{{ asset('/js/controllers/admin/store/admin-store-productsctrl.js')}}"></script>
        <script src="{{ asset('/js/controllers/admin/store/admin-store-variantsctrl.js')}}"></script>
        <script src="{{ asset('/js/controllers/admin/store/admin-store-merchantsctrl.js')}}"></script>
        <script src="{{ asset('/js/controllers/admin/store/admin-store-categoriesctrl.js')}}"></script>
        <script src="{{ asset('/js/controllers/foodaddressesctrl.js')}}"></script>
        <script src="{{ asset('/js/controllers/foodmessagesctrl.js')}}"></script>
        <script src="{{ asset('/js/controllers/zonesctrl.js')}}"></script>
        <script src="{{ asset('/js/services/map.js')}}"></script>
        <script src="{{ asset('/js/services/merchants.js')}}"></script>
        <script src="{{ asset('/js/services/categories.js')}}"></script>
        <script src="{{ asset('/js/services/mapDash.js')}}"></script>
        <script src="{{ asset('/js/services/users.js')}}"></script>
        <script src="{{ asset('/js/services/location.js')}}"></script>
        <script src="{{ asset('/js/services/products.js')}}"></script>
        <script src="{{ asset('/js/services/cart.js')}}"></script>
        <script src="{{ asset('/js/services/modals.js')}}"></script>
        <script src="{{ asset('/js/services/billing.js')}}"></script>
        <script src="{{ asset('/js/services/passport.js')}}"></script>
        <script src="{{ asset('/js/services/groups.js')}}"></script>
        <script src="{{ asset('/js/services/address.js')}}"></script>
        <script src="{{ asset('/js/services/routes.js')}}"></script>
        <script src="{{ asset('/js/services/payments.js')}}"></script>
        <script src="{{ asset('/js/services/orders.js')}}"></script>
        <script src="{{ asset('/js/services/product-import.js')}}"></script>
        <script src="{{ asset('/js/services/food.js')}}"></script>
        <script src="{{ asset('/js/services/zones.js')}}"></script>
        <script src="{{ asset('/js/services/leads.js')}}"></script>

    </head>
    <body>
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
                                        <a href="index.html" class="brand-image">
                                            <img src="image/main-logo.png" alt="">
                                        </a>
                                    </div>
                                </div>
                                <!-- Category With Search -->
                                <div class="col-lg-5 col-md-7 order-3 order-md-2">
                                    <form class="category-widget">
                                        <input type="text" name="search" placeholder="Search products ">
                                        <div class="search-form__group search-form__group--select">
                                            <select name="category " id="searchCategory" class="search-form__select nice-select">
                                                <option value="all">All Categories</option>
                                                <optgroup label="Books, Magazines">
                                                    <option>Bedroom</option>
                                                    <option>Kitchen</option>
                                                    <option>Livingroom</option>
                                                </optgroup>
                                                <optgroup label="Electronics">
                                                    <option>Fridge</option>
                                                    <option>Laptops, Desktops</option>
                                                    <option>Mobiles, Tablets</option>
                                                </optgroup>
                                                <optgroup label="Furniture">
                                                    <option>Accessories</option>
                                                    <option>Men</option>
                                                    <option>Women</option>
                                                </optgroup>
                                                <option value="3">Home, Garden</option>
                                                <option value="3">Kids, Baby</option>
                                                <option value="3">Sport</option>
                                            </select>
                                        </div>
                                        <button class="search-submit"><i class="fas fa-search"></i></button>
                                    </form>
                                </div>
                                <!-- Call Login & Track of Order -->
                                <div class="col-lg-4 col-md-5 col-sm-8 order-2 order-md-3">
                                    <div class="header-widget-2 text-center text-sm-right ">
                                        <div class="call-widget">
                                            <p>CALL US NOW: <i class="icon ion-ios-telephone"></i><span class="font-weight-mid">+91-012
                                                    345 678</span></p>
                                        </div>
                                        <ul class="header-links">
                                            <li><a href="cart.html"><i class="fas fa-car-alt"></i> Track Your Order</a></li>
                                            <li ng-hide="user"><a href="/login"><i class="fas fa-user"></i> Ingresa</a></li>
                                            <li ng-show="user"><a href="{{ url('user/editProfile')}}"><i class="fas fa-user"></i> Hola @{{user.firstName}}  </a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="header-nav-wrapper">
                    <div class="container">
                        <div class="header-bottom-inner">
                            <div class="row no-gutters">
                                <!-- Category Nav -->
                                {!!$menu!!}
                                <!-- Main Menu -->
                                <div class="col-lg-7 d-none d-lg-block">
                                    <nav class="main-navigation">
                                        <!-- Mainmenu Start -->
                                        <ul class="mainmenu">
                                            <li class="mainmenu__item">
                                                <a href="/" class="mainmenu__link">Home</a>
                                            </li>
                                            <li class="mainmenu__item menu-item-has-children">
                                                <a href="#" class="mainmenu__link">Mi cuenta</a>
                                                <ul class="sub-menu">
                                                    <li>
                                                        <a href="{{ url('user/editProfile')}}">Mi cuenta</a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ url('user/editAddress')}}">Mis direcciones</a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ url('user/editPassword')}}">Actualizar Contraseña</a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ url('user/myPayments')}}">Mis Pagos</a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ url('/logout')}}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Cerrar session</a>
                                                        <form id="logout-form" action="{{ url('/logout')}}" method="POST" style="display: none;">
                                                            {{ csrf_field()}}
                                                        </form>
                                                    </li>
                                                </ul>
                                            </li>
                                            <li class="mainmenu__item menu-item-has-children">
                                                <a href="javascript" class="mainmenu__link">Acerca de</a>
                                                <ul class="sub-menu">
                                                    <li>
                                                        <a href="/a/about-us">Nosotros</a>
                                                    </li>
                                                    <li>
                                                        <a href="/a/faq">Preguntas Frecuentes</a>
                                                    </li>
                                                    <li>
                                                        <a href="/a/contact-us/bla">Contactanos</a>
                                                    </li>
                                                    <li>
                                                        <a href="/a/terms">Terminos y condiciones</a>
                                                    </li>
                                                </ul>
                                            </li>
                                            <li class="mainmenu__item menu-item-has-children ">
                                                <a href="blog.html" class="mainmenu__link">Blog</a>
                                                <ul class="sub-menu">
                                                    <li class="menu-item-has-children">
                                                        <a href="blog.html">Blog Gird</a>
                                                        <ul class="sub-menu">
                                                            <li><a href="blog-left-sidebar.html">Blog Left Sidebar</a></li>
                                                            <li><a href="blog-right-sidebar.html">Blog Right Sidebar</a></li>
                                                            <li><a href="blog.html">Blog Full Width</a></li>
                                                        </ul>
                                                    </li>
                                                    <li class="menu-item-has-children">
                                                        <a href="blog-list.html">Blog List</a>
                                                        <ul class="sub-menu">
                                                            <li><a href="blog-list-left-sidebar.html">Blog List Left Sidebar</a></li>
                                                            <li><a href="blog-list-right-sidebar.html">Blog List Right Sidebar</a></li>
                                                        </ul>
                                                    </li>
                                                    <li class="menu-item-has-children">
                                                        <a href="blog-details.html">Blog Details</a>
                                                        <ul class="sub-menu">
                                                            <li><a href="blog-details-left-sidebar.html">Left Sidebar</a></li>
                                                            <li><a href="blog-details.html">Image Format</a></li>
                                                            <li><a href="blog-details-video.html">Video Format</a></li>
                                                            <li><a href="blog-details-gallery.html">Gallery Format</a></li>
                                                            <li><a href="blog-details-audio.html">Audio Format</a></li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                            </li>
                                        </ul>
                                        <!-- Mainmenu End -->
                                    </nav>
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
                                    <a class="sticky-logo" href="index.html">
                                        <img src="image/main-logo.png" alt="logo">
                                    </a>
                                    <!-- Sticky Logo End -->
                                </div>
                                <div class="col-lg-9">
                                    <!-- Sticky Mainmenu Start -->
                                    <nav class="sticky-navigation">
                                        <ul class="mainmenu sticky-menu">
                                            <li class="mainmenu__item ">
                                                <a href="/" class="mainmenu__link">Home</a>
                                            </li>
                                            <li class="mainmenu__item menu-item-has-children sticky-has-child ">
                                                <a href="index.html" class="mainmenu__link">Mi cuenta</a>
                                                <ul class="sub-menu">
                                                    <li>
                                                        <a href="{{ url('user/editProfile')}}">Mi cuenta</a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ url('user/editAddress')}}">Mis direcciones</a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ url('user/editPassword')}}">Editar Contraseña</a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ url('user/myPayments')}}">Mis Pagos</a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ url('/logout')}}" onclick="event.preventDefault(); document.cookie = 'user_obj= ; expires = Thu, 01 Jan 1970 00:00:00 GMT'; document.getElementById('logout-form').submit();">Cerrar session</a>
                                                        <form id="logout-form" action="{{ url('/logout')}}" method="POST" style="display: none;">
                                                            {{ csrf_field()}}
                                                        </form>
                                                    </li>
                                                </ul>
                                            </li>
                                            <li class="mainmenu__item menu-item-has-children sticky-has-child ">
                                                <a href="javascript" class="mainmenu__link">Acerca de</a>
                                                <ul class="sub-menu">
                                                    <li>
                                                        <a href="/a/about-us">Nosotros</a>
                                                    </li>
                                                    <li>
                                                        <a href="/a/faq">Preguntas Frecuentes</a>
                                                    </li>
                                                    <li>
                                                        <a href="/a/contact-us/bla">Contactanos</a>
                                                    </li>
                                                    <li>
                                                        <a href="/a/terms">Terminos y condiciones</a>
                                                    </li>
                                                </ul>
                                            </li>
                                            <li class="mainmenu__item menu-item-has-children sticky-has-child ">
                                                <a href="blog.html" class="mainmenu__link">Blog</a>
                                                <ul class="sub-menu">
                                                    <li class="menu-item-has-children">
                                                        <a href="blog.html">Blog Gird</a>
                                                        <ul class="sub-menu left-align">
                                                            <li><a href="blog-left-sidebar.html">Blog Left Sidebar</a></li>
                                                            <li><a href="blog-right-sidebar.html">Blog Right Sidebar</a></li>
                                                            <li><a href="blog.html">Blog Full Width</a></li>
                                                        </ul>
                                                    </li>
                                                    <li class="menu-item-has-children">
                                                        <a href="blog-list.html">Blog List</a>
                                                        <ul class="sub-menu left-align">
                                                            <li><a href="blog-list-left-sidebar.html">Blog List Left Sidebar</a></li>
                                                            <li><a href="blog-list-right-sidebar.html">Blog List Right Sidebar</a></li>
                                                        </ul>
                                                    </li>
                                                    <li class="menu-item-has-children">
                                                        <a href="blog-details.html">Blog Details</a>
                                                        <ul class="sub-menu left-align">
                                                            <li><a href="blog-details-left-sidebar.html">Left Sidebar</a></li>
                                                            <li><a href="blog-details.html">Image Format</a></li>
                                                            <li><a href="blog-details-video.html">Video Format</a></li>
                                                            <li><a href="blog-details-gallery.html">Gallery Format</a></li>
                                                            <li><a href="blog-details-audio.html">Audio Format</a></li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                            </li>
                                        </ul>
                                        <div class="sticky-mobile-menu  d-lg-none">
                                            <span class="sticky-menu-btn"></span>
                                        </div>
                                    </nav>
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
                                    <p class="text-italic">We are a team of designers and developers that create high quality Wordpress, Magento, Prestashop, Opencart.</p>
                                    <p class="font-weight-500 text-white"><span class="d-block">Contact info:</span>
                                        169-C, Technohub, Dubai Silicon Oasis.</p>
                                    <p class="social-icons">
                                        <a href=""><i class="fab fa-facebook-f"></i></a>
                                        <a href=""><i class="fab fa-twitter"></i></a>
                                        <a href=""><i class="fab fa-instagram"></i></a>
                                        <a href=""><i class="fab fa-linkedin-in"></i></a>
                                        <a href=""><i class="fas fa-rss"></i></a>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6">
                            <div class="single-footer contact-block">
                                <h3 class="footer-title">Informacion</h3>
                                <div class="single-footer-content">
                                    <ul class="footer-list">
                                        <li><a href="/a/about-us">Acerca de </a></li>
                                        <li><a href="/a/contact-us/bla">Contactanos</a></li>
                                        <li><a href="#">Returns & Exchanges</a></li>
                                        <li><a href="#">Shipping & Delivery</a></li>
                                        <li><a href="/a/terms">Terminos y condiciones</a></li>
                                        <li><a href="/a/icons">Iconos</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="single-footer contact-block">
                                <h3 class="footer-title">SUBSCRIBE TO OUR NEWSLETTER</h3>
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
                                </div>
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
                    <p>Copyright © 2018 <a href="#">Petmark.com</a>. All Rights Reserved</p>
                </div>
            </footer>
        </div>
        <script src="/js/custom.js"></script>
        <!-- Scripts --j>
        <script src="/js/app.js"></script>-->
    </body>
</html>
