<header class="header petmark-header-1">
                <div class="header-wrapper">
                    <!-- Site Wrapper Starts -->
                    <div class="header-top bg-ash">
                        <div class="container">
                            <div class="row align-items-center">
                                <div class="col-sm-6 text-center text-sm-left">
                                    <p style="color: white" class="font-weight-300">Todo para tu Mascota</p>
                                </div>
                                <div class="col-sm-6">
                                    <div class="header-top-nav right-nav">
                                        <div class="nav-item slide-down-wrapper" ng-show="shippingAddress">
                                            <span></span><a class="slide-down--btn" style="color: white" href="javascript:;" ng-click="changeShippingHeader()" role="button">
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
                                            <img src="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/main-logo2.webp" alt="">
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
                                <div class="col-lg-2">
                                    <!-- Sticky Logo Start -->
                                    <a class="sticky-logo" href="/">
                                        <img src="https://gohife.s3.us-east-2.amazonaws.com/petworld/home/main-logo2.webp" alt="logo">
                                    </a>
                                    <!-- Sticky Logo End -->
                                </div>
                                <div class="col-lg-10"> 
                                    <!-- Sticky Mainmenu Start -->
                                    {!!$sticky!!}
                                    <!-- Sticky Mainmenu End -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>