<!--header area start-->
<div >
    <!--offcanvas menu area start-->
    <div class="off_canvars_overlay">

    </div>
    <div class="offcanvas_menu">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="canvas_open">
                        <a href="javascript:void(0)"><i class="icon-menu"></i></a>
                    </div>
                    <div class="offcanvas_menu_wrapper">
                        <div class="canvas_close">
                            <a href="javascript:void(0)"><i class="icon-x"></i></a>  
                        </div>
                        <div class="language_currency" ng-controller="SitemapCtrl">
                            <ul>
                                <li class="language" ng-show="shippingAddress"><a href="javascript:;" ng-click="changeShippingHeader()" > @{{shippingAddress.address}} <i class="icon-right ion-ios-arrow-down"></i></a>

                                </li>
                                <li class="language">Bienvenido!
                                </li>
                            </ul>
                        </div>
                        <div class="header_social text-right">
                            <ul>
                                <li><a href="https://www.facebook.com/lonchisapp"><i class="ion-social-facebook"></i></a></li>
                                <li><a href="https://www.instagram.com/lonchisapp_col/"><i class="ion-social-instagram-outline"></i></a></li>
                            </ul>
                        </div>


                        <div class="call-support">
                            <p><a href="tel:+573103418432">(310) 341 8432</a> Contáctanos</p>
                        </div>
                        <div id="menu" class="text-left ">
                            <ul class="offcanvas_main_menu">
                                <li class="menu-item-has-children">
                                    <a href="/a/planes">Planes</a>
                                </li>
                                <li class="menu-item-has-children">
                                    <a href="/a/faq">FAQ's</a>
                                </li>
                                <li class="menu-item-has-children">
                                    <a href="/a/zones">Cobertura</a>
                                </li>
                                <li class="menu-item-has-children">
                                    <a href="/a/contact-us/bla"> Contact Us</a> 
                                </li>
                            </ul>
                        </div>
                        <div class="offcanvas_footer">
                            <span><a href="mailto:servicioalcliente@lonchis.com.co"><i class="fa fa-envelope-o"></i> servicioalcliente@lonchis.com.co</a></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--offcanvas menu area end-->

    <header>
        <div class="main_header">
            <div class="header_top">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-6 col-md-6">
                            <div class="language_currency" ng-controller="SitemapCtrl">
                                <ul>
                                    <li class="language" ng-show="shippingAddress"><a href="javascript:;" ng-click="changeShippingHeader()"> @{{shippingAddress.address}} <i class="icon-right ion-ios-arrow-down"></i></a>
                                    </li>
                                    <li class="currency" ng-hide="shippingAddress">Bienvenido!
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="header_social text-right">
                                <ul>
                                    <li><a href="https://www.facebook.com/lonchisapp"><i class="ion-social-facebook"></i></a></li>
                                    <li><a href="https://www.instagram.com/lonchisapp_col/"><i class="ion-social-instagram-outline"></i></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="header_middle header_middle5">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-3 col-md-3 col-sm-3 col-3">
                            <div class="logo">
                                <a href="/"><img src="/fitmeal/images/logotranparenteazul300.82.png" alt=""></a>
                            </div>
                        </div>
                        <div class="col-lg-5 col_search5">
                        </div>
                        <div class="col-lg-4 col-md-6 col-sm-7 col-8">
                            <div class="header_account_area">
                                <div class="header_account_list register">
                                    <ul>
                                        <li ng-show="user"><a href="{{ url('user/editProfile')}}"><span class="lnr lnr-user"></span> Hola @{{user.firstName}}  </a></li>
                                        <li ng-hide="user"><a href="/login">Ingresa</a></li>
                                    </ul>
                                </div>
                                @include(config("app.views").'.products.cart')
                            </div>
                        </div>
                    </div> 
                </div>
            </div>
            <div class="header_bottom sticky-header">
                <div class="container">  
                    <div class="row align-items-center">
                        <div class="col-12 col-md-6 col-sm-6 mobail_s_block">

                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-6">
                            <div class="categories_menu">
                                <div class="categories_title">
                                    <h2 class="categori_toggle">Catering</h2>
                                </div>
                                <div class="categories_menu_toggle">
                                    <ul>
                                        <li class="show-responsive"><a href="/a/planes"> Planes de Almuerzo</a></li>
                                        <li><a href="/a/products/cenas-y-almuerzos?merchant_id=1300"> Cenas y almuerzos</a></li>
                                        <li><a href="/a/products/pasabocas?merchant_id=1300"> Pasabocas</a></li>
                                        <li><a href="/a/products/desayunos?merchant_id=1300">Desayunos</a></li>
                                        <li><a href="/a/products/refrigerios?merchant_id=1300">Refrigerios</a></li>
                                        <li><a href="/a/products/servicios?merchant_id=1300"> Servicios</a></li>
                                        <li><a href="/a/products/bebidas?merchant_id=1300"> Bebidas</a></li>
                                        <li><a href="/a/products/postres?merchant_id=1300"> Postres</a></li>
                                        <li><a href="/a/products/estacion-de-cafe?merchant_id=1300"> Estación de cafe </a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <!--main menu start-->
                            <div class="main_menu menu_position"> 
                                <nav>  
                                    <ul>
                                        <li>
                                            <a class="active"  href="/a/planes">Planes</a>
                                        </li>
                                        <li><a href="/a/zones">Cobertura</a></li>
                                        <li><a href="/a/faq"> FAQ's</a></li>
                                        <li><a href="/a/contact-us/bla">Contáctanos</a></li>
                                    </ul>  
                                </nav> 
                            </div>
                            <!--main menu end-->
                        </div>
                        <div class="col-lg-3">
                            <div class="call-support">
                                <p><a href="tel:+573103418432">(310) 341-8432</a> Contactanos</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> 
    </header>
    <!--header area end-->
</div>
