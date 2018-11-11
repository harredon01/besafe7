import {Injectable} from '@angular/core';

import {Api} from '../api/api';

@Injectable()
export class Billing {

    constructor(public api: Api) {}

    payCreditCard(data: any) {
        let url = "/billing/pay_cc/PayU";
        let seq = this.api.post(url, data).share();

        seq.subscribe((data: any) => {
            console.log("after payCreditCard", data);
            return data;

            // If the API returned a successful response, mark the user as logged in
        }, err => {
            console.error('ERROR', err);
        });
        return seq;
    }

    payDebit(data: any) {
        let url = "/billing/pay_debit/PayU";
        let seq = this.api.post(url, data).share();

        seq.subscribe((data: any) => {
            console.log("after payDebit", data);
            return data;

            // If the API returned a successful response, mark the user as logged in
        }, err => {
            console.error('ERROR', err);
        });
        return seq;
    }

    payCash(data: any) {
        let url = "/billing/pay_cash/PayU";
        let seq = this.api.post(url, data).share();

        seq.subscribe((data: any) => {
            console.log("after payCash", data);
            return data;

            // If the API returned a successful response, mark the user as logged in
        }, err => {
            console.error('ERROR', err);
        });
        return seq;
    }
    getRawSources() {
        let url = "/billing/raw_sources/PayU";
        let seq = this.api.get(url).share();

        seq.subscribe((data: any) => {
            console.log("after getRawSources", data);
            return data;

            // If the API returned a successful response, mark the user as logged in
        }, err => {
            console.error('ERROR', err);
        });
        return seq;
    }
    getPayments(where) {
        let url = "/billing/payments";

        if (where) {
            url = url + "?" + where;
        }
        let seq = this.api.get(url).share();

        seq.subscribe((data: any) => {
            console.log("after getPayments", data);
            return data;

            // If the API returned a successful response, mark the user as logged in
        }, err => {
            console.error('ERROR', err);
        });
        return seq;
    }
    getBanks() {
        let url = "/payu/banks";
        let seq = this.api.get(url).share();

        seq.subscribe((data: any) => {
            console.log("after getBanks", data);
            return data;

            // If the API returned a successful response, mark the user as logged in
        }, err => {
            console.error('ERROR', err);
        });
        return seq;
    }
    getPaymentMethods() {
        let url = "/payu/payment_methods";
        let seq = this.api.get(url).share();

        seq.subscribe((data: any) => {
            console.log("after getPaymentMethods", data);
            return data;

            // If the API returned a successful response, mark the user as logged in
        }, err => {
            console.error('ERROR', err);
        });
        return seq;
    }

}
