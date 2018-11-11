import {Injectable} from '@angular/core';

import {Api} from '../api/api';

@Injectable()
export class Deliveries {

    constructor(public api: Api) {}

    getDeliveries(where?: any) {
        let url = "/deliveries";
        if (where) {
            url = url + "?" + where;
        }
        let seq = this.api.get(url).share();

        seq.subscribe((data: any) => {
            console.log("after get Deliveries",data);
            return data;

            // If the API returned a successful response, mark the user as logged in
        }, err => {
            console.error('ERROR', err);
        });
        return seq;
    }

}
