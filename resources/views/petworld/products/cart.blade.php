<div class="col-lg-2 col-6 offset-6 offset-md-0 col-md-3 order-3  mini-cart" ng-controller="CartCtrl">
    <div class="cart-widget-wrapper slide-down-wrapper">
        <div class="cart-widget slide-down--btn">
            <div class="cart-icon show-responsive" style="padding-right: 0;margin-right: 0">
                
                
                
                <i class="ion-bag" style="font-size: 19px;position:absolute;top:-5px"></i>
                <strong style="font-size: 13px"><span class="amount"><span class="currencySymbol"></span>@{{subtotal | currency}}</span></strong>
                <span class="cart-count-badge" style="top:0;float: right">
                    @{{items.length}}
                </span>
            </div>
            <div class="cart-icon hide-responsive">
                <i class="ion-bag"></i>
                <span class="cart-count-badge">
                    @{{items.length}}
                </span>
            </div>
            <div class="cart-text hide-responsive">
                <span class="d-block">Carrito</span>
                
            </div>
        </div>
        <div class="slide-down--item ">
            <div class="cart-widget-box">
                <ul class="cart-items">
                    <li class="single-cart" ng-repeat="item in items">
                        <a href="#" class="cart-product">
                            <div class="cart-product-img" ng-show="item.attributes.image">
                                <img ng-src="@{{item.attributes.image.file}}" alt="Selected Products">
                            </div>
                            <div class="product-details">
                                <h4 class="product-details--title">@{{item.name}}</h4>
                                <span class="product-details--price">@{{item.quantity}} x @{{item.priceWithConditions | currency}}</span> 
                            </div>
                            <span class="cart-cross" ng-click="deleteCartItem(item.id)">x</span>
                        </a>
                    </li>
                    <li class="single-cart">
                        <div class="cart-product__subtotal">
                            <span class="subtotal--title">Subtotal</span>
                            <span class="subtotal--price">@{{subtotal | currency}}</span>
                        </div>
                    </li>
                    <li class="single-cart">
                        <div class="cart-buttons">
                            <a href="javascript:;" ng-click="clearCart()" class="btn btn-outlined">Limpiar</a>
                            <a href="/checkout" class="btn btn-outlined">Pagar</a>
                        </div>
                    </li>
                </ul>

            </div>
        </div>
    </div>
</div>
