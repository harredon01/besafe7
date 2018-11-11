import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams } from 'ionic-angular';
import { Product } from '../../models/product';
import { Items } from '../../providers/items/items';
import { Products } from '../../providers/products/products';

@IonicPage()
@Component({
    selector: 'page-product-detail',
    templateUrl: 'product-detail.html'
})
export class ProductDetailPage {
    product: Product;

    constructor(public navCtrl: NavController, navParams: NavParams, items: Items, products: Products) {
        let result = navParams.get('product');
        this.product = <Product>result;
    }

}
