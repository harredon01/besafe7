<div class="header_account_list  mini_cart_wrapper" ng-controller="CartCtrl">
                                   <a href="javascript:void(0)"><span class="lnr lnr-cart"></span><span class="item_count">@{{items.length}}</span></a>
                                    <!--mini cart-->
                                    <div class="mini_cart">
                                        <div class="cart_gallery">
                                            <div class="cart_close">
                                                <div class="cart_text">
                                                    <h3>cart</h3>
                                                </div>
                                                <div class="mini_cart_close">
                                                    <a href="javascript:void(0)"><i class="icon-x"></i></a>
                                                </div>
                                            </div>
                                            <div class="cart_item" ng-repeat="item in items">
                                               <div class="cart_img">
                                                   <a href="#"><img ng-src="@{{item.attributes.image.file}}" alt=""></a>
                                               </div>
                                                <div class="cart_info">
                                                    <a href="#">@{{item.name}}</a>
                                                    <p>@{{item.quantity}} x <span> @{{item.priceWithConditions | currency}} </span>  </p>    
                                                </div>
                                                <div class="cart_remove"> 
                                                    <a href="javascript:;" ng-click="deleteCartItem(item.id)"><i class="icon-x"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mini_cart_table">
                                            <div class="cart_table_border">
                                                <div class="cart_total">
                                                    <span>Sub total:</span>
                                                    <span class="price">@{{subtotal | currency}}</span>
                                                </div>
                                                <div class="cart_total mt-10">
                                                    <span>total:</span>
                                                    <span class="price">@{{total | currency}}</span> 
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mini_cart_footer">
                                           <div class="cart_button">
                                                <a href="javascript:;" ng-click="clearCart()"><i class="fa fa-shopping-cart"></i> Limpiar</a>
                                            </div>
                                            <div class="cart_button">
                                                <a href="/checkout"><i class="fa fa-sign-in"></i>Pagar</a>
                                            </div>

                                        </div>
                                    </div>
                                    <!--mini cart end-->
                               </div>
