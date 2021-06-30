@extends(config("app.views").'.layouts.app')

@section('content')
<!--slider area start-->

<!--banner area end-->
<!--product area start-->
<div class="product_area mb-65" ng-controller="LonchisCtrl">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section_title">
                      <p>Deliciosos y nutritivos</p>
                       <h2>Planes de almuerzos</h2>
                    </div>
                    <p style="text-align:center">Un servicio de comida saludable, fresca y deliciosa que te ayudará a llevar una dieta balanceada de una manera fácil y práctica con nuestros planes de almuerzos saludables a domicilio.</p>
                    <br/>
                    <h2>¿Cómo funciona?</h2>
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
        </div> 
        <div class="product_container">  
            <div class="row">
                <article class="single_product col-lg-4 col-md-4">
                    <figure>
                        <div class="product_thumb">
                            <a class="primary_img" href="javascript:;"><img src="https://gohife.s3.us-east-2.amazonaws.com/public/ecom-home/plan1.jpg" alt=""></a>
                            <a class="secondary_img" href="javascript:;"><img src="assets/img/product/product21.jpg" alt=""></a>
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
                                            <span class="current_price">$11,000</span>
                                        </div>
                                        <div class="addto_cart_btnF">
                                                    <a href="javascript:;" ng-click="addToCart(1)" title="Add to cart">Probar</a>
                                                </div>
                        </figcaption>
                    </figure>
                </article>
                <article class="single_product col-lg-4 col-md-4">
                    <figure>
                        <div class="product_thumb">
                            <a class="primary_img" href="javascript:;"><img src="https://gohife.s3.us-east-2.amazonaws.com/public/ecom-home/plan11.jpg" alt=""></a>
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
                <article class="single_product col-lg-4 col-md-4">
                    <figure>
                        <div class="product_thumb">
                            <a class="primary_img" href="javascript:;"><img src="/fitmeal/images/plato-7/07.360.png" alt=""></a>
                            <a class="secondary_img" href="javascript:;"><img src="assets/img/product/product21.jpg" alt=""></a>
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
                            <h4 class="product_name"><a href="">Envase Desechable</a></h4>
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

@endsection