import {Component} from '@angular/core';
import {TranslateService} from '@ngx-translate/core';
import {IonicPage, ModalController, NavController, ToastController, ViewController, Events} from 'ionic-angular';

import {Item} from '../../models/item';
import {Cart} from '../../providers/cart/cart';
import {OrderData} from '../../providers/orderdata/orderdata';

@IonicPage()
@Component({
    selector: 'page-cart',
    templateUrl: 'cart.html'
})
export class CartPage {
    currentItems: Item[];
    // Our translated text strings
    private cartErrorString: string; 
    public totalItems: any;
    public subtotal: any;
    public total: any;

    constructor(public navCtrl: NavController,
        public cart: Cart,
        public events: Events,
        public modalCtrl: ModalController, 
        public orderData: OrderData,
        public viewCtrl: ViewController,
        public toastCtrl: ToastController,
        public translateService: TranslateService) {

        this.translateService.get('CART.ERROR_UPDATE').subscribe((value) => {
            this.cartErrorString = value;
        });
        this.loadCart();

    }

    /**
     * The view loaded, let's query our items for the list
     */
    ionViewDidLoad() {
    }

    /**
     * Prompt the user to add a new item. This shows our ItemCreatePage in a
     * modal and then adds the new item to our data source if the user created one.
     */
    updateCartItem(amount: any, product_id: any, item_id: any) {
        let container = {
            product_id: product_id,
            amount: amount,
            item_id: item_id
        };
        let item = new Item(container);
        this.cart.updateCartItem(item).subscribe((resp: any) => {
            this.orderData.cartData = resp.cart;
            this.loadCart();
            //this.navCtrl.push(MainPage);
        }, (err) => {
            //this.navCtrl.push(MainPage);
            // Unable to log in
            let toast = this.toastCtrl.create({
                message: this.cartErrorString,
                duration: 3000,
                position: 'top'
            });
            toast.present();
        });
    }

    /**
     * Delete an item from the list of cart.
     */
    deleteItem(item) {
        item.amount = 0;
        this.cart.updateCartItem(item).subscribe((resp: any) => {
            this.orderData.cartData = resp.cart;
            this.loadCart();
            this.events.publish('cart:deleteItem', item, Date.now());
            //this.navCtrl.push(MainPage);
        }, (err) => {
            //this.navCtrl.push(MainPage);
            // Unable to log in
            let toast = this.toastCtrl.create({
                message: this.cartErrorString,
                duration: 3000,
                position: 'top'
            });
            toast.present();
        });
    }
    /**
     * Delete an item from the list of cart.
     */
    loadCart() {
        this.currentItems = [];
        if (this.orderData.cartData) {
            let cartContainer = this.orderData.cartData;
            console.log("cartContainer", cartContainer);
            let results = cartContainer.items;
            this.totalItems = cartContainer.totalItems;
            this.subtotal = cartContainer.subtotal;
            this.total = cartContainer.total;
            for (let index = 0; index < results.length; ++index) {
                console.log(results[index]);
                if (results[index].attributes.image) {
                    results[index].image = results[index].attributes.image.file;
                }

                let itemAdd = new Item(results[index]);
                this.currentItems.push(itemAdd);
            }
            console.log("Cart items", this.currentItems);
        } else {
            this.totalItems = 0;
            this.subtotal = 0;
            this.total = 0;
        }

    }
    /**
     * Delete an item from the list of cart.
     */
    clearCart() {
        this.cart.clearCart().subscribe((resp) => {
            this.orderData.cartData.items = [];
            this.orderData.cartData.total = 0;
            this.orderData.cartData.subtotal = 0;
            this.orderData.cartData.totalItems = 0;
            this.loadCart();
            this.events.publish('cart:clear');
            //this.navCtrl.push(MainPage);
        }, (err) => {
            //this.navCtrl.push(MainPage);
            // Unable to log in
            let toast = this.toastCtrl.create({
                message: this.cartErrorString,
                duration: 3000,
                position: 'top'
            });
            toast.present();
        });
    }

    /**
     * Navigate to the detail page for this item.
     */
    checkout() {
        this.viewCtrl.dismiss("Checkout");



    }

    /**
     * The user is done and wants to create the item, so return it
     * back to the presenter.
     */
    done() {
        this.viewCtrl.dismiss("Close");
    }
}
