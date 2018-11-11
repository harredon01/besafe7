import {Component} from '@angular/core';
import {FormBuilder, FormGroup, Validators} from '@angular/forms';
import {IonicPage, NavController, ViewController, NavParams, LoadingController, ToastController} from 'ionic-angular';
import {TranslateService} from '@ngx-translate/core';
import {Addresses} from '../../providers/addresses/addresses';
@IonicPage()
@Component({
    selector: 'page-address-create',
    templateUrl: 'address-create.html'
})
export class AddressCreatePage {
    isReadyToSave: boolean; 
    item: any;
    form: FormGroup;
    private addressErrorStringSave: string;
    loading: any;

    constructor(public navCtrl: NavController,
        public viewCtrl: ViewController,
        formBuilder: FormBuilder,
        public toastCtrl: ToastController,
        public addresses: Addresses,
        public translateService: TranslateService,
        public navParams: NavParams,
        private loadingCtrl: LoadingController) {
        this.translateService.get('ADDRESS_FIELDS.ERROR_SAVE').subscribe((value) => {
            this.addressErrorStringSave = value;
        });
        this.form = formBuilder.group({
            address: ['', Validators.required],
            postal: ['', Validators.required],
            address_id: [''],
            city_id: ['', Validators.required],
            region_id: ['', Validators.required],
            country_id: ['', Validators.required],
            phone: ['', Validators.required],
            name: ['', Validators.required],
            lat: [''],
            long: [''],
            type: ['', Validators.required],
        });
        let address_id: string = navParams.get('id');
        if (address_id) {
            let container = {
                city_id: 524,
                region_id: 11,
                country_id: 1,
                address_id: address_id,
                address: navParams.get('address'),
                postal: navParams.get('postal'),
                phone: navParams.get('phone'),
                name: navParams.get('name'),
                lat: navParams.get('lat'),
                long: navParams.get('long'),
                type: navParams.get('type'),
            };
            console.log("Setting form values: ", container);
            this.isReadyToSave = true;
            this.form.setValue(container);

        } else {
            let address = "";
            if (navParams.get('address')) {
                address = navParams.get('address');
            }
            let postal = "";
            if (navParams.get('postal')) {
                postal = navParams.get('postal');
            }
            let lat = "";
            if (navParams.get('lat')) {
                lat = navParams.get('lat');
            }
            let long = "";
            if (navParams.get('long')) {
                long = navParams.get('long');
            }
            let container = {
                city_id: 524,
                region_id: 11,
                country_id: 1,
                address_id: "",
                address: address,
                postal: postal,
                lat: lat,
                phone: "",
                name: "",
                long: long,
                type: navParams.get('type'),
            };
            console.log("Setting form values2: ", container);
            this.form.setValue(container);
        }


        // Watch the form for changes, and
        this.form.valueChanges.subscribe((v) => {
            console.log("form change",v);
            this.isReadyToSave = this.form.valid;
        });
    }

    ionViewDidLoad() {

    }
    /**
           * Send a POST request to our signup endpoint with the data
           * the user entered on the form.
           */
    saveAddress(address: any) {
        return new Promise((resolve, reject) => {
            console.log("Save Address", address);
            this.showLoader();
            this.addresses.saveAddress(address).subscribe((resp: any) => {
                this.loading.dismiss();
                console.log("Save Address result", resp);
                if (resp.status == "success") {
                    resolve(resp.address);
                } else {
                    resolve(null);
                }
            }, (err) => {
                this.loading.dismiss();
                reject(err);
            });
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
    done() {
        if (!this.form.valid) {return;}
        this.saveAddress(this.form.value).then((value) => {
            console.log("saveAddress result", value);
            if (value) {
                this.viewCtrl.dismiss(value);
            } else {
                // Unable to log in
                let toast = this.toastCtrl.create({
                    message: this.addressErrorStringSave,
                    duration: 3000,
                    position: 'top'
                });
                toast.present();
            }
        }).catch((error) => {
            console.log('Error saveAddress', error);
            let toast = this.toastCtrl.create({
                message: this.addressErrorStringSave,
                duration: 3000,
                position: 'top'
            });
            toast.present();
        });;

    }
    showLoader() {
        this.loading = this.loadingCtrl.create({
            content: 'Estamos guardando tus cambios'
        });

        this.loading.present();
    }
}
