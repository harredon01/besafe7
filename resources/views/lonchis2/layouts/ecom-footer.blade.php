<!--footer area start-->
<footer class="footer_widgets" style="padding-top: 20px; background: #f8f9fa;">
        <div class="footer_top">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 col-md-12 col-sm-7">
                        <div class="widgets_container contact_us">
                           <div class="footer_logo">
                               <a href="index.html"><img src="/fitmeal/images/logotranparenteazul300.82.png" style="width: 150px" alt=""></a>
                           </div>
                           <p class="footer_desc">Somos un equipo de tecnologia que desarrolla plataformas para digitalizar empresas y industrias</p>
                            <p><span>Email:</span> <a href="mailto:servicioalcliente@lonchis.com.co">servicioalcliente@lonchis.com.co</a></p>
                            <p><span>Call us:</span> <a href="tel:+573103418432">(310) 3418432</a> </p>
                        </div>          
                    </div>
                    <div class="col-lg-4 col-md-3 col-sm-5">
                        <div class="widgets_container widget_menu">
                            <h3>Informacion</h3>
                            <div class="footer_menu">
                            
                                <ul>
                                    <li><a href="/a/zones">Cobertura</a></li>
                                    <li><a href="/a/faq"> Preguntas Frecuentes</a></li>
                                    <li><a href="/a/terms"> Términos & Condiciones</a></li>
                                    <li><a href="/a/contact-us/bla"> Contacto</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-8">
                        <div class="widgets_container widget_newsletter">
                            <h3>Sign up newsletter</h3>
                            <p class="footer_desc">Get updates by subscribe our weekly newsletter</p>
                            <div class="subscribe_form">
                                <form id="mc-form" class="mc-form footer-newsletter" >
                                    <input id="mc-email" type="email" autocomplete="off" placeholder="Enter you email" />
                                    <button id="mc-submit">Subscribe</button>
                                </form>
                                <!-- mailchimp-alerts Start -->
                                <div class="mailchimp-alerts text-centre">
                                    <div class="mailchimp-submitting"></div><!-- mailchimp-submitting end -->
                                    <div class="mailchimp-success"></div><!-- mailchimp-success end -->
                                    <div class="mailchimp-error"></div><!-- mailchimp-error end -->
                                </div><!-- mailchimp-alerts end -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="footer_bottom">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 col-md-7">
                        <div class="copyright_area">
                            <p>Copyright  © 2021  <a href="#">Lonchis</a></p>
                        </div>
                    </div>    
                    <div class="col-lg-6 col-md-5">    
                        <div class="footer_payment">
                            <ul>
                                <li><a href="#"><img src="/safira/assets/img/icon/paypal2.jpg" alt=""></a></li>
                                <li><a href="#"><img src="/safira/assets/img/icon/paypal3.jpg" alt=""></a></li>
                                <li><a href="#"><img src="/safira/assets/img/icon/paypal4.jpg" alt=""></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>   
    </footer>
    <!--footer area end-->
   
    <!-- modal area start-->
    <div class="modal fade" id="modal_box" tabindex="-1" role="dialog"  aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="icon-x"></i></span>
                </button>
                <div class="modal_body">
                    <div class="container" ng-controller="ProductModalCtrl">
                        <div class="row">
                            <div class="col-lg-5 col-md-5 col-sm-12">
                                <div class="modal_tab">  
                                    <div class="tab-content product-details-large">
                                        <div class="tab-pane fade show active" id="tab1" role="tabpanel" >
                                            <div class="modal_tab_img">
                                                <a href="/a/product-detail/@{{modalProd.slug}}?merchant_id=@{{modalProd.merchant_id}}"><img ng-src="@{{modalProd.src}}" alt=""></a>    
                                            </div>
                                        </div>
                                    </div>
                                </div>  
                            </div> 
                            <div class="col-lg-7 col-md-7 col-sm-12">
                                <div class="modal_right">
                                    <div class="modal_title mb-10">
                                        <h2>@{{modalProd.name}}</h2> 
                                    </div>
                                    <div class="modal_price mb-10" ng-show="modalProd.activeVariant.is_on_sale">
                                        <span class="new_price">@{{modalProd.activeVariant.sale| currency}}</span>    
                                        <span class="old_price" >@{{modalProd.activeVariant.price| currency}}</span>    
                                    </div>
                                    <div class="modal_price mb-10" ng-hide="modalProd.activeVariant.is_on_sale">
                                        <span class="new_price">@{{modalProd.activeVariant.price| currency}}</span>     
                                    </div>
                                    <div class="modal_description mb-15" ng-bind-html="modalProd.description">
                                        
                                    </div> 
                                    <div class="variants_selects">
                                        <div class="modal_add_to_cart">
                                            <form>
                                                <input min="1" max="100" step="1" value="1" type="number" ng-model="modalProd.quantity">
                                                <button ng-click="addCartItem(modalProd)" >add to cart</button>
                                            </form>
                                                
                                        </div>   
                                    </div>
                                    <div class="modal_social" style="display:none">
                                        <h2>Share this product</h2>
                                        <ul>
                                            <li class="facebook"><a href="#"><i class="fa fa-facebook"></i></a></li>
                                            <li class="twitter"><a href="#"><i class="fa fa-twitter"></i></a></li>
                                            <li class="pinterest"><a href="#"><i class="fa fa-pinterest"></i></a></li>
                                            <li class="google-plus"><a href="#"><i class="fa fa-google-plus"></i></a></li>
                                            <li class="linkedin"><a href="#"><i class="fa fa-linkedin"></i></a></li>
                                        </ul>    
                                    </div>      
                                </div>    
                            </div>    
                        </div>     
                    </div>
                </div>    
            </div>
        </div>
    </div>
    <!-- modal area end-->
    

 
<!-- JS
============================================ -->
<script src="/js/all.js?v=1.0.0.18"></script>
<!--jquery min js-->
<script src="/safira/assets/js/vendor/jquery-3.4.1.min.js"></script>
<!--popper min js-->
<script src="/safira/assets/js/popper.js"></script>
<!--bootstrap min js-->
<script src="/safira/assets/js/bootstrap.min.js"></script>
<!--owl carousel min js-->
<script src="/safira/assets/js/owl.carousel.min.js"></script>
<!--slick min js-->
<script src="/safira/assets/js/slick.min.js"></script>
<!--magnific popup min js-->
<script src="/safira/assets/js/jquery.magnific-popup.min.js"></script>
<!--counterup min js-->
<script src="/safira/assets/js/jquery.counterup.min.js"></script>
<!--jquery countdown min js-->
<script src="/safira/assets/js/jquery.countdown.js"></script>
<!--jquery ui min js
<script src="/safira/assets/js/jquery.ui.js"></script>-->
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<!--jquery elevatezoom min js-->
<script src="/safira/assets/js/jquery.elevatezoom.js"></script>
<!--isotope packaged min js-->
<script src="/safira/assets/js/isotope.pkgd.min.js"></script>
<!--slinky menu js-->
<script src="/safira/assets/js/slinky.menu.js"></script>
<!--instagramfeed menu js-->
<script src="/safira/assets/js/jquery.instagramFeed.min.js"></script>
<!-- tippy bundle umd js -->
<script src="/safira/assets/js/tippy-bundle.umd.js"></script>
<!-- Plugins JS -->
<script src="/safira/assets/js/plugins.js?v=1.0.0.18"></script>

<!-- Main JS -->
<script src="/safira/assets/js/main.js?v=1.0.0.18"></script>
