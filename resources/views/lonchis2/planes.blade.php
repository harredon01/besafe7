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
                       <h2>Quiero Probar</h2>
                    </div>
                </div>
            </div> 
             <div class="product_container">  
               <div class="row">
                   <div class="col-12">
                        <div class="">
                            <article class="single_product col-lg-4 col-md-4">
                                <figure>
                                    <div class="product_thumb">
                                        <a class="primary_img" href="product-details.html"><img src="/fitmeal/images/plato-7/07.360.png" alt=""></a>
                                        <a class="secondary_img" href="product-details.html"><img src="assets/img/product/product21.jpg" alt=""></a>
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
                                        <h4 class="product_name"><a href="">Prueba 1</a></h4>
                                        <div class="price_box"> 
                                            <span class="current_price">$8,000</span>
                                        </div>
                                    </figcaption>
                                </figure>
                            </article>
                            <article class="single_product col-lg-4 col-md-4">
                                <figure>
                                    <div class="product_thumb">
                                        <a class="primary_img" href="product-details.html"><img src="/fitmeal/images/plato-7/07.360.png" alt=""></a>
                                        <a class="secondary_img" href="product-details.html"><img src="assets/img/product/product21.jpg" alt=""></a>
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
                                            <span class="current_price">@{{precio2D/cantidad2 | currency}}</span>
                                        </div>
                                        <label>Cantidad</label><br/>
                                        <select ng-model="cantidad1" class="nice-select" style="float:none;margin:0 auto" name="region_id" ng-change="changeAmount(1)" 
                                                            ng-options="option for option in options" required>

                                                    </select>
                                    </figcaption>
                                </figure>
                            </article>
                            <article class="single_product col-lg-4 col-md-4">
                                <figure>
                                    <div class="product_thumb">
                                        <a class="primary_img" href="product-details.html"><img src="/fitmeal/images/plato-7/07.360.png" alt=""></a>
                                        <a class="secondary_img" href="product-details.html"><img src="assets/img/product/product21.jpg" alt=""></a>
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
                                                            ng-options="option for option in options" required>

                                                    </select>
                                    </figcaption>
                                </figure>
                            </article>
                        </div>
                    </div>
                </div>        
            </div>  
        </div> 
    </div>

@endsection