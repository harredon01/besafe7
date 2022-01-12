@extends(config("app.views").'.layouts.app')

@section('content')
<style>
    .main-slider .slider_content {
        padding:15px;
        background-color: rgba(255,255,255,0.5);
    }
    .transback {
        background-color: rgba(255,255,255,0.5);
    }
    .product_content .addto_cart_btnF {
        font-size: 18px;
        margin-top: 8px;
        color:#0d6efd;
    }
</style>
<!--slider area start-->
    <section class="slider_section slider_s_five mb-70 main-slider">
        <div class="slider_area owl-carousel">
            <div class="single_slider d-flex align-items-center" data-bgimg="https://gohife.s3.us-east-2.amazonaws.com/public/ecom-home/Banner_PP1.jpg">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="slider_content">
                                <h1>Planes de almuerzos</h1>
                                <h2>Fresco, saludable y delicioso

</h2>
                                <p>
								Lleva una dieta balanceada de una manera fácil y práctica
							     </p> 
                                <a href="/a/products/cenas-y-almuerzos?merchant_id=1300">Probar </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="single_slider d-flex align-items-center" data-bgimg="https://gohife.s3.us-east-2.amazonaws.com/public/ecom-home/BannerPP2.jpg">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="slider_content">
                                <h1>Catering eventos</h1>
                                <h2>¿Cuál es tu ocasión?</h2>
                                <p>
								Para eventos sociales, eventos empresariales, rodajes y mucho más.

							    </p> 
                                <a href="/a/products/cenas-y-almuerzos?merchant_id=1300">Cenas y almuerzos </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="single_slider d-flex align-items-center" data-bgimg="https://gohife.s3.us-east-2.amazonaws.com/public/ecom-home/BannerPP3.jpg">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="slider_content">
                                <h1>Listo para servir</h1>
                                <h2>Calienta, sirve y ¡vuala!</h2>
                                <p>
								Calienta de 10 a 15 minutos en el horno, sirve en casa y sorprende a tus invitados.

							    </p> 
                                <a href="/a/products/cenas-y-almuerzos?merchant_id=1300">Menu </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--slider area end-->
    
    <!--banner area start-->
    <div class="banner_area">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-6">
                    <div class="single_banner">
                        <div class="banner_thumb">
                            <a href="shop.html"><img src="assets/img/bg/banner1.jpg" alt=""></a> 
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6">
                    <div class="single_banner">
                        <div class="banner_thumb">
                            <a href="shop.html"><img src="assets/img/bg/banner2.jpg" alt=""></a> 
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--banner area end-->
    <!--product area start-->
    <div class="product_area mb-65" ng-controller="LonchisCtrl">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="section_title">
                      <p>Deliciosos y Nutritivos</p>
                       <h2>Quiero Probar</h2>
                    </div>
                    <p style="text-align:center">Un servicio de comida saludable, fresca y deliciosa que te ayudara a llevar una dieta balanceada de una manera fácil y práctica. Mejora tu calidad de vida, fortalece tu salud y cuida tu figura con nuestros planes de almuerzos saludables a domicilio. Programa tus almuerzos antes de las 10 pm del día anterior y recibe tus comida fresca en la puerta de tu casa u oficina.</p>
                    <br/>
                    <h2>¿Cómo funciona?</h2>
                    </div>
            </div> 
            <div class="row">
                <div class="col-lg-6 col-md-6">
                    
                    <iframe width="425" height="300" src="https://www.youtube.com/embed/zTHRBZUB4AI" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    
                </div>
                <div class="col-lg-6 col-md-6">
                    <ol>
                        <li>Descarga la app en tu celular o usa la versión web</li> 
                        <li>Regístrate</li>
                        <li>Compra un paquete de almuerzos</li>
                        <li>Programa tus almuerzos antes de las 10 pm del día anterior </li>
                        <li>recibe tus comida fresca en la puerta de tu casa u oficina.</li>
                        
                    </ol>
                    <p><a href="https://itunes.apple.com/us/app/lonchis/id1459807225?ls=1&mt=8" style="margin-right:17px"><i class="fa fa-apple" style="font-size:35px"></i>Apple store</a>
                        <a href="https://play.google.com/store/apps/details?id=com.recurring.food" style="margin-right:16px"><i class="fa fa-android" style="font-size:35px"></i>Google Play store</a>
                        <a href="https://app.lonchis.com.co"><i class="fa fa-globe" style="font-size: 35px"></i>Version Web</a></p><br>
                </div>
            </div> <br>
             <div class="product_container">  
               <div class="row">
                   <div class="col-12">
                        <div class="product_carousel product_column3 owl-carousel">
                            <article class="single_product">
                                <figure>
                                    <div class="product_thumb">
                                        <a class="primary_img" href="/a/planes"><img src="https://gohife.s3.us-east-2.amazonaws.com/public/ecom-home/plan1.jpg" alt=""></a>
                                        <div class="label_product">
                                            <span class="label_sale">Sale</span>
                                        </div>
                                        <div class="action_links">
                                            <ul>
                                                <li class="add_to_cart"><a href="javascript:;" ng-click="addToCart(1)" data-tippy="Add to cart" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"> <span class="lnr lnr-cart"></span></a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <figcaption class="product_content">
                                        <h4 class="product_name"><a href="javascript:;">Prueba el primero con tu cupon de bienvenida</a></h4>
                                        <div class="price_box"> 
                                            <span class="current_price">$14,000</span>
                                        </div>
                                        <div class="addto_cart_btnF">
                                                    <a href="javascript:;" ng-click="addToCart(1)" title="Add to cart">Probar</a>
                                                </div>
                                    </figcaption>
                                </figure>
                            </article>
                            <article class="single_product">
                                <figure>
                                    <div class="product_thumb">
                                        <a class="primary_img" href="/a/planes"><img src="https://gohife.s3.us-east-2.amazonaws.com/public/ecom-home/plan11.jpg" alt=""></a>
                                        <div class="label_product">
                                            <span class="label_sale">Sale</span>
                                        </div>
                                        <div class="action_links">
                                            <ul>
                                                <li class="add_to_cart"><a href="javascript:;" ng-click="addToCart(2)" data-tippy="Add to cart" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"> <span class="lnr lnr-cart"></span></a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <figcaption class="product_content">
                                        <h4 class="product_name"><a href="">Eco Friendly</a></h4>
                                        <div class="price_box"> 
                                            Por almuerzo:
                                            <span class="current_price">@{{precio1D/cantidad1 | currency}}</span>
                                        </div>
                                        <label>Cantidad</label><br/>
                                        <select ng-model="cantidad1" class="nice-select" style="float:none;margin:0 auto" name="region_id" ng-change="changeAmount(1)" 
                                                            ng-options="option for option in options" required>

                                                    </select>
                                        <div class="addto_cart_btnF">
                                                    <a href="javascript:;" ng-click="addToCart(2)" title="Add to cart">Probar</a>
                                                </div>
                                    </figcaption>
                                </figure>
                            </article>
                            <article class="single_product">
                                <figure>
                                    <div class="product_thumb">
                                        <a class="primary_img" href="/a/planes"><img src="https://gohife.s3.us-east-2.amazonaws.com/public/ecom-home/plan2.jpg" alt=""></a>
                                        <div class="label_product">
                                            <span class="label_sale">Sale</span>
                                        </div>
                                        <div class="action_links">
                                            <ul>
                                                <li class="add_to_cart"><a href="javascript:;" ng-click="addToCart(3)" data-tippy="Add to cart" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"> <span class="lnr lnr-cart"></span></a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <figcaption class="product_content">
                                        <h4 class="product_name"><a href="">Plan Desechable</a></h4>
                                        <div class="price_box"> 
                                            Por almuerzo:
                                            <span class="current_price">@{{precio2D/cantidad2 | currency}}</span>
                                        </div>
                                        <label>Cantidad</label><br/>
                                        <select ng-model="cantidad2" class="nice-select" style="float:none;margin:0 auto" name="region_id" ng-change="changeAmount(2)" 
                                                            ng-options="option for option in options2" required>

                                                    </select>
                                        <div class="addto_cart_btnF">
                                                    <a href="javascript:;" ng-click="addToCart(3)" title="Add to cart">Probar</a>
                                                </div>
                                    </figcaption>
                                </figure>
                            </article>
                        </div>
                    </div>
                </div>        
            </div>  
        </div> 
    </div>
   <!--home three bg area start-->   
    <div class="home3_bg_area product_five">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-5">
                   <div class="productbg_right_left">
                        <div class="deals_prodict_three">
                            <div class="deals_title">
                                <h2>Descuentos</h2>
                            </div>
                            <div class="deals_prodict_inner3">
                                <div class="product_carousel deals3_column1 owl-carousel">
                                    <article class="single_product">
                                        <figure>
                                            <div class="product_thumb">
                                                <a class="primary_img" href="javascript:;"><img src="https://gohife.s3.us-east-2.amazonaws.com/public/ecom-home/317_317.jpg" alt=""></a>
                                                <div class="label_product">
                                                    <span class="label_sale">Sale</span>
                                                </div>
                                            </div>
                                            <figcaption class="product_content">
                                                <h4 class="product_name"><a href="javascript:;" style="font-size:23px"><strong>5% Off</strong></a></h4>
                                                <div class="price_box"> 
                                                    <span class="current_price">$19,000.00</span>
                                                    <span class="old_price">$20,000.00</span>
                                                </div>
                                                <div class="product_timing">
                                                    <div data-countdown="2021/03/01"></div>
                                                </div>
<!--                                                <div class="addto_cart_btn">
                                                    <a href="cart.html" title="Add to cart">Add to Cart</a>
                                                </div>-->
                                            </figcaption>
                                        </figure>
                                    </article>
                                </div>
                            </div>
                        </div>
                        <div class="banner_thumb">
                            <a href="shop.html"><img src="assets/img/bg/banner12.jpg" alt=""></a> 
                        </div>
                    </div>
                </div>
                <div class="col-lg-8 col-md-7">
                    <div class="productbg_right_side">
                        <div class="small_product_inner3">
                            <div class="section_title">
                               <h2>Menu's de almuerzo favoritos</h2>
                            </div>
                            <div class="small_product_area product_carousel smallp_column2 owl-carousel">
                                <div class="product_items">
                                    <article class="single_product">
                                        <figure>
                                            <div class="product_thumb">
                                                <a class="primary_img" href="javascript:;"><img src="https://gohife.s3.us-east-2.amazonaws.com/public/ecom-home/cordonblue.jpg" alt=""></a>
                                            </div>
                                            <figcaption class="product_content">
                                                <h4 class="product_name"><a href="javascript:;">Cordon Blue</a></h4>
                                                <p>Pollo crocante relleno de jamón y queso</p>
                                            </figcaption>
                                        </figure>
                                    </article>
                                    <article class="single_product">
                                        <figure>
                                            <div class="product_thumb">
                                                <a class="primary_img" href="javascript:;"><img src="https://gohife.s3.us-east-2.amazonaws.com/public/ecom-home/caprese.jpg" alt=""></a>
                                            </div>
                                            <figcaption class="product_content">
                                                <h4 class="product_name"><a href="javascript:;">Ensalada Caprese</a></h4>
                                                <p>Rodajas de tomate, mozzarella fresca y hojas de albahaca</p>
                                            </figcaption>
                                        </figure>
                                    </article>
                                </div>
                                <div class="product_items">
                                    <article class="single_product">
                                        <figure>
                                            <div class="product_thumb">
                                                <a class="primary_img" href="javascript:;"><img src="https://gohife.s3.us-east-2.amazonaws.com/public/ecom-home/sopamexicana.jpg" alt=""></a>
                                            </div>
                                            <figcaption class="product_content">
                                                <h4 class="product_name"><a href="javascript:;">Sopa Mexicana</a></h4>
                                                <p>Crema de tomate, pollo, aguacate, queso, totopos, pico de gallo y crema agria.</p>
                                            </figcaption>
                                        </figure>
                                    </article>
                                    <article class="single_product">
                                        <figure>
                                            <div class="product_thumb">
                                                <a class="primary_img" href="javascript:;"><img src="https://gohife.s3.us-east-2.amazonaws.com/public/ecom-home/filetepescado.jpg" alt=""></a>
                                            </div>
                                            <figcaption class="product_content">
                                                <h4 class="product_name"><a href="javascript:;">Filete de pescado blanco</a></h4>
                                                <p>Filete de pescado blanco a la plancha (Breca)</p>
                                            </figcaption>
                                        </figure>
                                    </article>
                                </div>
                            </div>
                        </div>
                        <div class="small_product_inner3">
                            <div class="section_title">
                               <h2>Menús favoritos  para Catering rodajes</h2>
                            </div>
                            <div class="small_product_area product_carousel smallp_column2 owl-carousel">
                                <div class="product_items">
                                    <article class="single_product">
                                        <figure>
                                            <div class="product_thumb">
                                                <a class="primary_img" href="javascript:;"><img src="https://gohife.s3.us-east-2.amazonaws.com/public/ecom-home/parabe.jpg" alt=""></a>
                                            </div>
                                            <figcaption class="product_content">
                                                <h4 class="product_name"><a href="javascript:;">Plato Arabe</a></h4>
                                                <p>Pan pita con hummus para untar, Kibbes, tabule y falafel.</p>
                                                <div class="price_box"> 
                                                    <span class="current_price">$20,000.00</span>
                                                </div>
                                            </figcaption>
                                        </figure>
                                    </article>
                                    <article class="single_product">
                                        <figure>
                                            <div class="product_thumb">
                                                <a class="primary_img" href="javascript:;"><img src="https://gohife.s3.us-east-2.amazonaws.com/public/ecom-home/frijolada.jpg" alt=""></a>
                                            </div>
                                            <figcaption class="product_content">
                                                <h4 class="product_name"><a href="javascript:;">Cazuela de frijoles</a></h4>
                                                <p>Frijoles, arroz blanco, carne molida, arepita paisa, aguacate y tajadas.</p>
                                                <div class="price_box"> 
                                                    <span class="current_price">$20,000.00</span>
                                                </div>
                                            </figcaption>
                                        </figure>
                                    </article>
                                </div>
                                <div class="product_items">
                                    <article class="single_product">
                                        <figure>
                                            <div class="product_thumb">
                                                <a class="primary_img" href="javascript:;"><img src="https://gohife.s3.us-east-2.amazonaws.com/public/ecom-home/ajiaco.jpg" alt=""></a>
                                            </div>
                                            <figcaption class="product_content">
                                                <h4 class="product_name"><a href="javascript:;">Ajiaco Santafereño</a></h4>
                                                <p> Esta sopa de papa emblemática de Bogotá, se acompaña con arroz blanco, pechuga de pollo desmechada, mazorca, aguacate, crema de leche y alcaparras.</p>
                                                <div class="price_box"> 
                                                    <span class="current_price">$20,000.00</span>
                                                </div>
                                            </figcaption>
                                        </figure>
                                    </article>
                                    <article class="single_product">
                                        <figure>
                                            <div class="product_thumb">
                                                <a class="primary_img" href="javascript:;"><img src="https://gohife.s3.us-east-2.amazonaws.com/public/ecom-home/pollomaracuya.jpg" alt=""></a>
                                            </div>
                                            <figcaption class="product_content">
                                                <h4 class="product_name"><a href="javascript:;">Pechuga en salsa de maracuya</a></h4>
                                                <p><a href="#">Fruits</a></p>
                                                <div class="price_box"> 
                                                    <span class="current_price">$20,000.00</span>
                                                </div>
                                            </figcaption>
                                        </figure>
                                    </article>
                                </div>
                            </div> 
                        </div> 
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--home three bg area end--> 
    
    <!--banner fullwidth area satrt-->
    <div class="banner_fullwidth">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="banner_full_content">
                        <p>Black Fridays !</p>
                        <h2 class="transback">Catering para eventos <span>Para eventos sociales, eventos empresariales, rodajes, Celebraciones en casa</span></h2>
                        <a href="/a/products/cenas-y-almuerzos?merchant_id=1300">Comprar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--banner fullwidth area end-->
    
    <!--product area start-->
    <div class="product_area mb-65">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="section_title">
                      <p>Frescos y Deliciosos </p>
                       <h2>Ultimos menús agregados</h2>
                    </div>
                </div>
            </div> 
             <div class="product_container">  
               <div class="row">
                   <div class="col-12">
                        <div class="product_carousel product_column3 owl-carousel">
                            <article class="single_product">
                                <figure>
                                    <div class="product_thumb">
                                        <a class="primary_img" href="javascript:;"><img src="https://gohife.s3.us-east-2.amazonaws.com/public/ecom-home/nuwmenu1.jpg" alt=""></a>
                                        <div class="label_product">
                                            <span class="label_new">New</span>
                                        </div>
                                    </div>
                                    <figcaption class="product_content">
                                        <h4 class="product_name"><a href="javascript:;">Pad thai de pollo y camarones</a></h4>
                                        <p>Un plato típico de la cocina tailandesa con pasta de arroz salteado al wok con pollo, camarones, huevo y verduras.</p>
                                    </figcaption>
                                </figure>
                            </article>
                            <article class="single_product">
                                <figure>
                                    <div class="product_thumb">
                                        <a class="primary_img" href="javascript:;"><img src="https://gohife.s3.us-east-2.amazonaws.com/public/ecom-home/Newmenu2.jpg" alt=""></a>
                                        <div class="label_product">
                                            <span class="label_new">New</span>
                                        </div>
                                    </div>
                                    <figcaption class="product_content">
                                        <h4 class="product_name"><a href="javascript:;">Cazuela de mariscos</a></h4>
                                        <p>Sopa de mariscos en base de leche de coco y tomate natural</p>
                                    </figcaption>
                                </figure>
                            </article>
                            <article class="single_product">
                                <figure>
                                    <div class="product_thumb">
                                        <a class="primary_img" href="javascript:;"><img src="https://gohife.s3.us-east-2.amazonaws.com/public/ecom-home/newmenu3.jpg" alt=""></a>
                                        <div class="label_product">
                                            <span class="label_new">New</span>
                                        </div>
                                    </div>
                                    <figcaption class="product_content">
                                        <h4 class="product_name"><a href="javascript:;">Crema de zapallo y jengibre auyama y jengibre</a></h4>
                                        <p>Una saludable sopa que nos ayudará a limpiar el colon de toxinas, a eliminar grasa corporal y prevenir la retención de líquidos.</p>
                                    </figcaption>
                                </figure>
                            </article>
                        </div>
                    </div>
                </div>        
            </div>  
        </div> 
    </div>
    <!--product area end-->
    <!--testimonial area start-->
    <div class="faq-client-say-area">
        <div class="container">   
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    <!--testimonial area start-->
                    <div class="testimonial_area  testimonial_about">
                        <div class="section_title">
                           <h2>Lo que dicen nuestros clientes</h2>
                        </div>
                        <div class="testimonial_container">
                            <div class="testimonial_carousel testimonial-two owl-carousel">
                                <div class="single_testimonial">
                                    <div class="testimonial_content">

                                        <p>Hace mucho tiempo estaba buscando un lugar que me diera toda la confianza para poder pedirle almuerzo a mi papá. El tiene más de 70 años y por ende buscaba un lugar que no solo fuera rico sino que fuera nutritivo y con buena variedad para que se alimentara muy bien. cuando encontré a Lonchis primero pague para hacer una prueba, y desde el día uno nos gustó! Encontramos que por buen precio recibíamos un producto de muy buena calidad en términos de sabor, cantidad y variedad.</p>
                                        <a href="javascript:;">Natalia Castellanos</a>
                                    </div>
                                </div>
                                <div class="single_testimonial">
                                    <div class="testimonial_content">

                                        <p>¡Gracias Lonchis! Comidas deliciosas, menús variados y siempre llegan a tiempo. pero los eligió a ustedes sobre otros servicios porque su política sostenible va con mi estilo de vida. Los envases retornables son lo máximo.</p>
                                        <a href="javascript:;">Andrea Sandoval</a>
                                    </div>
                                </div>
                                <div class="single_testimonial">
                                    <div class="testimonial_content">
                                        <p>Me encanta lochis, porque me simplifica la vida, desde que lo utilizo me despreocupo por pensar que voy a pedir de almuerzo, no gasto en domicilios, ahorro dinero, ademas como super saludable, balanceado y delicioso. La comida es muy variada, super buena calidad y sabor. Definitivamente hay un equilibrio entre precio vs calidad. Super recomendado!!</p>
                                        <a href="javascript:;">Maria Adelaida Torres</a>
                                    </div>
                                </div>
                                <div class="single_testimonial">
                                    <div class="testimonial_content">

                                        <p>Soy fiel cliente de Lonchis ya hace más de 9 meses. Vivo sola y cocinar todos los días es muy difícil.  La verdad lonchis a sido la bendición, pues además  de comer saludable , rico y Variado  ahorrado en mercado, no cocino, ni  lavo loza.</p>
                                        <a href="javascript:;">Maria Luisa Gomez</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--testimonial area end-->
                </div>
            </div>
        </div>    
    </div>
    <!--testimonial area end-->
    <!--blog area start
    <section class="blog_section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="section_title">
                       <p>Our recent articles about Organic</p>
                       <h2>Our Blog Posts</h2>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="blog_carousel blog_column3 owl-carousel">
                    <div class="col-lg-3">
                        <article class="single_blog">
                            <figure>
                                <div class="blog_thumb">
                                    <a href="blog-details.html"><img src="assets/img/blog/blog1.jpg" alt=""></a>
                                </div>
                                <figcaption class="blog_content">
                                   <div class="articles_date">
                                         <p>18/01/2019 | <a href="#">eCommerce</a> </p>
                                    </div>
                                    <h4 class="post_title"><a href="blog-details.html">Lorem ipsum dolor sit amet,  elit. Impedit, aliquam animi, saepe ex.</a></h4>
                                    <footer class="blog_footer">
                                        <a href="blog-details.html">Show more</a>
                                    </footer>
                                </figcaption>
                            </figure>
                        </article>
                    </div>
                    <div class="col-lg-3">
                        <article class="single_blog">
                            <figure>
                                <div class="blog_thumb">
                                    <a href="blog-details.html"><img src="assets/img/blog/blog2.jpg" alt=""></a>
                                </div>
                                <figcaption class="blog_content">
                                   <div class="articles_date">
                                         <p>18/01/2019 | <a href="#">eCommerce</a> </p>
                                    </div>
                                    <h4 class="post_title"><a href="blog-details.html"> dolor sit amet, elit. Illo iste sed animi quaerat  nobis odit  nulla.</a></h4>
                                    <footer class="blog_footer">
                                        <a href="blog-details.html">Show more</a>
                                    </footer>
                                </figcaption>
                            </figure>
                        </article>
                    </div>
                    <div class="col-lg-3">
                        <article class="single_blog">
                            <figure>
                                <div class="blog_thumb">
                                    <a href="blog-details.html"><img src="assets/img/blog/blog3.jpg" alt=""></a>
                                </div>
                                <figcaption class="blog_content">
                                   <div class="articles_date">
                                         <p>18/01/2019 | <a href="#">eCommerce</a> </p>
                                    </div>
                                    <h4 class="post_title"><a href="blog-details.html">maxime laborum voluptas minus, est, unde eaque esse tenetur.</a></h4>
                                    <footer class="blog_footer">
                                        <a href="blog-details.html">Show more</a>
                                    </footer>
                                </figcaption>
                            </figure>
                        </article>
                    </div>
                    <div class="col-lg-3">
                        <article class="single_blog">
                            <figure>
                                <div class="blog_thumb">
                                    <a href="blog-details.html"><img src="assets/img/blog/blog2.jpg" alt=""></a>
                                </div>
                                <figcaption class="blog_content">
                                   <div class="articles_date">
                                         <p>18/01/2019 | <a href="#">eCommerce</a> </p>
                                    </div>
                                    <h4 class="post_title"><a href="blog-details.html">Lorem ipsum dolor sit amet, elit. Impedit, aliquam animi, saepe ex.</a></h4>
                                    <footer class="blog_footer">
                                        <a href="blog-details.html">Show more</a>
                                    </footer>
                                </figcaption>
                            </figure>
                        </article>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--blog area end-->
    
    <!--custom product area start
    <div class="custom_product_area">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="section_title">
                       <p>Recently added our store </p>
                       <h2>Featured Products</h2>
                    </div>
                </div>
            </div> 
            <div class="row">
                <div class="col-12">
                    <div class="small_product_area product_carousel product_column3 owl-carousel">
                        <div class="product_items">
                            <article class="single_product">
                                <figure>
                                    <div class="product_thumb">
                                        <a class="primary_img" href="javascript:;"><img src="assets/img/product/product1.jpg" alt=""></a>
                                        <a class="secondary_img" href="javascript:;"><img src="assets/img/product/product2.jpg" alt=""></a>
                                    </div>
                                    <figcaption class="product_content">
                                        <h4 class="product_name"><a href="javascript:;">Aliquam Consequat</a></h4>
                                        <p><a href="#">Fruits</a></p>
                                        <div class="action_links">
                                            <ul>
                                                <li class="add_to_cart"><a href="cart.html" data-tippy="Add to cart" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"> <span class="lnr lnr-cart"></span></a></li>
                                               <li class="quick_button"><a href="#" data-tippy="quick view" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true" data-bs-toggle="modal" data-bs-target="#modal_box" > <span class="lnr lnr-magnifier"></span></a></li>
                                                 <li class="wishlist"><a href="wishlist.html" data-tippy="Add to Wishlist" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"><span class="lnr lnr-heart"></span></a></li> 
                                                <li class="compare"><a href="#" data-tippy="Add to Compare" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"><span class="lnr lnr-sync"></span></a></li>
                                            </ul>
                                        </div>
                                        <div class="price_box"> 
                                            <span class="current_price">$26.00</span>
                                            <span class="old_price">$362.00</span>
                                        </div>
                                    </figcaption>
                                </figure>
                            </article>
                            <article class="single_product">
                                <figure>
                                    <div class="product_thumb">
                                        <a class="primary_img" href="javascript:;"><img src="assets/img/product/product3.jpg" alt=""></a>
                                        <a class="secondary_img" href="javascript:;"><img src="assets/img/product/product4.jpg" alt=""></a>
                                    </div>
                                    <figcaption class="product_content">
                                        <h4 class="product_name"><a href="javascript:;">Donec Non Est</a></h4>
                                        <p><a href="#">Fruits</a></p>
                                        <div class="action_links">
                                            <ul>
                                                <li class="add_to_cart"><a href="cart.html" data-tippy="Add to cart" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"> <span class="lnr lnr-cart"></span></a></li>
                                               <li class="quick_button"><a href="#" data-tippy="quick view" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true" data-bs-toggle="modal" data-bs-target="#modal_box" > <span class="lnr lnr-magnifier"></span></a></li>
                                                 <li class="wishlist"><a href="wishlist.html" data-tippy="Add to Wishlist" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"><span class="lnr lnr-heart"></span></a></li> 
                                                <li class="compare"><a href="#" data-tippy="Add to Compare" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"><span class="lnr lnr-sync"></span></a></li>
                                            </ul>
                                        </div>
                                        <div class="price_box"> 
                                            <span class="current_price">$46.00</span>
                                            <span class="old_price">$382.00</span>
                                        </div>
                                    </figcaption>
                                </figure>
                            </article>
                            <article class="single_product">
                                <figure>
                                    <div class="product_thumb">
                                        <a class="primary_img" href="javascript:;"><img src="assets/img/product/product5.jpg" alt=""></a>
                                        <a class="secondary_img" href="javascript:;"><img src="assets/img/product/product6.jpg" alt=""></a>
                                    </div>
                                    <figcaption class="product_content">
                                        <h4 class="product_name"><a href="javascript:;">Mauris Vel Tellus</a></h4>
                                        <p><a href="#">Fruits</a></p>
                                        <div class="action_links">
                                            <ul>
                                                <li class="add_to_cart"><a href="cart.html" data-tippy="Add to cart" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"> <span class="lnr lnr-cart"></span></a></li>
                                               <li class="quick_button"><a href="#" data-tippy="quick view" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true" data-bs-toggle="modal" data-bs-target="#modal_box" > <span class="lnr lnr-magnifier"></span></a></li>
                                                 <li class="wishlist"><a href="wishlist.html" data-tippy="Add to Wishlist" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"><span class="lnr lnr-heart"></span></a></li> 
                                                <li class="compare"><a href="#" data-tippy="Add to Compare" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"><span class="lnr lnr-sync"></span></a></li>
                                            </ul>
                                        </div>
                                        <div class="price_box"> 
                                            <span class="current_price">$56.00</span>
                                            <span class="old_price">$362.00</span>
                                        </div>
                                    </figcaption>
                                </figure>
                            </article>
                        </div>
                        <div class="product_items">
                            <article class="single_product">
                                <figure>
                                    <div class="product_thumb">
                                        <a class="primary_img" href="javascript:;"><img src="assets/img/product/product7.jpg" alt=""></a>
                                        <a class="secondary_img" href="javascript:;"><img src="assets/img/product/product8.jpg" alt=""></a>
                                    </div>
                                    <figcaption class="product_content">
                                        <h4 class="product_name"><a href="javascript:;">Quisque In Arcu</a></h4>
                                        <p><a href="#">Fruits</a></p>
                                        <div class="action_links">
                                            <ul>
                                                <li class="add_to_cart"><a href="cart.html" data-tippy="Add to cart" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"> <span class="lnr lnr-cart"></span></a></li>
                                               <li class="quick_button"><a href="#" data-tippy="quick view" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true" data-bs-toggle="modal" data-bs-target="#modal_box" > <span class="lnr lnr-magnifier"></span></a></li>
                                                 <li class="wishlist"><a href="wishlist.html" data-tippy="Add to Wishlist" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"><span class="lnr lnr-heart"></span></a></li> 
                                                <li class="compare"><a href="#" data-tippy="Add to Compare" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"><span class="lnr lnr-sync"></span></a></li>
                                            </ul>
                                        </div>
                                        <div class="price_box"> 
                                            <span class="current_price">$20.00</span>
                                            <span class="old_price">$352.00</span>
                                        </div>
                                    </figcaption>
                                </figure>
                            </article>
                            <article class="single_product">
                                <figure>
                                    <div class="product_thumb">
                                        <a class="primary_img" href="javascript:;"><img src="assets/img/product/product9.jpg" alt=""></a>
                                        <a class="secondary_img" href="javascript:;"><img src="assets/img/product/product10.jpg" alt=""></a>
                                    </div>
                                    <figcaption class="product_content">
                                        <h4 class="product_name"><a href="javascript:;">Cas Meque Metus</a></h4>
                                        <p><a href="#">Fruits</a></p>
                                        <div class="action_links">
                                            <ul>
                                                <li class="add_to_cart"><a href="cart.html" data-tippy="Add to cart" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"> <span class="lnr lnr-cart"></span></a></li>
                                               <li class="quick_button"><a href="#" data-tippy="quick view" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true" data-bs-toggle="modal" data-bs-target="#modal_box" > <span class="lnr lnr-magnifier"></span></a></li>
                                                 <li class="wishlist"><a href="wishlist.html" data-tippy="Add to Wishlist" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"><span class="lnr lnr-heart"></span></a></li> 
                                                <li class="compare"><a href="#" data-tippy="Add to Compare" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"><span class="lnr lnr-sync"></span></a></li>
                                            </ul>
                                        </div>
                                        <div class="price_box"> 
                                            <span class="current_price">$72.00</span>
                                            <span class="old_price">$352.00</span>
                                        </div>
                                    </figcaption>
                                </figure>
                            </article>
                            <article class="single_product">
                                <figure>
                                    <div class="product_thumb">
                                        <a class="primary_img" href="javascript:;"><img src="assets/img/product/product11.jpg" alt=""></a>
                                        <a class="secondary_img" href="javascript:;"><img src="assets/img/product/product12.jpg" alt=""></a>
                                    </div>
                                    <figcaption class="product_content">
                                        <h4 class="product_name"><a href="javascript:;">Proin Lectus Ipsum</a></h4>
                                        <p><a href="#">Fruits</a></p>
                                        <div class="action_links">
                                            <ul>
                                                <li class="add_to_cart"><a href="cart.html" data-tippy="Add to cart" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"> <span class="lnr lnr-cart"></span></a></li>
                                               <li class="quick_button"><a href="#" data-tippy="quick view" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true" data-bs-toggle="modal" data-bs-target="#modal_box" > <span class="lnr lnr-magnifier"></span></a></li>
                                                 <li class="wishlist"><a href="wishlist.html" data-tippy="Add to Wishlist" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"><span class="lnr lnr-heart"></span></a></li> 
                                                <li class="compare"><a href="#" data-tippy="Add to Compare" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"><span class="lnr lnr-sync"></span></a></li>
                                            </ul>
                                        </div>
                                        <div class="price_box"> 
                                            <span class="current_price">$36.00</span>
                                            <span class="old_price">$282.00</span>
                                        </div>
                                    </figcaption>
                                </figure>
                            </article>
                        </div>
                        <div class="product_items">
                            <article class="single_product">
                                <figure>
                                    <div class="product_thumb">
                                        <a class="primary_img" href="javascript:;"><img src="assets/img/product/product13.jpg" alt=""></a>
                                        <a class="secondary_img" href="javascript:;"><img src="assets/img/product/product1.jpg" alt=""></a>
                                    </div>
                                    <figcaption class="product_content">
                                        <h4 class="product_name"><a href="javascript:;">Mauris Vel Tellus</a></h4>
                                        <p><a href="#">Fruits</a></p>
                                        <div class="action_links">
                                            <ul>
                                                <li class="add_to_cart"><a href="cart.html" data-tippy="Add to cart" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"> <span class="lnr lnr-cart"></span></a></li>
                                               <li class="quick_button"><a href="#" data-tippy="quick view" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true" data-bs-toggle="modal" data-bs-target="#modal_box" > <span class="lnr lnr-magnifier"></span></a></li>
                                                 <li class="wishlist"><a href="wishlist.html" data-tippy="Add to Wishlist" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"><span class="lnr lnr-heart"></span></a></li> 
                                                <li class="compare"><a href="#" data-tippy="Add to Compare" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"><span class="lnr lnr-sync"></span></a></li>
                                            </ul>
                                        </div>
                                        <div class="price_box"> 
                                            <span class="current_price">$45.00</span>
                                            <span class="old_price">$162.00</span>
                                        </div>
                                    </figcaption>
                                </figure>
                            </article>
                            <article class="single_product">
                                <figure>
                                    <div class="product_thumb">
                                        <a class="primary_img" href="javascript:;"><img src="assets/img/product/product10.jpg" alt=""></a>
                                        <a class="secondary_img" href="javascript:;"><img src="assets/img/product/product3.jpg" alt=""></a>
                                    </div>
                                    <figcaption class="product_content">
                                        <h4 class="product_name"><a href="javascript:;">Donec Non Est</a></h4>
                                        <p><a href="#">Fruits</a></p>
                                        <div class="action_links">
                                            <ul>
                                                <li class="add_to_cart"><a href="cart.html" data-tippy="Add to cart" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"> <span class="lnr lnr-cart"></span></a></li>
                                               <li class="quick_button"><a href="#" data-tippy="quick view" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true" data-bs-toggle="modal" data-bs-target="#modal_box" > <span class="lnr lnr-magnifier"></span></a></li>
                                                 <li class="wishlist"><a href="wishlist.html" data-tippy="Add to Wishlist" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"><span class="lnr lnr-heart"></span></a></li> 
                                                <li class="compare"><a href="#" data-tippy="Add to Compare" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"><span class="lnr lnr-sync"></span></a></li>
                                            </ul>
                                        </div>
                                        <div class="price_box"> 
                                            <span class="current_price">$46.00</span>
                                            <span class="old_price">$382.00</span>
                                        </div>
                                    </figcaption>
                                </figure>
                            </article>
                            <article class="single_product">
                                <figure>
                                    <div class="product_thumb">
                                        <a class="primary_img" href="javascript:;"><img src="assets/img/product/product8.jpg" alt=""></a>
                                        <a class="secondary_img" href="javascript:;"><img src="assets/img/product/product5.jpg" alt=""></a>
                                    </div>
                                    <figcaption class="product_content">
                                        <h4 class="product_name"><a href="javascript:;">Donec Non Est</a></h4>
                                        <p><a href="#">Fruits</a></p>
                                        <div class="action_links">
                                            <ul>
                                                <li class="add_to_cart"><a href="cart.html" data-tippy="Add to cart" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"> <span class="lnr lnr-cart"></span></a></li>
                                               <li class="quick_button"><a href="#" data-tippy="quick view" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true" data-bs-toggle="modal" data-bs-target="#modal_box" > <span class="lnr lnr-magnifier"></span></a></li>
                                                 <li class="wishlist"><a href="wishlist.html" data-tippy="Add to Wishlist" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"><span class="lnr lnr-heart"></span></a></li> 
                                                <li class="compare"><a href="#" data-tippy="Add to Compare" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"><span class="lnr lnr-sync"></span></a></li>
                                            </ul>
                                        </div>
                                        <div class="price_box"> 
                                            <span class="current_price">$46.00</span>
                                            <span class="old_price">$382.00</span>
                                        </div>
                                    </figcaption>
                                </figure>
                            </article>
                        </div>
                        <div class="product_items">
                            <article class="single_product">
                                <figure>
                                    <div class="product_thumb">
                                        <a class="primary_img" href="javascript:;"><img src="assets/img/product/product1.jpg" alt=""></a>
                                        <a class="secondary_img" href="javascript:;"><img src="assets/img/product/product2.jpg" alt=""></a>
                                    </div>
                                    <figcaption class="product_content">
                                        <h4 class="product_name"><a href="javascript:;">Aliquam Consequat</a></h4>
                                        <p><a href="#">Fruits</a></p>
                                        <div class="action_links">
                                            <ul>
                                                <li class="add_to_cart"><a href="cart.html" data-tippy="Add to cart" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"> <span class="lnr lnr-cart"></span></a></li>
                                               <li class="quick_button"><a href="#" data-tippy="quick view" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true" data-bs-toggle="modal" data-bs-target="#modal_box" > <span class="lnr lnr-magnifier"></span></a></li>
                                                 <li class="wishlist"><a href="wishlist.html" data-tippy="Add to Wishlist" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"><span class="lnr lnr-heart"></span></a></li> 
                                                <li class="compare"><a href="#" data-tippy="Add to Compare" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"><span class="lnr lnr-sync"></span></a></li>
                                            </ul>
                                        </div>
                                        <div class="price_box"> 
                                            <span class="current_price">$26.00</span>
                                            <span class="old_price">$362.00</span>
                                        </div>
                                    </figcaption>
                                </figure>
                            </article>
                            <article class="single_product">
                                <figure>
                                    <div class="product_thumb">
                                        <a class="primary_img" href="javascript:;"><img src="assets/img/product/product11.jpg" alt=""></a>
                                        <a class="secondary_img" href="javascript:;"><img src="assets/img/product/product10.jpg" alt=""></a>
                                    </div>
                                    <figcaption class="product_content">
                                        <h4 class="product_name"><a href="javascript:;">Donec Non Est</a></h4>
                                        <p><a href="#">Fruits</a></p>
                                        <div class="action_links">
                                            <ul>
                                                <li class="add_to_cart"><a href="cart.html" data-tippy="Add to cart" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"> <span class="lnr lnr-cart"></span></a></li>
                                               <li class="quick_button"><a href="#" data-tippy="quick view" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true" data-bs-toggle="modal" data-bs-target="#modal_box" > <span class="lnr lnr-magnifier"></span></a></li>
                                                 <li class="wishlist"><a href="wishlist.html" data-tippy="Add to Wishlist" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"><span class="lnr lnr-heart"></span></a></li> 
                                                <li class="compare"><a href="#" data-tippy="Add to Compare" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"><span class="lnr lnr-sync"></span></a></li>
                                            </ul>
                                        </div>
                                        <div class="price_box"> 
                                            <span class="current_price">$46.00</span>
                                            <span class="old_price">$382.00</span>
                                        </div>
                                    </figcaption>
                                </figure>
                            </article>
                            <article class="single_product">
                                <figure>
                                    <div class="product_thumb">
                                        <a class="primary_img" href="javascript:;"><img src="assets/img/product/product9.jpg" alt=""></a>
                                        <a class="secondary_img" href="javascript:;"><img src="assets/img/product/product8.jpg" alt=""></a>
                                    </div>
                                    <figcaption class="product_content">
                                        <h4 class="product_name"><a href="javascript:;">Mauris Vel Tellus</a></h4>
                                        <p><a href="#">Fruits</a></p>
                                        <div class="action_links">
                                            <ul>
                                                <li class="add_to_cart"><a href="cart.html" data-tippy="Add to cart" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"> <span class="lnr lnr-cart"></span></a></li>
                                               <li class="quick_button"><a href="#" data-tippy="quick view" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true" data-bs-toggle="modal" data-bs-target="#modal_box" > <span class="lnr lnr-magnifier"></span></a></li>
                                                 <li class="wishlist"><a href="wishlist.html" data-tippy="Add to Wishlist" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"><span class="lnr lnr-heart"></span></a></li> 
                                                <li class="compare"><a href="#" data-tippy="Add to Compare" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"><span class="lnr lnr-sync"></span></a></li>
                                            </ul>
                                        </div>
                                        <div class="price_box"> 
                                            <span class="current_price">$56.00</span>
                                            <span class="old_price">$362.00</span>
                                        </div>
                                    </figcaption>
                                </figure>
                            </article>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--custom product area end-->
    

     <!--brand area start
    <div class="brand_area">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="brand_container owl-carousel ">
                        <div class="single_brand">
                            <a href="#"><img src="assets/img/brand/brand1.jpg" alt=""></a>
                        </div>
                        <div class="single_brand">
                            <a href="#"><img src="assets/img/brand/brand2.jpg" alt=""></a>
                        </div>
                        <div class="single_brand">
                            <a href="#"><img src="assets/img/brand/brand3.jpg" alt=""></a>
                        </div>
                        <div class="single_brand">
                            <a href="#"><img src="assets/img/brand/brand4.jpg" alt=""></a>
                        </div>
                        <div class="single_brand">
                            <a href="#"><img src="assets/img/brand/brand2.jpg" alt=""></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--brand area end-->
    <!--brand area end-->
@endsection