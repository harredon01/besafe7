import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams } from 'ionic-angular';
import { Address } from '../../models/address';
import { Items } from '../../providers/items/items';
import { Addresses } from '../../providers/addresses/addresses';

@IonicPage()
@Component({
    selector: 'page-address-detail',
    templateUrl: 'address-detail.html'
})
export class AddressDetailPage {
    product: Address;

    constructor(public navCtrl: NavController, navParams: NavParams, items: Items, products: Addresses) {
        let result = navParams.get('address');
        this.product = <Address>result;
    }

}
