import 'rxjs/add/operator/toPromise';

import {Injectable} from '@angular/core';
import {Address} from '../../models/address';


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
export class MapData {
    map: any;
    activeType: string;
    activeId: string;
    activeObject: string;
    reports: any[];
    merchants: any[];
    shared: any[];
    objects: any[];
    polygons: any[];
    meMarker: any;
    address:Address;
    newReportMarker: any;
    newAddressMarker: any;

    constructor() {
        this.map = null;
        this.address = new Address({});
        this.meMarker = null;
        this.newReportMarker = null;
        this.newAddressMarker = null;
        this.reports = [];
        this.polygons = [];
        this.merchants = [];
        this.shared = [];
        this.objects = [];
    }
    /**
     * prepares sharer data for creating a marker
     */
    hideMarkerList(typeMarker: string) {
        let hidingList = this[typeMarker];
        for (let item in hidingList) {
            let cont = hidingList[item];
            cont.setMap(null);
        }
    }
    hideAll() {
        this.hideMarkerList("Reports");
        this.hideMarkerList("Merchants");
        this.hideMarkerList("Shared");
        this.hideMarkerList("Objects");
    }
    showAll() {
        this.showMarkerList("Reports");
        this.showMarkerList("Merchants");
        this.showMarkerList("Shared");
        this.showMarkerList("Objects");
    }
    /**
     * prepares sharer data for creating a marker
     */
    showMarkerList(typeMarker: string) {
        typeMarker = typeMarker.toLowerCase();
        let hidingList = this[typeMarker];
        for (let item in hidingList) {
            let cont = hidingList[item];
            cont.setMap(this.map);
        }
    }
    addItem(item: any, typeMarker: string) {
        typeMarker = typeMarker.toLowerCase();
        let container = this[typeMarker];
        try {
            container.push(item);
        } catch {
            console.log("item not supported", typeMarker);
        }
    }
    deleteItem(item: any, typeMarker: string) {
        typeMarker = typeMarker.toLowerCase();
        let container = this[typeMarker];
        try {
            container.splice(container.indexOf(item), 1);
        } catch {
            console.log("item not supported", typeMarker);
        }
    }
    getItem(item_id: any, typeMarker: string) {
        typeMarker = typeMarker.toLowerCase();
        let container = this[typeMarker];
        try {
            for (let marker in container) {
                let cont = container[marker];
                if (item_id == cont.get("id")) {
                    return cont;
                }

            }
            return null;
        } catch {
            console.log("item not supported", typeMarker);
        }
        return null;
    }
    getItemUser(user_id: any, typeMarker: string) {
        typeMarker = typeMarker.toLowerCase();
        let container = this[typeMarker];
        try {
            for (let marker in container) {
                let cont = container[marker];
                if (user_id == cont.get("user_id")) {
                    return cont;
                }

            }
            return null;
        } catch {
            console.log("item not supported", typeMarker);
        }
        return null;
    }

    addMap(item: any) {
        this.map = item;
    }

    getMap() {
        return this.map;
    }


    /**
     * Send a POST request to our signup endpoint with the data
     * the user entered on the form.
     */
    //  signup(accountInfo: any) {
    //    let seq = this.api.post('signup', accountInfo).share();
    //
    //    seq.subscribe((res: any) => {
    //      // If the API returned a successful response, mark the user as logged in
    //      if (res.status == 'success') {
    //        this._loggedIn(res);
    //      }
    //    }, err => {
    //      console.error('ERROR', err);
    //    });
    //
    //    return seq;
    //  }

    /**
     * Log the user out, which forgets the session
     */
    //  logout() {
    //    this._user = null;
    //  }

    /**
     * Process a login/signup response to store user data
     */
    //  _loggedIn(resp) {
    //    this._user = resp.user;
    //  }
}
