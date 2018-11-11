import 'rxjs/add/operator/toPromise';

import {Injectable} from '@angular/core';
import {Api} from '../api/api';
import {UserData} from '../userdata/userdata';
import {OneSignal} from '@ionic-native/onesignal';

/**
 * Most apps have the concept of a User. This is a simple provider
 * with stubs for login/signup/etc.
 *
 * This User provider makes calls to our API at the `login` and `signup` endpoints.
 *
 * By default, it expects `login` and `signup` to return a JSON object of the shape:
 *
 * ```json
 * {
 *   status: 'success',
 *   user: {
 *     // User fields your app needs, like "id", "name", "email", etc.
 *   }
 * }Ø
 * ```
 *
 * If the `status` field is not `success`, then an error is detected and returned.
 */
@Injectable()
export class User {
    _user: any;

    constructor(public api: Api,
        public userData: UserData,
        private oneSignal: OneSignal) {

    }

    /**
     * Send a POST request to our login endpoint with the data
     * the user entered on the form.
     */
    login(accountInfo: any) {
        accountInfo.client_id = 1;
        accountInfo.client_secret = "nuoLagU2jqmzWqN6zHMEo82vNhiFpbsBsqcs2DPt";
        accountInfo.grant_type = "password";
        accountInfo.scope = "*";
        let seq = this.api.post('/oauth/token', accountInfo).share();

        seq.subscribe((data: any) => {

            console.log("after auth");
            console.log(JSON.stringify(data));
            // If the API returned a successful response, mark the user as logged in
            if (data.access_token) {
                this._loggedIn(data, accountInfo);
            } else {
            }
        }, err => {
            console.error('ERROR', err);
        });

        return seq;
    }
    saveTokenServer() {
        this.oneSignal.getIds().then((ids) => {
            console.log('platform: food  getIds: ' + JSON.stringify(ids));
            let token = {
                "platform": "food",
                "token": ids.userId
            }
            this.registerToken(token);
        });
    }
    /**
     * Send a POST request to our login endpoint with the data
     * the user entered on the form.
     */
    getUser() {

        let seq = this.api.get('/user').share();

        seq.subscribe((data: any) => {

            console.log("after get user");
            console.log(JSON.stringify(data));
            return data;
        }, err => {
            console.error('ERROR', err);
        });

        return seq;
    }

    /**
     * Send a POST request to our login endpoint with the data
     * the user entered on the form.
     */
    checkCredits(credits: any) {

        let seq = this.api.post('/user/credits', credits).share();

        seq.subscribe((data: any) => {

            console.log("after checkcredits");
            console.log(JSON.stringify(data));
            return data;
        }, err => {
            console.error('ERROR', err);
        });

        return seq;
    }

    /**
     * Send a POST request to our login endpoint with the data
     * the user entered on the form.
     */
    getUserByEmail(email: any) {
        let seq = this.api.get('/contact/email/' + email).share();
        seq.subscribe((data: any) => {
            console.log("after getUserByEmail");
            console.log(JSON.stringify(data));
            return data;
        }, err => {
            console.error('ERROR', err);
        });
        return seq;
    }

    /**
     * Send a POST request to our login endpoint with the data
     * the user entered on the form.
     */
    updatePassword(passwordData: any) {
        let seq = this.api.post('/user/change_password', passwordData).share();
        seq.subscribe((data: any) => {
            console.log("after update password");
            console.log(JSON.stringify(data));
            return data;
        }, err => {
            console.error('ERROR', err);
        });

        return seq;
    }
    /**
         * Send a POST request to our signup endpoint with the data
         * the user entered on the form.
         */
    registerToken(token: any) {
        let seq = this.api.post('/user/token', token).share();

        seq.subscribe((res: any) => {
            // If the API returned a successful response, mark the user as logged in
            if (res.status == 'success') {
                console.log("Push notifications token successfully stored");
            }
        }, err => {
            console.error('ERROR', err);
        });

        return seq;
    }
    /**
     * Send a POST request to our signup endpoint with the data
     * the user entered on the form.
     */
    signup(accountInfo: any) {
        let seq = this.api.post('/auth/register', accountInfo).share();

        seq.subscribe((res: any) => {
            // If the API returned a successful response, mark the user as logged in
            if (res.status == 'success') {
                this._loggedIn(res, accountInfo);
            }
        }, err => {
            console.error('ERROR', err);
        });

        return seq;
    }
    /**
     * Send a POST request to our signup endpoint with the data
     * the user entered on the form.
     */
    myAccount(accountInfo: any) {
        let seq = this.api.post('/user', accountInfo).share();

        seq.subscribe((res: any) => {
            // If the API returned a successful response, mark the user as logged in
            if (res.status == 'success') {
                this._loggedIn(res, accountInfo);
            }
        }, err => {
            console.error('ERROR', err);
        });

        return seq;
    }

    /**
     * Log the user out, which forgets the session
     */
    logout() {
        this._user = null;
    }

    /**
       * Process a login/signup response to store user data
       */
    _loggedIn(resp, accountInfo) {
        this._user = resp.user;
        console.log("accountInfo.remember", accountInfo.remember);
        console.log("token", resp.access_token);
        this.userData.setToken(resp.access_token);
        if (accountInfo.remember) {

            this.userData.setUsername(accountInfo.username);
            this.userData.setPassword(accountInfo.password);
            this.userData.setRemember(accountInfo.remember);
        }
        this.saveTokenServer();

    }
}
