import {Component} from '@angular/core';
import {TranslateService} from '@ngx-translate/core';
import {FormBuilder, FormGroup, Validators} from '@angular/forms';
import {IonicPage, NavController, ToastController, LoadingController, ModalController, NavParams} from 'ionic-angular';
import {Addresses} from '../../providers/addresses/addresses';
import {Address} from '../../models/address';
import {OrderData} from '../../providers/orderdata/orderdata';
import {UserData} from '../../providers/userdata/userdata';

@IonicPage()
@Component({
    selector: 'page-checkout-payer',
    templateUrl: 'checkout-payer.html'
})
export class CheckoutPayerPage {
    // The account fields for the login form.
    // If you're using the username field with or without email, make
    // sure to add it to the type
    payer: {
        payer_name: string,
        payer_email: string,
        payer_id: string,

    } = {
            payer_name: '',
            payer_email: '',
            payer_id: '',
        };
    payerForm: FormGroup;
    submitAttempt: boolean = false;
    loading: any;
    v: any;
    showAddressCard: boolean;
    selectedAddress: Address;
    currentItems: Address[];

    private addressErrorString: string;

    constructor(public navCtrl: NavController,
        public userData: UserData,
        public orderData: OrderData,
        public navParams: NavParams,
        public modalCtrl: ModalController,
        public toastCtrl: ToastController,
        public translateService: TranslateService,
        public addresses: Addresses,
        public formBuilder: FormBuilder,
        private loadingCtrl: LoadingController) {
        this.showAddressCard = false;
        this.currentItems = [];
        console.log("user", this.userData._user);

        this.translateService.get('ADDRESS_FIELDS.ERROR_GET').subscribe((value) => {
            this.addressErrorString = value;
        });

        this.payerForm = formBuilder.group({
            payer_name: ['', Validators.compose([Validators.maxLength(30), Validators.pattern('[a-zA-Z 0-9._%+-]*'), Validators.required])],
            payer_email: ['', Validators.compose([Validators.maxLength(30), Validators.pattern('[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$'), Validators.required])],
            payer_id: ['', Validators.compose([Validators.maxLength(30), Validators.pattern('[a-zA-Z 0-9._%+-]*'), Validators.required])],
        });
    }

    savePayer(item: Address) {
        this.orderData.payerAddress = item;
        this.showAddressCard = true;
        this.selectedAddress = item;
        this.checkAdvance();
    }
    useUser() {
        console.log("prefil", this.v);
        console.log("user", this.userData._user);
        let container: any = null;
        if (this.v) {
            container = {
                payer_name: this.userData._user.firstName + " " + this.userData._user.lastName,
                payer_email: this.userData._user.email,
                payer_id: this.userData._user.docNum,
            };
            this.payer.payer_name = this.userData._user.firstName + " " + this.userData._user.lastName;
            this.payer.payer_email = this.userData._user.email;
            this.payer.payer_id = this.userData._user.docNum;
        } else {
            container = {
                payer_name: "",
                payer_email: "",
                payer_id: "",
            };
        }

        console.log("Setting form values: ", container);
        this.payerForm.setValue(container);
    }

    /**
     * The user is done and wants to create the item, so return it
     * back to the presenter.
     */
    submitPayer() {
        this.submitAttempt = true;
        if (!this.payerForm.valid) {return;}
        this.orderData.payerInfo = this.payerForm.value;
        this.checkAdvance();

    }
    /**
     * The user is done and wants to create the item, so return it
     * back to the presenter.
     */
    checkAdvance() {
        if (this.orderData.payerAddress && this.orderData.payerInfo) {
            this.navCtrl.push("CheckoutCardPage");
        }
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
        this.currentItems = this.navParams.get('items');
        if (this.orderData.payerAddress) {
            this.showAddressCard = true;
            this.selectedAddress = this.orderData.payerAddress;
            this.loading.dismiss();
        } else {

            this.loading.dismiss();
        }
    }
    keytab(event) {
        let nextInput = event.srcElement.nextElementSibling; // get the sibling element

        var target = event.target || event.srcElement;
        var id = target.id
        console.log(id.maxlength); // prints undefined

        if (nextInput == null)  // check the maxLength from here
            return;
        else
            nextInput.focus();   // focus if not null
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
                this.savePayer(item);
            }
        })
        addModal.present();

    }
    continuePayer() {
        this.checkAdvance();
    }
    changeAddress() {
        this.showAddressCard = false;
    }
}
