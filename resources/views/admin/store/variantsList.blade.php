<div>
    Variants<br><br>
    <ul>
        <li id="item-@{{ item.id}}" ng-repeat="item in items">
            Id: <span class="type">@{{ item.id}}</span><br/>
            Merchants:
            <ul>
                <li id="item-@{{ item.id}}" ng-repeat="point in item.product.merchants">@{{ point.name }}</li>
            </ul>
            product: <span class="type">@{{ item.product.name}}</span><br/>
            sku: <span class="type">@{{ item.sku}}</span><br/>
            ref2: <span class="type">@{{ item.ref2}}</span><br/>
            type: <span class="type">@{{ item.type}}</span><br/>
            description: <span class="type">@{{ item.description}}</span><br/>
            is_digital: <span class="type">@{{ item.is_digital}}</span><br/>
            is_on_sale: <span class="type">@{{ item.is_on_sale}}</span><br/>
            is_shippable: <span class="type">@{{ item.is_shippable}}</span><br/>
            price: <span class="type">@{{ item.price}}</span><br/>
            sale: <span class="type">@{{ item.sale}}</span><br/>
            tax: <span class="type">@{{ item.tax}}</span><br/>
            quantity: <span class="type">@{{ item.quantity}}</span><br/>
            requires_authorization: <span class="type">@{{ item.requires_authorization}}</span><br/>
            attributes: <span class="type">@{{ item.attributes}}</span><br/>
            
            <br/><a href="javascript:;" ng-click="editDish(item)" class="editar">Editar</a>
        </li>
        <li ng-show="loadMore">
            <button ng-click="getItems()">Cargar mas</button>
        </li>
    </ul>
</div>
<div>
    
</div>


