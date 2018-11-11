import {Component} from '@angular/core';
import {TranslateService} from '@ngx-translate/core';
import {IonicPage, NavController, ToastController, LoadingController, NavParams} from 'ionic-angular';
import {Addresses} from '../../providers/addresses/addresses';
import {Address} from '../../models/address';
import {MapData} from '../../providers/mapdata/mapdata';
import {OrderData} from '../../providers/orderdata/orderdata';
import {Order} from '../../providers/order/order';
import {User} from '../../providers';

@IonicPage()
@Component({
    selector: 'page-checkout-shipping',
    templateUrl: 'checkout-shipping.html'
})
export class CheckoutShippingPage {
    // The account fields for the login form.
    // If you're using the username field with or without email, make
    // sure to add it to the type

    loading: any;
    showAddressCard: boolean;
    selectedAddress: Address;
    currentItems: Address[];

    private addressErrorString: string;
    private saveAddressErrorString: string;
    private addressGetStartString: string;
    private addressSaveStartString: string;

    constructor(public navCtrl: NavController,
        public user: User,
        public mapData: MapData,
        public toastCtrl: ToastController,
        public translateService: TranslateService,
        public navParams: NavParams,
        public addresses: Addresses,
        public order: Order,
        public orderData: OrderData,
        private loadingCtrl: LoadingController) {
        this.showAddressCard = false;
        this.currentItems = [];
        this.translateService.get('ADDRESS_FIELDS.ERROR_GET').subscribe((value) => {
            this.addressErrorString = value;
        });
        this.translateService.get('ADDRESS_FIELDS.ERROR_SAVE').subscribe((value) => {
            this.saveAddressErrorString = value;
        });
        this.translateService.get('ADDRESS_FIELDS.STARTING_GET').subscribe((value) => {
            this.addressGetStartString = value;
        });
        this.translateService.get('ADDRESS_FIELDS.STARTING_SAVE').subscribe((value) => {
            this.addressSaveStartString = value;
        });
    }

    saveShipping(item: Address) {
        console.log("SaveShipping", item);
        let container = {"address_id": item.id};
        this.showLoaderSave();
        this.order.setShippingAddress(container).subscribe((data: any) => {
            this.loading.dismiss();
            console.log("after get addresses");
            this.orderData.shippingAddress = item;
            this.showAddressCard = true;
            this.selectedAddress = item;
            this.orderData.currentOrder = data.order;
            this.orderData.loadSavedPayers(data.order.id);
            this.prepareOrder();
            console.log(JSON.stringify(data));
        }, (err) => {
            this.loading.dismiss();
            // Unable to log in
            let toast = this.toastCtrl.create({
                message: this.saveAddressErrorString,
                duration: 3000,
                position: 'top'
            });
            toast.present();
        });

        //        let container = {
        //            shipping_address: item.address,
        //            shipping_city: item.cityName,
        //            shipping_state: item.regionName,
        //            shipping_country: item.countryName,
        //            shipping_postal: item.postal,
        //            shipping_phone: item.phone
        //        };
        //        this.showAddressCard = true;
        //        this.orderData.shippingAddress = item;
        //this.navCtrl.push("CheckoutBuyerPage");
    }

    showLoader() {
        this.loading = this.loadingCtrl.create({
            content: this.addressGetStartString
        });

        this.loading.present();
    }
    showLoaderSave() {
        this.loading = this.loadingCtrl.create({
            content: this.addressSaveStartString
        });

        this.loading.present();
    }
    /**
       * The view loaded, let's query our items for the list
       */
    ionViewDidEnter() {
        this.showLoader();
        if (this.orderData.shippingAddress) {
            this.showAddressCard = true;
            this.selectedAddress = this.orderData.shippingAddress;
            this.loading.dismiss();
        } else {
            this.getAddresses();
        }
    }
    createAddress() {
        this.mapData.activeType = "Address";
        this.mapData.activeId = "0";
        this.navCtrl.parent.select(1);
        console.log("createAddress");
    }
    prepareOrder() {
        this.navCtrl.push("CheckoutPreparePage");
    }
    getAddresses() {
        let result = "type=shipping";
        this.addresses.getAddresses(result).subscribe((data: any) => {
            this.loading.dismiss();
            console.log("after get addresses");
            let results = data.addresses;
            for (let one in results) {
                let container = new Address(results[one]);
                this.currentItems.push(container);
            }
            //this.createAddress();
            console.log(JSON.stringify(data));
        }, (err) => {
            this.loading.dismiss();
            // Unable to log in
            let toast = this.toastCtrl.create({
                message: this.addressErrorString,
                duration: 3000,
                position: 'top'
            });
            toast.present();
        });
    }
    changeAddress() {
        if (this.currentItems.length == 0) {
            this.getAddresses();
        }
        this.showAddressCard = false;
    }
}
