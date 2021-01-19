<!DOCTYPE html>
<html lang="en" ng-app="besafe" ng-strict-di>
    <head>
        @include(config("app.views").'.layouts.app-head')
    </head>
    <body id="main-body" ng-cloak>
        <div id="fb-root"></div>

        <div class="site-wrapper">
            @include(config("app.views").'.layouts.app-header')

            @yield('content')
            @include(config("app.views").'.layouts.app-footer')
        </div>
        <div id="overlay" ng-show="loader">
    <div class="spinner"></div>
    <br/>
    Cargando...
</div>

<!-- <script async defer crossorigin="anonymous" src="https://connect.facebook.net/es_LA/sdk.js#xfbml=1&version=v9.0&appId=340442469993643&autoLogAppEvents=1" nonce="8NzAZP9u"></script>
Scripts --j>
<script src="/js/app.js"></script>-->
    </body>
</html>
