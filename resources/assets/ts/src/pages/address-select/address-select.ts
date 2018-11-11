import {Component} from '@angular/core';
import {IonicPage, ViewController, NavParams, LoadingController,ToastController} from 'ionic-angular';
import {TranslateService} from '@ngx-translate/core';
import {Address} from '../../models/address';
import {Addresses} from '../../providers/addresses/addresses';
@IonicPage()
@Component({
    selector: 'page-address-select',
    templateUrl: 'address-select.html'
})
export class AddressSelectPage {
    item: any;
    loading: any;
    currentItems: Address[];
    private addressErrorString: string;

    constructor(
        public viewCtrl: ViewController,
        public toastCtrl: ToastController,
        public addresses: Addresses,
        public translateService: TranslateService,
        public navParams: NavParams,
        private loadingCtrl: LoadingController) {
        this.translateService.get('ADDRESS_FIELDS.ERROR_GET').subscribe((value) => {
            this.addressErrorString = value;
        });

    }
    showLoader() {
        
        this.loading = this.loadingCtrl.create({
            content: 'Estamos obteniendo tus direcciones'
        });

        this.loading.present();
    }
/**
     * The view loaded, let's query our items for the list
     */
    ionViewDidEnter() {
        this.showLoader();
        let result = "type="+this.navParams.get('type');
        this.addresses.getAddresses(result).subscribe((data: any) => {
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


    /**
     * The user cancelled, so we dismiss without sending data back.
     */
    cancel() {
        this.viewCtrl.dismiss();
    }

    /**
     * The user is done and wants to create the item, so return it
     * back to the presenter.
     */
    done(item:Address) {
        this.viewCtrl.dismiss(item);
    }
}
