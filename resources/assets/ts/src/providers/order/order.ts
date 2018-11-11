import {Injectable} from '@angular/core';

import {Api} from '../api/api';

@Injectable()
export class Order {

    constructor(public api: Api) {}


    /**
      * Send a POST request to our signup endpoint with the data
      * the user entered on the form.
      */
    setShippingAddress(shipping: any) {
        let seq = this.api.post('/orders/shipping', shipping).share();
        seq.subscribe((res: any) => {
            console.log("after post addToCart", res);
            return res;
        }, err => {
            console.error('ERROR', err);
        });
        return seq;
    }
    /**
     * Send a POST request to our signup endpoint with the data
     * the user entered on the form.
     */
    setPayerAddress(shipping: any) {
        let seq = this.api.post('/orders/shipping', shipping).share();
        seq.subscribe((res: any) => {
            console.log("after post addToCart", res);
            return res;
        }, err => {
            console.error('ERROR', err);
        });
        return seq;
    }
    /**
     * Send a POST request to our signup endpoint with the data
     * the user entered on the form.
     */
    updateCart(accountInfo: any) {
        let seq = this.api.post('/cart/update', accountInfo).share();
        seq.subscribe((res: any) => {
            console.log("after post updateCart", res);
            return res;
        }, err => {
            console.error('ERROR', err);
        });
        return seq;
    }
    /**
     * Send a POST request to our signup endpoint with the data
     * the user entered on the form.
     */
    deleteCartItem(accountInfo: any) {
        let seq = this.api.post('/cart/update', accountInfo).share();
        seq.subscribe((res: any) => {
            console.log("after deleteCartItem", res);
            return res;
        }, err => {
            console.error('ERROR', err);
        });
        return seq;
    }
    /**
     * Send a POST request to our signup endpoint with the data
     * the user entered on the form.
     */
    clearCart(accountInfo: any) {
        let seq = this.api.post('/cart/clear', accountInfo).share();
        seq.subscribe((res: any) => {
            console.log("after post clearCart", res);
            return res;
        }, err => {
            console.error('ERROR', err);
        });
        return seq;
    }

    /**
     * Send a POST request to our signup endpoint with the data
     * the user entered on the form.
     */
    prepareOrder(accountInfo: any) {
        let seq = this.api.post('/orders/prepare/food', accountInfo).share();
        seq.subscribe((res: any) => {
            console.log("after post prepareOrder", res);
            return res;
        }, err => {
            console.error('ERROR', err);
        });
        return seq;
    }
    /**
     * Send a POST request to our signup endpoint with the data
     * the user entered on the form.
     */
    setDiscounts(order: any) {
        let seq = this.api.post('/orders/discounts/food/'+order, {}).share();
        seq.subscribe((res: any) => {
            console.log("after post setDiscounts", res);
            return res;
        }, err => {
            console.error('ERROR', err);
        });
        return seq;
    }

}
