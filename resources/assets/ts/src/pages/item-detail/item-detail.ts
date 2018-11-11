import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams } from 'ionic-angular';
import { Item } from '../../models/item';
import { Items } from '../../providers/items/items';

@IonicPage()
@Component({
    selector: 'page-item-detail',
    templateUrl: 'item-detail.html'
})
export class ItemDetailPage {
    item: Item;

    constructor(public navCtrl: NavController, navParams: NavParams, items: Items) {
        let result = navParams.get('item');
        this.item = <Item>result;
    }

}
