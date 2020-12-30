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
        <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/angular_material/1.1.0/angular-material.min.css">
        <!-- Scripts -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-sortablejs@latest/jquery-sortable.js"></script>
        
        <script src="/js/app.js"></script>
        
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.0/angular.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.0/angular-cookies.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.0/angular-route.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/angular_material/1.1.0/angular-material.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.5/angular-animate.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.5/angular-aria.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/angular-google-maps/2.3.2/angular-google-maps.min.js"></script>
        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCOlc_3d8ygnNCMRzfEpmvSNsYtmbowtYo"></script>
        <script src="/js/all.js"></script>

        <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  
    </head>
    <body>
        <div id="app">
            <nav class="navbar navbar-default navbar-static-top">
                <div class="container">
                    <div class="navbar-header">

                        <!-- Collapsed Hamburger -->
                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                            <span class="sr-only">Toggle Navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>

                        <!-- Branding Image -->
                        <a class="navbar-brand" href="{{ url('/')}}">
                            {{ config('app.name', 'Laravel')}}
                        </a>
                    </div>

                    <div class="collapse navbar-collapse" id="app-navbar-collapse">
                        <!-- Left Side Of Navbar -->
                        <ul class="nav navbar-nav">
                            &nbsp;
                        </ul>

                        <!-- Right Side Of Navbar -->
                        <ul class="nav navbar-nav navbar-right">
                            <!-- Authentication Links -->
                            @if (Auth::guest())
                            <li><a href="{{ url('/login') }}">Login</a></li>
                            <li><a href="{{ url('/register') }}">Register</a></li>
                            @else
                                @if (Auth::user()->id == 2 || Auth::user()->id == 77 )
                                <li><a href="{{ url('products/planes/1')}}">Planes</a></li>
                                <li><a href="{{ url('products/para-servir/1')}}">Para Servir</a></li>
                                <li><a href="{{ url('products/catering/1')}}">Catering</a></li>
                                <li><a href="{{ url('/checkout')}}">Checkout</a></li>
                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                        {{ Auth::user()->name }} <span class="caret"></span>
                                    </a>

                                    <ul class="dropdown-menu" role="menu">
                                        <li>
                                            <a href="{{ url('/logout')}}"
                                               onclick="event.preventDefault();
                                                       document.getElementById('logout-form').submit();">
                                                Logout
                                            </a>

                                            <form id="logout-form" action="{{ url('/logout')}}" method="POST" style="display: none;">
                                                {{ csrf_field()}}
                                            </form>
                                        </li>
                                        <li><a href="{{ url('user/editProfile')}}">Edit Profile</a></li>
                                        <li><a href="{{ url('user/editAddress')}}">Edit Addresses</a></li>
                                        <li><a href="{{ url('sources')}}">Edit Sources</a></li>
                                        <li><a href="{{ url('subscriptions')}}">Edit Subscriptions</a></li>
                                        <li><a href="{{ url('user/editAccess')}}">Edit Access</a></li>
                                    </ul>
                                </li>
                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                        Lonchis <span class="caret"></span>
                                    </a>
                                    <ul class="dropdown-menu" role="menu">
                                        <li><a href="{{ url('food/menu')}}">Menu</a></li>
                                        <li><a href="{{ url('admin/zones')}}">Zonas</a></li>
                                        <li><a href="{{ url('food/messages')}}">Mensajes</a></li>
                                        <li><a href="{{ url('food/routes')}}">Rutas</a></li>
                                        <li><a href="{{ url('food/deliveries')}}">Entregas</a></li>
                                        <li><a href="{{ url('food/largest_addresses')}}">Direcciones Comunes</a></li>
                                        
                                    </ul>
                                </li>
                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                        Store <span class="caret"></span>
                                    </a>
                                    <ul class="dropdown-menu" role="menu">
                                        <li><a href="{{ url('admin/store/products')}}">Productos</a></li>
                                        <li><a href="{{ url('admin/store/variants')}}">Variants</a></li>
                                        <li><a href="{{ url('admin/store/merchants')}}">Merchants</a></li>
                                        <li><a href="{{ url('admin/store/prod_categories')}}">Categories</a></li>
                                        <li><a href="{{ url('billing/orders')}}">Ordenes</a></li>
                                        <li><a href="{{ url('billing/payments')}}">Pagos</a></li>
                                    </ul>
                                </li>
                                @else
                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                        {{ Auth::user()->name }} <span class="caret"></span>
                                    </a>

                                    <ul class="dropdown-menu" role="menu">
                                        <li>
                                            <a href="{{ url('/logout')}}"
                                               onclick="event.preventDefault();
                                                       document.getElementById('logout-form').submit();">
                                                Logout
                                            </a>

                                            <form id="logout-form" action="{{ url('/logout')}}" method="POST" style="display: none;">
                                                {{ csrf_field()}}
                                            </form>
                                        </li>
                                    </ul>
                                </li>
                                @endif
                            
                            @endif
                        </ul>
                    </div>
                </div>
            </nav>

            @yield('content')
        </div>

        <!-- Scripts --j>
        <script src="/js/app.js"></script>-->
    </body>
</html>
