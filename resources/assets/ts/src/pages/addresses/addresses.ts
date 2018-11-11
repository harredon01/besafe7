import {Component} from '@angular/core';
import {TranslateService} from '@ngx-translate/core';
import {IonicPage, NavController, ToastController, LoadingController, AlertController, ModalController} from 'ionic-angular';

import {Address} from '../../models/address';
import {Addresses} from '../../providers/addresses/addresses';
import {MapData} from '../../providers/mapdata/mapdata';
import {ILatLng} from '@ionic-native/google-maps';

@IonicPage()
@Component({
    selector: 'page-addresses',
    templateUrl: 'addresses.html'
})
export class AddressesPage {
    currentItems: Address[];
    private addressErrorString: string;
    private addressErrorStringSave: string;
    loading: any;

    constructor(public navCtrl: NavController,
        public addresses: Addresses,
        public toastCtrl: ToastController,
        public modalCtrl: ModalController,
        public mapData: MapData,
        public translateService: TranslateService,
        public alertCtrl: AlertController,
        private loadingCtrl: LoadingController) {
        this.currentItems = [];
        this.translateService.get('ADDRESS_FIELDS.ERROR_GET').subscribe((value) => {
            this.addressErrorString = value;
        });
        this.translateService.get('ADDRESS_FIELDS.ERROR_SAVE').subscribe((value) => {
            this.addressErrorStringSave = value;
        });

    }

    showPrompt() {
        const prompt = this.alertCtrl.create({
            title: 'Nueva dirección',
            message: "Dirección de envío o correspondencia", 
            inputs: [],
            buttons: [
                {
                    text: 'Correspondencia',
                    handler: data => {
                        this.addBillingAddress();
                    }
                },
                {
                    text: 'Envío',
                    handler: data => {
                        this.addShippingAddress();
                    }
                }
            ]
        });
        prompt.present();
    }

    showLoader() {
        this.loading = this.loadingCtrl.create({
            content: 'Estamos obteniendo tus direcciones'
        });

        this.loading.present();
    }
    showLoaderSave() {
        this.loading = this.loadingCtrl.create({
            content: 'Estamos guardando tus cambios'
        });

        this.loading.present();
    }

    /**
     * The view loaded, let's query our items for the list
     */
    ionViewDidEnter() {
        this.showLoader();
        this.addresses.getAddresses().subscribe((data: any) => {
            this.loading.dismiss();
            console.log("after get addresses");
            let results = data.addresses;
            for (let one in results) {
                let container = new Address(results[one])
                this.currentItems.push(container);
            }
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
    addBillingAddress() {
        console.log("completeAddressData", this.mapData.address);
        let container;
        container = {
            lat: "",
            long: "",
            address: "",
            id: "",
            phone: "",
            name: "",
            postal: "",
            type: "billing"
        }
        let addModal = this.modalCtrl.create('AddressCreatePage', container);
        addModal.onDidDismiss(item => {
            if (item) {
                console.log("Process complete, address created", item);
                let container = new Address(item);
                this.currentItems.push(container);
                
            }
        })
        addModal.present();
    }

    /**
     * Prompt the user to add a new item. This shows our ItemCreatePage in a
     * modal and then adds the new item to our data source if the user created one.
     */
    addShippingAddress() {
        this.mapData.activeType = "Address";
        this.mapData.activeId = "-1";
        this.navCtrl.parent.select(1);
    }


    /**
     * Delete an item from the list of items.
     */
    deleteAddress(item) {

        this.addresses.deleteAddress(item.id).subscribe((resp: any) => {
            this.loading.dismiss();
            if (resp.status == "success") {
                this.currentItems.splice(this.currentItems.indexOf(item), 1);
            }

            //this.navCtrl.push(MainPage);
        }, (err) => {
            this.loading.dismiss();
            // Unable to log in
            let toast = this.toastCtrl.create({
                message: this.addressErrorStringSave,
                duration: 3000,
                position: 'top'
            });
            toast.present();
        });
    }

    /**
     * Navigate to the detail page for this item.
     */
    openAddress(address: Address) {
        this.navCtrl.push('AddressDetailPage', {
            address: address
        });
    }
    /**
     * Navigate to the detail page for this item.
     */
    editAddress(address: Address) {
        if (address.type == "shipping") {
            this.mapData.activeType = "Address";
            this.mapData.activeId = "1";
            let position: ILatLng = {
                lat: address.lat,
                lng: address.long
            };
            this.mapData.newAddressMarker.setPosition(position);
            this.mapData.address = address;
            this.navCtrl.parent.select(1);
        } else {
            console.log("completeAddressData", this.mapData.address);
            let container;
            container = {
                lat: address.lat,
                long: address.long,
                address: address.address,
                id: address.id,
                phone: address.phone,
                name: address.name,
                postal: address.postal,
                type: "billing"
            }
            let addModal = this.modalCtrl.create('AddressCreatePage', container);
            addModal.onDidDismiss(item => {
                if (item) {
                    this.currentItems.splice(this.currentItems.indexOf(address), 1);
                    this.currentItems.push(item);
                    console.log("Process complete, address created", item);
                }
            })
            addModal.present();
        }


    }
}
