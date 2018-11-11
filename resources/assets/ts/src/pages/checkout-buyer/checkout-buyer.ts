import {Component} from '@angular/core';
import {TranslateService} from '@ngx-translate/core';
import {IonicPage, NavController, ToastController, LoadingController, ModalController} from 'ionic-angular';
import {Addresses} from '../../providers/addresses/addresses';
import {Address} from '../../models/address';
import {MapData} from '../../providers/mapdata/mapdata';
import {OrderData} from '../../providers/orderdata/orderdata';
import {User} from '../../providers';

@IonicPage()
@Component({
    selector: 'page-checkout-buyer',
    templateUrl: 'checkout-buyer.html'
})
export class CheckoutBuyerPage {
    // The account fields for the login form.
    // If you're using the username field with or without email, make
    // sure to add it to the type
    buyer: {
        buyer_address: string,
        buyer_city: string,
        buyer_state: string,
        buyer_country: string,
        buyer_postal: string,
        buyer_phone: string
    } = {
            buyer_address: '',
            buyer_city: '',
            buyer_state: '',
            buyer_country: '',
            buyer_postal: '',
            buyer_phone: '',
        };
    loading: any;
    showAddressCard: boolean;
    selectedAddress: Address;
    currentItems: Address[];

    private addressErrorString: string;

    constructor(public navCtrl: NavController,
        public user: User,
        public mapData: MapData,
        public orderData: OrderData,
        public modalCtrl: ModalController,
        public toastCtrl: ToastController,
        public translateService: TranslateService,
        public addresses: Addresses,
        private loadingCtrl: LoadingController) {
        this.showAddressCard = false;
        this.currentItems = [];
        console.log("checkout buyer");
        this.translateService.get('ADDRESS_FIELDS.ERROR_GET').subscribe((value) => {
            this.addressErrorString = value;
        });
    }

    saveBilling(item: Address) {
        this.buyer.buyer_address = item.address;
        this.buyer.buyer_city = item.cityName;
        this.buyer.buyer_state = item.regionName;
        this.buyer.buyer_country = item.countryName;
        this.buyer.buyer_postal = item.postal;
        this.buyer.buyer_phone = item.phone;
        this.orderData.buyerAddress = item;
        this.showAddressCard = true;
        this.selectedAddress = item;
        this.currentItems.push(item);
        this.navCtrl.push("CheckoutPayerPage", {
            items: this.currentItems
        });
    }

    showLoader() {
        this.loading = this.loadingCtrl.create({
            content: 'Estamos guardando tu dirección'
        });

        this.loading.present();
    }
    /**
       * The view loaded, let's query our items for the list
       */
    ionViewDidEnter() {
        this.showLoader();
        if (this.orderData.buyerAddress) {
            this.showAddressCard = true;
            this.selectedAddress = this.orderData.buyerAddress;
            this.loading.dismiss();
        } else {
            this.getAddresses();
        }
    }
    getAddresses() {
        this.addresses.getAddresses().subscribe((data: any) => {
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
    createAddress() {
        let container;
        container = {
            type: "billing"
        }
        let addModal = this.modalCtrl.create('AddressCreatePage', container);
        addModal.onDidDismiss(item => {
            if (item) {
                console.log("Process complete, address created", item);
                this.saveBilling(item);
            }
        })
        addModal.present();

    }
    continuePayer() {
        this.navCtrl.push("CheckoutPayerPage");
    }
    changeAddress() {
        if (this.currentItems.length == 0) {
            this.getAddresses();
        }
        this.showAddressCard = false;
    }
}
