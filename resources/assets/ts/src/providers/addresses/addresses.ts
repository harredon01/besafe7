import {Injectable} from '@angular/core';

import {Api} from '../api/api';

@Injectable()
export class Addresses {

    constructor(public api: Api) {}

    getAddresses(where?: any) {
        let url = "/addresses";
        if (where) {
            url = url + "?" + where;
        }
        let seq = this.api.get(url).share();

        seq.subscribe((data: any) => {
            console.log("after get Addresses",data);
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
  saveAddress(address: any) {
    let seq = this.api.post('/addresses', address).share();

    seq.subscribe((res: any) => {
        console.log("after Save Address",res);
        return res;
    }, err => {
      console.error('ERROR', err);
    });

    return seq;
  }
  deleteAddress(address_id: string) {
    let seq = this.api.delete('/addresses/'+address_id ).share();

    seq.subscribe((res: any) => {
        console.log("after Delete address",res);
        return res;
    }, err => {
      console.error('ERROR', err);
    });

    return seq;
  }

}
