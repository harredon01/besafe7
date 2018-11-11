import {Injectable} from '@angular/core';

import {Api} from '../api/api';

@Injectable()
export class Ratings {

    constructor(public api: Api) {}

    getRatings(where: any) {
        let url = "/ratings";
        if (where) {
            url = url + "?" + where;
        }
        let seq = this.api.get(url).share();

        seq.subscribe((data: any) => {
            console.log("after get Ratings",data);
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
  postRating(accountInfo: any) {
    let seq = this.api.post('/ratings', accountInfo).share();

    seq.subscribe((res: any) => {
        console.log("after post Ratings",res);
        return res;
    }, err => {
      console.error('ERROR', err);
    });

    return seq;
  }

}
