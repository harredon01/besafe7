import 'rxjs/add/operator/toPromise';
import {Injectable} from '@angular/core';
import {Storage} from '@ionic/storage';
import {Address} from '../../models/address';
import {DatabaseService} from '../../providers/database-service/database-service';
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
export class OrderData {
    currentOrder: any;
    cartData: any;
    shippingAddress: Address;
    payerAddress: Address;
    buyerAddress: Address;
    payerInfo: any;
    paymentMethod: any;
    creditProduct: any;
    payment: any;
    payers: any[];


    constructor(public storage: Storage,
        public database: DatabaseService) {
        this.currentOrder = null;
        this.cartData = null;
        this.shippingAddress = null;
        this.payerAddress = null;
        this.payerInfo = null;
        this.buyerAddress = null;
        this.paymentMethod = null;
        this.creditProduct = null;
        this.payment = null;
        this.payers = [];
    }
    getStep2(method: any) {
        if (method == "CC") {
            return "CheckoutBuyerPage";
        }
        if (method == "Cash") {
            return "CheckoutCashPage";
        }
        if (method == "Banks") {
            return "CheckoutBanksPage";
        }
    }
    savePayer(order_id: any, user_id: any, email: any) {

        let query = "SELECT * FROM payers where order_id = ? and user_id = ? ";
        let params = [order_id, user_id]
        this.database.executeSql(query, params)
            .then((res: any) => {
                if (res.length == 0) {
                    let query = "INSERT INTO payers (order_id, user_id, email ) VALUES (?,?,?)";
                    let params = [order_id, user_id, email];
                    this.database.executeSql(query, params)
                        .then((res: any) => {
                            console.log("payer saved", res);
                        }, (err) => console.error(err));

                }

            }, (err) => console.error(err));
    }
    loadSavedPayers(order_id: any) {
        if (this.currentOrder) {
            this.clearOtherPayers(this.currentOrder.id);
        }

        let query = "SELECT * FROM payers where order_id = ? OR order_id is null ";
        //let query = "SELECT * FROM payers";
        let params = [order_id];
        //let params = [];
        this.payers = [];
        this.database.executeSql(query, params)
            .then((res: any) => {
                console.log("Loading payers", res);
                if (res) {
                    for (let i = 0; i < res.length; i++) {
                        console.log("Payer fetched", res[i]);
                        let container = {"user_id": res[i].user_id, "email": res[i].email};
                        this.payers.push(container);
                    }
                    let query = "UPDATE payers SET order_id = ? WHERE order_id is null ";
                    let params = [order_id]
                    this.database.executeSql(query, params);

                }
                console.log("Saved payers", this.payers);

            }, (err) => console.error(err));
    }
    clearOrderPayers(order_id: any) {

        let query = "DELETE FROM payers where order_id = ?  ";
        let params = [order_id]
        this.database.executeSql(query, params)
            .then((res: any) => {


            }, (err) => console.error(err));
    }
    clearOrder() {
        this.currentOrder = null;
        this.cartData = null;
        this.shippingAddress = null;
        this.payerAddress = null;
        this.buyerAddress = null;
        this.payerInfo = null;
        this.paymentMethod = null;
        this.creditProduct = null;
        this.payment = null;
        this.payers = [];
    }
    clearOtherPayers(order_id: any) {

        let query = "DELETE FROM payers where order_id <> ? and order_id is not null  ";
        let params = [order_id]
        this.database.executeSql(query, params)
            .then((res: any) => {


            }, (err) => console.error(err));
    }
}
