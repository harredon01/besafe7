import {Component} from '@angular/core';
import {TranslateService} from '@ngx-translate/core';
import {IonicPage, NavController, ToastController, LoadingController, ModalController, NavParams} from 'ionic-angular';
import {Item} from '../../models/item';
import {OrderData} from '../../providers/orderdata/orderdata';

import {Cart} from '../../providers/cart/cart';
import {Order} from '../../providers/order/order';

@IonicPage()
@Component({
    selector: 'page-checkout-prepare',
    templateUrl: 'checkout-prepare.html'
})
export class CheckoutPreparePage {
    // The account fields for the login form.
    // If you're using the username field with or without email, make
    // sure to add it to the type
    loading: any;
    showPayment: boolean;
    showPayers: boolean;
    currentItems: Item[];
    conditions: any[];
    payers: any[];
    // Our translated text strings
    private cartErrorString: string;
    public totalItems: any;
    public subtotal: any;
    public total: any;
    split: any;

    private prepareOrderErrorString: string;
    private prepareOrderStartingString: string;
    private prepareOrderSuccessString: string;


    constructor(public navCtrl: NavController,
        public cartProvider: Cart,
        public orderData: OrderData,

        public orderProvider: Order,
        public navParams: NavParams,
        public modalCtrl: ModalController,
        public toastCtrl: ToastController,
        public translateService: TranslateService,
        private loadingCtrl: LoadingController) {
        this.showPayment = false;
        this.showPayers = false;
        this.currentItems = [];
        this.conditions = [];
        this.payers = [];
        if (this.orderData.payers.length > 0) {
            this.payers = this.orderData.payers;
            this.showPayers = true; 
        }
        console.log("payers", this.payers);

        this.translateService.get('CHECKOUT_PREPARE.PREPARE_ORDER_ERROR').subscribe((value) => {
            this.prepareOrderErrorString = value;
        });
        this.translateService.get('CHECKOUT_PREPARE.PREPARE_ORDER_STARTING').subscribe((value) => {
            this.prepareOrderStartingString = value;
        });
        this.translateService.get('CHECKOUT_PREPARE.PREPARE_ORDER_SUCCESS').subscribe((value) => {
            this.prepareOrderSuccessString = value;
        });
        this.setDiscounts();
    }

    selectPaymentOption(option: any) {
        this.orderData.paymentMethod = option;

        let nextPage = this.orderData.getStep2(option);
        this.navCtrl.push(nextPage);
    }
    prepareOrder() {
        this.showLoader();
        this.showPayment = false;
        let payers = [];
        let payersContainer = this.orderData.payers;
        for (let item in payersContainer) {
            payers.push(payersContainer[item].user_id);
        }
        let container = {
            "order_id": this.orderData.currentOrder.id,
            "payers": payers,
            "split_order": this.split
        };
        this.orderProvider.prepareOrder(container).subscribe((resp: any) => {
            if (resp) {
                if (resp.status == "success") {
                    this.loading.dismiss();
                    console.log("orderProvider", resp);
                    this.orderData.payment = resp.payment;
                    this.showPayment = true;
                }

            }
            if (!this.showPayment) {
                this.loading.dismiss();
                // Unable to log in
                let toast = this.toastCtrl.create({
                    message: this.prepareOrderErrorString,
                    duration: 3000,
                    position: 'top'
                });
                toast.present();
            }
        }, (err) => {
            console.log("getCartError", err);
            this.orderData.cartData = null;
        });
    }
    setDiscounts() {
        this.orderProvider.setDiscounts(this.orderData.currentOrder.id).subscribe((resp: any) => {
            if (resp) {
                console.log("setDiscounts", resp);
                this.orderData.payment = resp.payment;
                this.getCart();
            }
        }, (err) => {
            console.log("getCartError", err);
            this.orderData.cartData = null;
        });
    }

    getCart() {
        this.cartProvider.getCheckoutCart().subscribe((resp) => {
            if (resp) {
                console.log("getCheckoutCart", resp);
                this.orderData.cartData = resp;
                let cartContainer = this.orderData.cartData;
                console.log("cartContainer", cartContainer);
                let results = cartContainer.items;
                this.totalItems = cartContainer.totalItems;
                this.subtotal = cartContainer.subtotal;
                this.total = cartContainer.total;
                this.conditions = cartContainer.conditions;
                this.currentItems = [];
                for (let index = 0; index < results.length; ++index) {
                    console.log(results[index]);
                    if (results[index].attributes.image) {
                        results[index].image = results[index].attributes.image.file;
                    }

                    let itemAdd = new Item(results[index]);
                    this.currentItems.push(itemAdd);
                }
                console.log("Cart items", this.currentItems);
            }
        }, (err) => {
            console.log("getCartError", err);
            this.orderData.cartData = null;
        });
    }

    showLoader() {
        this.loading = this.loadingCtrl.create({
            content: this.prepareOrderStartingString
        });

        this.loading.present();
    }
}
