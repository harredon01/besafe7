<!DOCTYPE html>
<html lang="es">
    <head>
        <!--- Basic Page Needs  -->
        <meta charset="utf-8">
        <title>Lonchis App</title>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <!-- Mobile Specific Meta  -->
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <!-- CSS -->
        <link rel="stylesheet" href="assets/css/bootstrap.min.css">
        <link rel="stylesheet" href="assets/css/jquery-ui.css">
        <link rel="stylesheet" href="assets/css/font-awesome.min.css">
        <link rel="stylesheet" href="assets/css/owl.carousel.min.css">
        <link rel="stylesheet" href="assets/css/slicknav.min.css">
        <link rel="stylesheet" href="assets/css/magnificpopup.css">
        <link rel="stylesheet" href="assets/css/jquery.mb.YTPlayer.min.css">
        <link rel="stylesheet" href="assets/css/typography.css">
        <link rel="stylesheet" href="assets/css/style.css">
        <link rel="stylesheet" href="assets/css/responsive.css">
        <!-- Favicon -->
        <link rel="shortcut icon" type="image/png" href="assets/img/icon/favicon.ico">
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
            <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
            <![endif]-->
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=UA-139655421-1"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag() {
                dataLayer.push(arguments);
            }
            gtag('js', new Date());

            gtag('config', 'UA-139655421-1');
        </script>

    </head>
    <body>
        <!-- preloader area start -->
        <div id="preloader">
            <div class="spinner"></div>
        </div>
        <!-- preloader area end -->
        <!-- header area start -->
        <header id="header">
            <div class="header-area">
                <div class="container">
                    <div class="row">
                        <div class="menu-area">
                            <div class="col-md-3 col-sm-12 col-xs-12">
                                <div class="logo">
                                    <a href="/"><img src="assets/img/icon/logo_2.png" alt="Zeedapp - App Landing Template"></a>
                                </div>
                            </div>
                            <div class="col-md-9 hidden-xs hidden-sm">
                                <div class="main-menu">
                                    <nav class="nav-menu">
                                        <ul>
                                            <li class="active"><a href="#home">Home</a></li>
                                            <li><a href="#feature">Beneficios</a></li>
                                            <li><a href="#screenshot">Pantallazos</a></li>
                                            <li><a href="#pricing">Precios</a></li>
                                            <li style="display: none"><a href="#team">Team</a></li>
                                            <li><a href="#download">Descárgalo</a></li>
                                            <li style="display: none"><a href="#blog">Blog</a></li>
                                            <li><a href="#contact">Contáctanos</a></li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                            <div class="col-sm-12 col-xs-12 visible-sm visible-xs">
                                <div class="mobile_menu"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <!-- header area end -->

        @yield('content')


        <footer>
            <div class="footer-area">
                <div class="container">
                    <p><!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
                        Copyright &copy;<script>document.write(new Date().getFullYear());</script> All rights reserved | This template is made with <i class="fa fa-heart-o" aria-hidden="true"></i> by <a href="https://colorlib.com" target="_blank">Colorlib</a>
                        <!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. --></p>
                    <p><!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
                        Acerca de nuestros <a href="/icons" target="_blank">íconos</a>
                        <!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. --></p>
                </div>
            </div>
        </footer>
        <!-- footer area end -->

        <!-- Scripts -->
        <script src="assets/js/jquery-3.2.0.min.js"></script>
        <script src="assets/js/jquery-ui.js"></script>
        <script src="assets/js/bootstrap.min.js"></script>
        <script src="assets/js/jquery.slicknav.min.js"></script>
        <script src="assets/js/owl.carousel.min.js"></script>
        <script src="assets/js/magnific-popup.min.js"></script>
        <script src="assets/js/counterup.js"></script>
        <script src="assets/js/jquery.waypoints.min.js"></script>
        <script src="assets/js/jquery.mb.YTPlayer.min.js"></script>
        <script src="assets/js/theme.js"></script>
    </body>
</html>
