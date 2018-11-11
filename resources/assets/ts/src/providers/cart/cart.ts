import {Injectable} from '@angular/core';

import {Api} from '../api/api';

@Injectable()
export class Cart {

    constructor(public api: Api) {}

    getCart() {
        let url = "/cart/get";
        let seq = this.api.get(url).share();

        seq.subscribe((data: any) => {
            console.log("after getCart",data);
            return data;

            // If the API returned a successful response, mark the user as logged in
        }, err => {
            console.error('ERROR', err);
        });
        return seq;
    }
    
    getCheckoutCart() {
        let url = "/cart/checkout";
        let seq = this.api.get(url).share();

        seq.subscribe((data: any) => {
            console.log("after getCart",data);
            return data;

            // If the API returned a successful response, mark the user as logged in
        }, err => {
            console.error('ERROR', err);
        });
        return seq;
    }

 /**
   * Send a POST request to our signup endpoint with the data
   * the user entered on the form.
   */
  addCartItem(item: any) {
    let seq = this.api.post('/cart/add', item).share();
    seq.subscribe((res: any) => {
        console.log("after post addCartItem",res);
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
  updateCartItem(item: any) {
    let seq = this.api.post('/cart/update', item).share();
    seq.subscribe((res: any) => {
        console.log("after post updateCart",res);
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
  deleteCartItem(item: any) {
    let seq = this.api.post('/cart/update', item).share();
    seq.subscribe((res: any) => {
        console.log("after deleteCartItem",res);
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
  clearCart() {
    let seq = this.api.post('/cart/clear', {}).share();
    seq.subscribe((res: any) => {
        console.log("after post clearCart",res);
        return res;
    }, err => {
      console.error('ERROR', err);
    });
    return seq;
  }

}
