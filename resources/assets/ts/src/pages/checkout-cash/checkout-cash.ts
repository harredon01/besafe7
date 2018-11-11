import {Component} from '@angular/core';
import {TranslateService} from '@ngx-translate/core';
import {FormBuilder, FormGroup, Validators} from '@angular/forms';
import {IonicPage, NavController, ToastController, LoadingController, ModalController, NavParams} from 'ionic-angular';
import {OrderData} from '../../providers/orderdata/orderdata';
import {UserData} from '../../providers/userdata/userdata';
import {Billing} from '../../providers/billing/billing';
import { InAppBrowser } from '@ionic-native/in-app-browser';

@IonicPage()
@Component({
    selector: 'page-checkout-cash',
    templateUrl: 'checkout-cash.html'
})
export class CheckoutCashPage {
    // The account fields for the login form.
    // If you're using the username field with or without email, make
    // sure to add it to the type
    option: any;
    submitAttempt: boolean = false;
    loading: any;
    v: any;
    payerForm: FormGroup;
    payer: {
        payment_method: string,
        payer_email: string,
    } = {
            payment_method: '',
            payer_email: '',
        };

    private cashErrorString: string;
    private cashStartingString: string;
    private cashSuccessString: string;

    constructor(public navCtrl: NavController,
        public orderData: OrderData,
        public navParams: NavParams,
        public billing: Billing,
        public iab: InAppBrowser,
        public userData: UserData,
        public modalCtrl: ModalController,
        public formBuilder: FormBuilder,
        public toastCtrl: ToastController,
        public translateService: TranslateService,
        private loadingCtrl: LoadingController) {

        this.payerForm = formBuilder.group({
            payment_method: ['', Validators.compose([Validators.maxLength(30), Validators.pattern('[a-zA-Z 0-9._%+-]*'), Validators.required])],
            payer_email: ['', Validators.compose([Validators.maxLength(30), Validators.pattern('[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,7}$'), Validators.required])]
        });

        this.translateService.get('CHECKOUT_CASH.PAY_CASH_ERROR').subscribe((value) => {
            this.cashErrorString = value;
        });
        this.translateService.get('CHECKOUT_CASH.PAY_CASH_STARTING').subscribe((value) => {
            this.cashStartingString = value;
        });
        this.translateService.get('CHECKOUT_CASH.PAY_CASH_SUCCESS').subscribe((value) => {
            this.cashSuccessString = value;
        });
    }

    useUser() {
        console.log("prefil", this.v);
        console.log("user", this.userData._user.user);
        let container: any = null;
        if (this.v) {
            container = {
                payment_method: "",
                payer_email: this.userData._user.user.email,
            };
            this.payer.payer_email = this.userData._user.user.email;
        } else {
            container = {
                payer_name: "",
                payment_method: ""
            };
        }

        console.log("Setting form values: ", container);
        this.payerForm.setValue(container);
    }

    showLoader() {
        this.loading = this.loadingCtrl.create({
            content: this.cashStartingString
        });

        this.loading.present();
    }
    /**
       * The view loaded, let's query our items for the list
       */
    ionViewDidEnter() {
    }
    payCash() {
        this.submitAttempt = true;
        if (!this.payerForm.valid) {return;}
        this.showLoader();
        let shipping = this.orderData.shippingAddress;
        let container = {
            shipping_address: shipping.address,
            shipping_city: shipping.cityName,
            shipping_state: shipping.regionName,
            shipping_country: shipping.countryCode,
            shipping_postal: shipping.postal,
            shipping_phone: shipping.phone,
            payment_method: this.payer.payment_method,
            payer_email: this.payer.payer_email,
            payment_id: this.orderData.payment.id
        };
        this.billing.payCash(container).subscribe((data: any) => {
            this.loading.dismiss();
            console.log("after payCash");
            console.log(JSON.stringify(data));
            if(data.response.code=="SUCCESS"){
                if(data.response.transactionResponse.state=="PENDING"){
                    const browser = this.iab.create(data.response.transactionResponse.extraParameters.BANK_URL);
                }
            }
        }, (err) => {
            this.loading.dismiss();
            // Unable to log in
            let toast = this.toastCtrl.create({
                message: this.cashErrorString,
                duration: 3000,
                position: 'top'
            });
            toast.present();
        });
    }

}
