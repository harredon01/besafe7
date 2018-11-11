import {Injectable} from '@angular/core';

import {Item} from '../../models/item';
import {Api} from '../api/api';

@Injectable()
export class Items {
    items: Item[] = [];
    total: any;
    totalItems: any;

    constructor(public api: Api) {}

    getCart() {
        return this.api.get('/cart/get');
    }
    getCheckoutCart() {
        return this.api.get('/cart/checkout');
    }

    addCartItem(item: any) {
        let seq = this.api.post('/cart/add', item).share();
        seq.subscribe((data: any) => {

            console.log("after add cart item");
            console.log(JSON.stringify(data));
            return data;
            // If the API returned a successful response, mark the user as logged in
        }, err => {
            console.error('ERROR', err);
        });

        return seq;
    }
    updateCartItem(item: any) {
        let seq = this.api.post('/cart/add', item).share();
        seq.subscribe((data: any) => {
            console.log("after updateCartItem");
            console.log(JSON.stringify(data));
            return data;
        }, err => {
            console.error('ERROR', err);
        });

        return seq;
    }

    clearCart() {
        let seq = this.api.post('/cart/clear', {}).share();
        seq.subscribe((data: any) => {

            console.log("after clearCart");
            console.log(JSON.stringify(data));
            return data;
        }, err => {
            console.error('ERROR', err);
        });

        return seq;
    }

}
