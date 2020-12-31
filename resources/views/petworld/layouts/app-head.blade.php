<meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token()}}">
        <title>{{ config('app.name', 'Laravel')}}</title>

        <!-- Styles -->
        <link href="/css/app.css" rel="stylesheet">
        <link href="/css/style.css" rel="stylesheet">
        <link rel="stylesheet" href="/css/plugins.css" />
        <link rel="stylesheet" href="/css/main.css" />
        <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/angular_material/1.1.0/angular-material.min.css">
        <!-- Scripts 
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-sortablejs@latest/jquery-sortable.js"></script>-->

        <script src="/js/app.js"></script>
        <script src="/js/plugins.js"></script>

        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.0/angular.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.0/angular-cookies.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.0/angular-route.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/angular_material/1.1.0/angular-material.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.5/angular-animate.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.5/angular-aria.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/angular-google-maps/2.3.2/angular-google-maps.min.js"></script>
        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCOlc_3d8ygnNCMRzfEpmvSNsYtmbowtYo"></script>
        <script type="text/javascript" src="https://platform-api.sharethis.com/js/sharethis.js#property=5fa5a79fcc85000012ec2cee&product=inline-share-buttons" async="async"></script>
        <script src="/js/all.js"></script>
        <script src="https://www.google.com/recaptcha/api.js?render={{ env('GOOGLE_CAPTCHA_PUBLIC')}}"></script>
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-KBF630LLXE"></script>
        <script>
                    window.dataLayer = window.dataLayer || [];
                    function gtag(){dataLayer.push(arguments); }
                    gtag('js', new Date());
                    gtag('config', 'G-KBF630LLXE');
        </script>