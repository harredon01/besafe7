import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams } from 'ionic-angular';
import { Delivery } from '../../models/delivery';


@IonicPage()
@Component({
    selector: 'page-delivery-detail',
    templateUrl: 'delivery-detail.html'
})
export class DeliveryDetailPage {
    item: Delivery;

    constructor(public navCtrl: NavController, navParams: NavParams) {
        let result = navParams.get('item');
        this.item = <Delivery>result;
    }

}
