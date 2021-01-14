<meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token()}}">
        <title>@yield('title','Petworld un mundo de mascotas')</title>
        <meta name="keywords" content="@yield('meta_keywords','perro,gato,comida,veterinario,mascota,alimento,concentrado')">
        <meta name="description" content="@yield('meta_description','Encuentra todo lo que necesitas para tu mascota con petworld. Desde veterinarios y tiendas de mascotas hasta guarderías, fundaciones y mucho más')">
        <link rel="canonical" href="{{url()->current()}}"/>

        <!-- Styles 
        <link href="/css/app.css" rel="stylesheet" async>-->
        <link rel="preload" as="font" href="/fonts/fa-brands-400.woff2" type="font/woff2" crossorigin="anonymous">
        <link rel="preload" as="font" href="/fonts/fa-solid-900.woff2" type="font/woff2" crossorigin="anonymous">
        <link rel="preload" as="font" href="/fonts/Ionicons.ttf?v=2.0.0" type="font/ttf" crossorigin="anonymous">
        <link rel="preload" as="font" href="/fonts/themify.woff?-fvbane" type="font/woff" crossorigin="anonymous">
        <link rel="preload" as="font" href="/fonts/fa-brands-400.woff2" type="font/woff2" crossorigin="anonymous">
        <link rel="preload" as="font" href="https://fonts.gstatic.com/s/rubik/v11/iJWKBXyIfDnIV7nBrXw.woff2" type="font/woff2" crossorigin="anonymous">
        <link rel="preload" as="font" href="https://fonts.gstatic.com/s/rubik/v11/iJWEBXyIfDnIV7nEnX661A.woff2" type="font/woff2" crossorigin="anonymous">
        <link rel="preload" as="font" href="https://fonts.gstatic.com/s/roboto/v18/KFOmCnqEu92Fr1Mu4mxK.woff2" type="font/woff2" crossorigin="anonymous">
        <link rel="preload" as="font" href="https://fonts.gstatic.com/s/roboto/v18/KFOlCnqEu92Fr1MmEU9fBBc4.woff2" type="font/woff2" crossorigin="anonymous">
        <link rel="preload" href="/css/app.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
        <link href="/css/style.css" rel="preload" as="style" onload="this.onload=null;this.rel='stylesheet'">
        <link href="/css/plugins.css" rel="preload" as="style" onload="this.onload=null;this.rel='stylesheet'">
        <link rel="preload" href="/css/main.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
        <link rel="preload" href="https://ajax.googleapis.com/ajax/libs/angular_material/1.1.0/angular-material.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
        <noscript>
            <link rel="stylesheet" href="/css/app.css?v=1.0.0.0">
            <link href="/css/style.css?v=1.0.0.0" rel="stylesheet">
            <link rel="stylesheet" href="/css/plugins.css?v=1.0.0.0" />
            <link rel="stylesheet" href="/css/main.css?v=1.0.0.0" />
            <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/angular_material/1.1.0/angular-material.min.css">
        </noscript>
        
        
        <script src="/js/app.js?v=1.0.0.1"></script>
        
        <!-- Scripts 
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-sortablejs@latest/jquery-sortable.js"></script>-->

        
        
        
        <!--script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.0/angular.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/angular_material/1.1.0/angular-material.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.0/angular-cookies.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.0/angular-route.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.0/angular-animate.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.0/angular-aria.min.js"></script-->
        
        
        
        
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-KBF630LLXE"></script>
        <script>
                    window.dataLayer = window.dataLayer || [];
                    function gtag(){dataLayer.push(arguments); }
                    gtag('js', new Date());
                    gtag('config', 'G-KBF630LLXE');
                    gtag('config', 'UA-185676583-1');
        </script>