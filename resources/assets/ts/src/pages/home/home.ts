import {Component} from '@angular/core';
import {TranslateService} from '@ngx-translate/core';
import {IonicPage, NavController, NavParams, ModalController, LoadingController,AlertController} from 'ionic-angular';
import {FoodProvider} from '../../providers/food/food';
import {Cart} from '../../providers/cart/cart';
import {OrderData} from '../../providers/orderdata/orderdata';

/**
 * Generated class for the HomePage page.
 *
 * See https://ionicframework.com/docs/components/#navigation for more info on
 * Ionic pages and navigation.
 */

@IonicPage()
@Component({
    selector: 'page-home',
    templateUrl: 'home.html',
})
export class HomePage {
    public itemList: any;
    loading: any;
    deliveriesGetString: string;
    deliveryWaitingTitle: string;
    deliveryWaitingDesc: string;
    constructor(
        public navCtrl: NavController,
        public navParams: NavParams,
        public food: FoodProvider,
        public alertCtrl: AlertController,
        public cartProvider: Cart,
        public modalCtrl: ModalController,
        public orderData: OrderData,
        public translateService: TranslateService,
        private loadingCtrl: LoadingController) {
        this.translateService.get('HOME.DELIVERIES_GET').subscribe((value) => {
            this.deliveriesGetString = value;
        });
        this.translateService.get('HOME.DELIVERY_WAITING_TITLE').subscribe((value) => {
            this.deliveryWaitingTitle = value;
        });
        this.translateService.get('HOME.DELIVERY_WAITING_DESC').subscribe((value) => {
            this.deliveryWaitingDesc = value;
        });
    }

    ionViewDidLoad() {
        console.log('ionViewDidLoad HomePage');
        this.getDeliveries();
        this.getCart();
    }

    getDeliveries() {
        this.showLoader();
        this.food.getDeliveryByDateTimeRange({init: '2018-09-04', end: '2018-09-05'}).subscribe((resp) => {
            this.itemList = resp["data"]
            console.log(this.itemList);
            this.loading.dismiss();
        }, (err) => {
            this.loading.dismiss();
        });
    }
    getCart() {
        this.cartProvider.getCart().subscribe((resp) => {
            if (resp) {
                console.log("getCart", resp);
                this.orderData.cartData = resp;
                this.navCtrl.parent.select(4);
            }
        }, (err) => {
            console.log("getCartError", err);
            this.orderData.cartData = null;
        });
    }

    selectDelivery(item) {
        if (item.status == "pending") {
            this.navCtrl.push("DeliveryProgramPage", {delivery: item});
        } else if (item.status == "transit") {
            this.navCtrl.parent.select(4);
        } else if (item.status == "delivered") {
            this.navCtrl.push("CommentsPage", {delivery: item});
        } else if (item.status == "enqueue") {
            this.showPrompt()
        }

    }
    showPrompt() {
        const prompt = this.alertCtrl.create({
            title: this.deliveryWaitingTitle,
            message: this.deliveryWaitingDesc,
            inputs: [],
            buttons: [
                {
                    text: 'OK',
                    handler: data => {
                    }
                }
            ]
        });
        prompt.present();
    }
    goToCheckout() {
        this.navCtrl.parent.select(4);
    }
    openCart() {
        let container = {cart: this.orderData.cartData};
        console.log("Opening Cart", container);
        let addModal = this.modalCtrl.create('CartPage', container);
        addModal.onDidDismiss(item => {
            if (item == "Checkout") {
                //console.log("User: ", this.userData._user);
                this.navCtrl.push('CheckoutShippingPage');
            }

        })
        addModal.present();
    }


    showLoader() {
        this.loading = this.loadingCtrl.create({
            content: this.deliveriesGetString
        });

        this.loading.present();
    }
}
