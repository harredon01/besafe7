<!DOCTYPE html>
<html lang="en" ng-app="besafe">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token()}}">

        <title>{{ config('app.name', 'Laravel')}}</title>

        <!-- Styles -->
        <link href="/css/app.css" rel="stylesheet">
        <link href="/css/style.css" rel="stylesheet">

        <!-- Scripts -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
        <script src="/js/app.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.0/angular.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.0/angular-route.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/angular-google-maps/2.3.2/angular-google-maps.min.js"></script>
        <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
        <script src="{{ asset('/js/app_1.js')}}"></script>
        <script src="{{ asset('/js/constants.js')}}"></script>
        <script src="{{ asset('/js/controllers/userctrl.js')}}"></script>
        <script src="{{ asset('/js/controllers/mapctrl.js')}}"></script>
        <script src="{{ asset('/js/controllers/mapdashctrl.js')}}"></script>
        <script src="{{ asset('/js/controllers/productctrl.js')}}"></script>
        <script src="{{ asset('/js/controllers/checkoutctrl.js')}}"></script>
        <script src="{{ asset('/js/controllers/accessctrl.js')}}"></script>
        <script src="{{ asset('/js/controllers/sourcesctrl.js')}}"></script>
        <script src="{{ asset('/js/controllers/addressctrl.js')}}"></script>
        <script src="{{ asset('/js/controllers/groupsctrl.js')}}"></script>
        <script src="{{ asset('/js/services/map.js')}}"></script>
        <script src="{{ asset('/js/services/mapDash.js')}}"></script>
        <script src="{{ asset('/js/services/users.js')}}"></script>
        <script src="{{ asset('/js/services/location.js')}}"></script>
        <script src="{{ asset('/js/services/products.js')}}"></script>
        <script src="{{ asset('/js/services/checkout.js')}}"></script>
        <script src="{{ asset('/js/services/payu.js')}}"></script>
        <script src="{{ asset('/js/services/billing.js')}}"></script>
        <script src="{{ asset('/js/services/passport.js')}}"></script>
        <script src="{{ asset('/js/services/groups.js')}}"></script>
        <script src="{{ asset('/js/services/address.js')}}"></script>

        <script>
            window.Laravel = <?php
echo json_encode([
    'csrfToken' => csrf_token(),
]);
?>;
        </script>
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
                            <li><a href="{{ url('products')}}">Products</a></li>
                            <li><a href="{{ url('plans')}}">Plans</a></li>
                            <li class="dropdown replace-header-cart" ng-controller="CartCtrl">
                                @include('products.cart')
                            </li>
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
