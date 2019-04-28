@extends('layouts.zeeapp')

@section('content')
<!-- slider area start -->
<section id="home" class="slider-area image-background parallax" data-speed="3" data-bg-image="assets/img/bg/opcion2home.jpg">
    <div class="container">
        <div class="col-md-6 col-sm-6 hidden-xs">
            <div class="row">
                <div class="slider-img">
                    <img src="assets/img/mobile/slider-left-img.png" alt="slider image">
                </div>
            </div>
        </div>
        <div class="col-md-6 col-sm-6 col-xs-12">
            <div class="row">
                <div class="slider-inner text-right"><br/>
                    <h2>Fresco, delicioso, <br/>práctico y económico</h2>
                    <a class="expand-video" style="display:none" href="https://www.youtube.com/watch?v=8qs2dZO6wcc"><i class="fa fa-play"></i>Watch the video</a>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- slider area end -->
<!-- service area start -->
<div class="service-area">
    <div class="container">
        <div class="row">
            <div class="col-md-4 col-sm-4 col-xs-12">
                <div class="service-single">
                    <img src="assets/img/icon/plan-almuerzos.svg" alt="service image">
                    <h2>Planes de almuerzos</h2>
                </div>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12 col-6">
                <div class="service-single">
                    <img src="assets/img/icon/catering-eventos.svg" alt="service image">
                    <h2>Catering para eventos</h2>
                </div>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12 col-6">
                <div class="service-single">
                    <img src="assets/img/icon/listo.svg" alt="service image">
                    <h2>Listos para servir</h2>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- service area end -->
<!-- about area start -->
<div class="about-area ptb--100">
    <div class="container">
        <div class="section-title">
            <h2>Nosotros</h2>
        </div>
        <div class="row d-flex flex-center">
            <div class="col-md-6 col-sm-6 hidden-xs">
                <div class="about-left-img">
                    <img src="assets/img/about/nosotros.png" alt="image">
                </div>
            </div>

            <div class="col-md-6 col-sm-6 col-xs-12 d-flex flex-center">
                <div class="about-content">
                    <p>Ofrecemos 3 servicios de alimentos y bebidas que se adaptan a tus necesidades y presupuesto.</p>
                    <ol>
                        <li>
                            <p>Almuerzos a domicilios que entregamos en la puerta de tu casa  u oficina para que puedas comer sano, rico y variado mientras cuidas tu bolsillo</p><br/>
                        </li>
                        <li>
                            <p>Desayunos, almuerzos, cenas, pasabocas  y refrigerios para eventos con servicio de meseros para calentar, servir y atender a tus invitados.</p><br/>
                        </li>
                        <li>
                            <p>Platos recién preparados por el chef  que entregamos en la puerta de tu casa u oficina listos para servir.</p><br/>
                        </li>
                    </ol>                  
                    
                </div>
            </div>
        </div>
    </div>
</div>
<!-- about area end -->
<!-- feature area start -->
<section class="feature-area bg-gray ptb--100" id="feature">
    <div class="container">
        <div class="section-title">
            <h2>Beneficios</h2>
        </div>
        <div class="row">
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div class="ft-content rtl">
                    <div class="ft-single">
                        <img src="assets/img/icon/features-ecofriendly.svg" alt="icon">
                        <div class="meta-content">
                            <h2>¡Eco- Friendly!</h2>
                            <p>Usamos recipientes retornables o  de material reciclable que nos ayudan a cuidar nuestro planeta.</p>
                        </div>
                    </div>
                    <div class="ft-single">
                        <img src="assets/img/icon/feature-economic.svg" alt="icon">
                        <div class="meta-content">
                            <h2>Mayor economía</h2>
                            <p>Planes para todos los gustos y presupuestos que te ayudan a cuidar tu bolsillo, mientras disfrutas de una comida y un servicio de calidad.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 hidden-sm col-xs-12">
                <div class="ft-screen-img">
                    <img src="assets/img/icon/feature/features.png" alt="image">
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div class="ft-content">
                    <div class="ft-single">
                        <img src="assets/img/icon/feature-facil.svg" alt="icon">
                        <div class="meta-content">
                            <h2>Fácil y práctico</h2>
                            <p>Con <i>Lonchis app</i> puedes solicitar cualquiera de los servicios en menos de 10 minutos, ¡y sin salir de casa!</p>
                        </div>
                    </div>
                    <div class="ft-single">
                        <img src="assets/img/icon/Feature-payment.svg" alt="icon">
                        <div class="meta-content">
                            <h2>Variedad y seguridad en métodos de pago</h2>
                            <p>Contamos con 4 métodos seguros de pago para la facilidad y comodidad de todos.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- feature area end -->
<!-- achivement area start -->
<div class="achivement-area ptb--100">
    <div class="container">
        <div class="row">
            <div class="col-md-3 col-sm-3 col-xs-12">
                <div class="ach-single">
                    <div class="icon"><i class="fa fa-coffee"></i></div>
                    <p><span class="counter">8</span></p>
                    <h5>Años de experiencia</h5>
                </div>
            </div>
            <div class="col-md-3 col-sm-3 col-xs-12">
                <div class="ach-single">
                    <div class="icon"><i class="fa fa-book"></i></div>
                    <p>+<span class="counter">100</span></p>
                    <h5>Opciones de menú</h5>
                </div>
            </div>
            <div class="col-md-3 col-sm-3 col-xs-12">
                <div class="ach-single">
                    <div class="icon"><i class="fa fa-users"></i></div>
                    <p><span class="counter">18</span></p>
                    <h5>Planes activos</h5>
                </div>
            </div>
            <div class="col-md-3 col-sm-3 col-xs-12">
                <div class="ach-single">
                    <div class="icon"><i class="fa fa-trophy"></i></div>
                    <p>+<span class="counter">500</span></p>
                    <h5>Eventos atendidos</h5>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- achivement area end -->
<!-- screen slider area start -->
<section class="screen-area bg-gray ptb--100" id="screenshot">
    <div class="container">
        <div class="section-title">
            <h2>Screenshot</h2>
            <p>Nemo enim ipsam voluptatem quia voluptas sit</p>
        </div>
        <img class="screen-img" src="assets/img/mobile/screen-slider.png" alt="mobile screen">
        <div class="screen-slider owl-carousel">
            <div class="single-screen">
                <img src="assets/img/mobile/screen-slider/lonchis1.jpeg" alt="mobile screen">
            </div>
            <div class="single-screen">
                <img src="assets/img/mobile/screen-slider/lonchis2.jpeg" alt="mobile screen">
            </div>
            <div class="single-screen">
                <img src="assets/img/mobile/screen-slider/lonchis3.jpeg" alt="mobile screen">
            </div>
            <div class="single-screen">
                <img src="assets/img/mobile/screen-slider/lonchis4.jpeg" alt="mobile screen">
            </div>
            <div class="single-screen">
                <img src="assets/img/mobile/screen-slider/lonchis3.jpeg" alt="mobile screen">
            </div>
            <div class="single-screen">
                <img src="assets/img/mobile/screen-slider/lonchis2.jpeg" alt="mobile screen">
            </div>
            <div class="single-screen">
                <img src="assets/img/mobile/screen-slider/lonchis1.jpeg" alt="mobile screen">
            </div>
        </div>
    </div>
</section>
<!-- screen slider area end -->
<!-- testimonial carousel area start -->
<div class="testimonial-area ptb--100" style="display: none">
    <div class="container">
        <div class="section-title">
            <h2>Client Says</h2>
            <p>Nemo enim ipsam voluptatem quia voluptas sit</p>
        </div>
        <div class="testimonial-slider owl-carousel">
            <div class="single-tst">
                <img src="assets/img/author/tst-author1.jpg" alt="author">
                <h4>John Doe</h4>
                <span>Founder</span>
                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis</p>
                <ul class="tst-social">
                    <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                    <li><a href="#"><i class="fa fa-twitter"></i></a></li>
                    <li><a href="#"><i class="fa fa-linkedin"></i></a></li>
                    <li><a href="#"><i class="fa fa-dribbble"></i></a></li>
                    <li><a href="#"><i class="fa fa-ponterest"></i></a></li>
                </ul>
            </div>
            <div class="single-tst">
                <img src="assets/img/author/tst-author2.jpg" alt="author">
                <h4>John Doe</h4>
                <span>CEO</span>
                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis</p>
                <ul class="tst-social">
                    <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                    <li><a href="#"><i class="fa fa-twitter"></i></a></li>
                    <li><a href="#"><i class="fa fa-linkedin"></i></a></li>
                    <li><a href="#"><i class="fa fa-dribbble"></i></a></li>
                    <li><a href="#"><i class="fa fa-ponterest"></i></a></li>
                </ul>
            </div>
            <div class="single-tst">
                <img src="assets/img/author/tst-author1.jpg" alt="author">
                <h4>John Doe</h4>
                <span>CEO</span>
                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis</p>
                <ul class="tst-social">
                    <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                    <li><a href="#"><i class="fa fa-twitter"></i></a></li>
                    <li><a href="#"><i class="fa fa-linkedin"></i></a></li>
                    <li><a href="#"><i class="fa fa-dribbble"></i></a></li>
                    <li><a href="#"><i class="fa fa-ponterest"></i></a></li>
                </ul>
            </div>
            <div class="single-tst">
                <img src="assets/img/author/tst-author2.jpg" alt="author">
                <h4>John Doe</h4>
                <span>CEO</span>
                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis</p>
                <ul class="tst-social">
                    <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                    <li><a href="#"><i class="fa fa-twitter"></i></a></li>
                    <li><a href="#"><i class="fa fa-linkedin"></i></a></li>
                    <li><a href="#"><i class="fa fa-dribbble"></i></a></li>
                    <li><a href="#"><i class="fa fa-ponterest"></i></a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- testimonial carousel area end -->
<!-- video area start -->
<div class="video-area ptb--100" style="display: none">
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1 col-xs-12">
                <h2>Watch Video Demo</h2>
                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco</p>
                <a class="expand-video" href="https://www.youtube.com/watch?v=8qs2dZO6wcc"><i class="fa fa-play"></i></a>
            </div>
        </div>
    </div>
</div>
<!-- video area end -->
<!-- pricing area start -->
<section class="pricing-area ptb--100" id="pricing">
    <div class="container">
        <div class="section-title">
            <h2>Planes Envase Retornable</h2>
            <p>La personalización de los planes se puede hacer con la cantidad de personas y N° de almuerzos que desee, los datos anunciados a continuación son cantidades y valores intermedios de cada uno de los planes.</p>
        </div>
        <div class="row">
            <div class="col-md-4 col-sm-4 col-xs-12 col-6">
                <div class="single-price">
                    <div class="prc-head">
                        <span>Personal</span>
                        <h5><small>$</small>14.200c/u</h5>
                    </div>
                    <ul>
                        <li>1 Persona</li>
                        <li>11 Almuerzos</li>
                        <li>$156.200</li>
                        <li>[ENTRADA + PLATO FUERTE + DOMICILIO]</li>
                    </ul>
                    <a style="display: none" href="#">Order Now</a>
                </div>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12 col-6">
                <div class="single-price">
                    <div class="prc-head">
                        <span>3 Amigos</span>
                        <h5><small>$</small>12.100c/u</h5>
                    </div>
                    <ul>
                        <li>3 Personas</li>
                        <li>11 Almuerzos/p</li>
                        <li>33 Almuerzos</li>
                        <li>$399.300</li>
                        <li>[ENTRADA + PLATO FUERTE + DOMICILIO]</li>
                    </ul>
                    <a style="display: none" href="#">Order Now</a>
                </div>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12 col-6">
                <div class="single-price">
                    <div class="prc-head">
                        <span>10 Amigos</span>
                        <h5><small>$</small>11.400c/u</h5>
                    </div>
                    <ul>
                        <li>10 Personas</li>
                        <li>11 Almuerzos/p</li>
                        <li>110 Almuerzos</li>
                        <li>$1.254.300</li>
                        <li>[ENTRADA + PLATO FUERTE + DOMICILIO]</li>
                    </ul>
                    <a style="display: none" href="#">Order Now</a>
                </div>
            </div>
        </div>
        <div class="section-title">
            <h2>Planes Envase Desechable</h2>
            <p>La personalización de los planes se puede hacer con la cantidad de personas y N° de almuerzos que desee, los datos anunciados a continuación son cantidades y valores intermedios de cada uno de los planes.</p>
        </div>
        <div class="row">
            <div class="col-md-4 col-sm-4 col-xs-12 col-6">
                <div class="single-price">
                    <div class="prc-head">
                        <span>Personal</span>
                        <h5><small>$</small>15.100c/u</h5>
                    </div>
                    <ul>
                        <li>1 Persona</li>
                        <li>11 Almuerzos</li>
                        <li>$166.100</li>
                        <li>[ENTRADA + PLATO FUERTE + DOMICILIO]</li>
                    </ul>
                    <a style="display: none" href="#">Order Now</a>
                </div>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12 col-6">
                <div class="single-price">
                    <div class="prc-head">
                        <span>3 Amigos</span>
                        <h5><small>$</small>13.000c/u</h5>
                    </div>
                    <ul>
                        <li>3 Personas</li>
                        <li>11 Almuerzos/p</li>
                        <li>33 Almuerzos</li>
                        <li>$427.900</li>
                        <li>[ENTRADA + PLATO FUERTE + DOMICILIO]</li>
                    </ul>
                    <a style="display: none" href="#">Order Now</a>
                </div>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12 col-6">
                <div class="single-price">
                    <div class="prc-head">
                        <span>10 Amigos</span>
                        <h5><small>$</small>12.250c/u</h5>
                    </div>
                    <ul>
                        <li>10 Personas</li>
                        <li>11 Almuerzos/p</li>
                        <li>110 Almuerzos</li>
                        <li>$1.254.300</li>
                        <li>[ENTRADA + PLATO FUERTE + DOMICILIO]</li>
                    </ul>
                    <a style="display: none" href="#">Order Now</a>
                </div>
            </div>
        </div>
        <div class="section-title">
            <h2>Cenas y eventos</h2>
            <p>Para el servicio de catering debes usar nuestro servicio de meseros.</p>
        </div>
        <div class="row">
            <div class="col-md-6 col-sm-4 col-xs-12 col-6">
                <div class="single-price">
                    <div class="prc-head">
                        <h5>Alimentos</h5>
                    </div>
                    <ul>
                        <li>Cenas y almuerzos desde $21.300/plato*</li>
                        <li>Desayunos desde $17.800*</li>
                        <li>Pasabocas desde $4.100*</li>
                        <li>Refrigerios desde $6.650*</li>
                        <li><b>*Aplican cantidades minimas</b></li>
                    </ul>
                    <a style="display: none" href="#">Order Now</a>
                </div>
            </div>
            <div class="col-md-6 col-sm-4 col-xs-12 col-6">
                <div class="single-price">
                    <div class="prc-head">
                        <h5>Servicios</h5>
                    </div>
                    <ul>
                        <li>Mesero 1 a 5 horas: $96.000</li>
                        <li>Menaje x persona: $ 4.000</li>
                        <li>Transporte: $25.000</li>
                    </ul>
                    <a style="display: none" href="#">Order Now</a>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- pricing area end -->
<!-- team area start -->
<section class="team-area bg-gray ptb--100" id="team" style="display: none">
    <div class="container">
        <div class="section-title">
            <h2>Our Amazing Team</h2>
            <p>Nemo enim ipsam voluptatem quia voluptas sit</p>
        </div>
        <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12 col-6">
                <div class="single-team">
                    <div class="team-thumb">
                        <img src="assets/img/team/team-img1.jpg" alt="image">
                    </div>
                    <h4>Jhon Deo</h4>
                    <span>Web Developer</span>
                    <ul class="tst-social">
                        <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                        <li><a href="#"><i class="fa fa-twitter"></i></a></li>
                        <li><a href="#"><i class="fa fa-linkedin"></i></a></li>
                        <li><a href="#"><i class="fa fa-dribbble"></i></a></li>
                        <li><a href="#"><i class="fa fa-ponterest"></i></a></li>
                    </ul>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12 col-6">
                <div class="single-team">
                    <div class="team-thumb">
                        <img src="assets/img/team/team-img2.jpg" alt="image">
                    </div>
                    <h4>Jhon Deo</h4>
                    <span>Web Developer</span>
                    <ul class="tst-social">
                        <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                        <li><a href="#"><i class="fa fa-twitter"></i></a></li>
                        <li><a href="#"><i class="fa fa-linkedin"></i></a></li>
                        <li><a href="#"><i class="fa fa-dribbble"></i></a></li>
                        <li><a href="#"><i class="fa fa-ponterest"></i></a></li>
                    </ul>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12 col-6">
                <div class="single-team">
                    <div class="team-thumb">
                        <img src="assets/img/team/team-img2.jpg" alt="image">
                    </div>
                    <h4>Jhon Deo</h4>
                    <span>Web Developer</span>
                    <ul class="tst-social">
                        <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                        <li><a href="#"><i class="fa fa-twitter"></i></a></li>
                        <li><a href="#"><i class="fa fa-linkedin"></i></a></li>
                        <li><a href="#"><i class="fa fa-dribbble"></i></a></li>
                        <li><a href="#"><i class="fa fa-ponterest"></i></a></li>
                    </ul>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12 col-6">
                <div class="single-team">
                    <div class="team-thumb">
                        <img src="assets/img/team/team-img4.jpg" alt="image">
                    </div>
                    <h4>Jhon Deo</h4>
                    <span>Web Developer</span>
                    <ul class="tst-social">
                        <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                        <li><a href="#"><i class="fa fa-twitter"></i></a></li>
                        <li><a href="#"><i class="fa fa-linkedin"></i></a></li>
                        <li><a href="#"><i class="fa fa-dribbble"></i></a></li>
                        <li><a href="#"><i class="fa fa-ponterest"></i></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- team area end -->
<!-- call-action area start -->
<section class="call-to-action ptb--100" id="download">
    <div class="container">
        <div class="section-title text-white">
            <h2>Descarga nuestra App</h2>
            <p>Plataformas disponibles</p>
        </div>
        <div class="download-btns btn-area text-center">
            <a href="#"><i class="fa fa-apple"></i>Apple store</a>
            <a href="#"><i class="fa fa-android"></i>Google Play store</a>
        </div>
    </div>
</section>
<!-- call-action area end -->
<!-- blog area start -->
<section class="blog-post ptb--100" id="blog" style="display: none">
    <div class="container">
        <div class="section-title">
            <h2>Latest News</h2>
            <p>Nemo enim ipsam voluptatem quia voluptas sit</p>
        </div>
        <div class="row">
            <div class="col-md-4 col-sm-6 col-xs-12 col-6">
                <div class="single-post">
                    <a href="blog.html"><img src="assets/img/blog/blog-post-img.jpg" alt="blog image"></a>
                    <div class="blog-meta">
                        <ul>
                            <li><i class="fa fa-user"></i>John</li>
                            <li><i class="fa fa-comment"></i>Comments</li>
                            <li><i class="fa fa-calendar"></i>21 Feb 2018</li>
                        </ul>
                    </div>
                    <h2><a href="blog.html">There are many variations</a></h2>
                    <p>Lorem ipsum dolor sit amet,ut consectetur adipisicing elit,eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12 col-6">
                <div class="single-post">
                    <a href="blog.html"><img src="assets/img/blog/blog-post-img1.jpg" alt="blog image"></a>
                    <div class="blog-meta">
                        <ul>
                            <li><i class="fa fa-user"></i>John</li>
                            <li><i class="fa fa-comment"></i>Comments</li>
                            <li><i class="fa fa-calendar"></i>21 Feb 2018</li>
                        </ul>
                    </div>
                    <h2><a href="blog.html">There are many variations</a></h2>
                    <p>Lorem ipsum dolor sit amet,ut consectetur adipisicing elit,eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12 col-6">
                <div class="single-post">
                    <a href="blog.html"><img src="assets/img/blog/blog-post-img2.jpg" alt="blog image"></a>
                    <div class="blog-meta">
                        <ul>
                            <li><i class="fa fa-user"></i>John</li>
                            <li><i class="fa fa-comment"></i>Comments</li>
                            <li><i class="fa fa-calendar"></i>21 Feb 2018</li>
                        </ul>
                    </div>
                    <h2><a href="blog.html">There are many variations</a></h2>
                    <p>Lorem ipsum dolor sit amet,ut consectetur adipisicing elit,eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- blog area end -->
<!-- client area start -->
<div class="clinet-area bg-gray ptb--100" style="display:none">
    <div class="container">
        <div class="client-carousel owl-carousel">
            <img src="assets/img/client/client-img.png" alt="client image">
            <img src="assets/img/client/client-img1.png" alt="client image">
            <img src="assets/img/client/client-img2.png" alt="client image">
            <img src="assets/img/client/client-img3.png" alt="client image">
            <img src="assets/img/client/client-img1.png" alt="client image">
        </div>
    </div>
</div>
<!-- client area end -->
<!-- contact area start -->
<section class="contact-area ptb--100" id="contact">
    <div class="container">
        <div class="section-title">
            <h2>Contáctanos</h2>
        </div>
        <div class="row">
            <div class="col-md-6 col-sm-6 col-xs-12">
                <div class="contact-form">
                    <img src="assets/img/icon/logo-lonchis.png" alt="client image">
                </div>
                
            </div>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <div class="contact_info">
                    <div class="s-info" style="display: none">
                        <i class="fa fa-map-marker"></i>
                        <div class="meta-content">
                            <span>17 Bath Rd, Heathrow, Longford,Hounslow</span>
                            <span>TW7 1AB, UK</span>
                        </div>
                    </div>
                    <div class="s-info">
                        <i class="fa fa-mobile"></i>
                        <div class="meta-content">
                            <span>+57 310 341 8432</span>

                        </div>
                    </div>
                    <div class="s-info">
                        <i class="fa fa-paper-plane"></i>
                        <div class="meta-content">
                            <span>servicioalcliente@lonchis.com.co</span>
                        </div>
                    </div>
                    <div class="c-social">
                        <ul>
                            <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                            <li><a href="#"><i class="fa fa-twitter"></i></a></li>
                            <li style="display: none"><a href="#"><i class="fa fa-behance"></i></a></li>
                            <li style="display: none"><a href="#"><i class="fa fa-dribbble"></i></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection