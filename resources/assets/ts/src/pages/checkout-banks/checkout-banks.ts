import {Component} from '@angular/core';
import {TranslateService} from '@ngx-translate/core';
import {FormBuilder, FormGroup, Validators} from '@angular/forms';
import {IonicPage, NavController, ToastController, LoadingController, ModalController} from 'ionic-angular';
import {OrderData} from '../../providers/orderdata/orderdata';
import {UserData} from '../../providers/userdata/userdata';
import {Billing} from '../../providers/billing/billing';
import { InAppBrowser } from '@ionic-native/in-app-browser';

@IonicPage()
@Component({
    selector: 'page-checkout-banks',
    templateUrl: 'checkout-banks.html'
})
export class CheckoutBanksPage {
    // The account fields for the login form.
    // If you're using the username field with or without email, make
    // sure to add it to the type
    payer: {
        payer_name: string,
        user_type: string,
        doc_type: string,
        payer_email: string,
        payer_phone: string,
        payer_id: string,
financial_institution_code:string
    } = {
            payer_name: '',
            payer_email: '',
            payer_phone: '',
            user_type: '',
            doc_type: '',
            payer_id: '',
            financial_institution_code:""
        };
    option: any;
    payerForm: FormGroup;
    submitAttempt: boolean = false;
    loading: any;
    v: any;
    currentItems: any[];

    private banksErrorString: string;
    private bankPaymentErrorString: string;

    constructor(public navCtrl: NavController,
        public orderData: OrderData,
        public iab: InAppBrowser,
        public billing: Billing,
        public userData: UserData,
        public modalCtrl: ModalController,
        public toastCtrl: ToastController,
        public translateService: TranslateService,
        public formBuilder: FormBuilder,
        private loadingCtrl: LoadingController) {
        this.payerForm = formBuilder.group({
            financial_institution_code: ['', Validators.required],
            payer_name: ['', Validators.compose([Validators.maxLength(30), Validators.pattern('[a-zA-Z 0-9._%+-]*'), Validators.required])],
            user_type: ['', Validators.required ],
            doc_type: ['', Validators.required ],
            payer_phone: ['', Validators.compose([Validators.maxLength(30), Validators.pattern('[a-zA-Z 0-9._%+-]*'), Validators.required])],
            payer_email: ['', Validators.compose([Validators.maxLength(30), Validators.pattern('[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,7}$'), Validators.required])],
            payer_id: ['', Validators.compose([Validators.maxLength(30), Validators.pattern('[a-zA-Z 0-9._%+-]*'), Validators.required])],
        });
        this.currentItems = [];

        this.translateService.get('CHECKOUT_BANKS.BANKS_GET_ERROR').subscribe((value) => {
            this.banksErrorString = value;
        });
        this.translateService.get('CHECKOUT_BANKS.DEBIT_PAY_ERROR').subscribe((value) => {
            this.bankPaymentErrorString = value;
        });
    }
    useUser() {
        console.log("prefil", this.v);
        console.log("user", this.userData._user.user);
        let container: any = null;
        if (this.v) {
            container = {
                payer_name: this.userData._user.user.firstName + " " + this.userData._user.user.lastName,
                payer_email: this.userData._user.user.email,
                payer_id: this.userData._user.user.docNum,
                user_type:"N",
                doc_type:"CC",
                payer_phone:this.userData._user.user.cellphone
            };
            this.payer.payer_name = this.userData._user.user.firstName + " " + this.userData._user.user.lastName;
            this.payer.payer_email = this.userData._user.user.email;
            this.payer.payer_id = this.userData._user.user.docNum;
            this.payer.payer_phone = this.userData._user.user.cellphone;
            this.payer.user_type = "N";
            this.payer.doc_type = "CC";
        } else {
            container = {
                payer_name: "",
                payer_email: "",
                payer_id: "",
                user_type:"",
                doc_type:"",
                payer_phone:""
            };
        }

        console.log("Setting form values: ", container);
        this.payerForm.setValue(container);
    }

    savePayer(item: any) {
        this.orderData.payerAddress = item;
    }

    getBanks() {
        this.showLoader();
        this.billing.getBanks().subscribe((data: any) => {
            this.loading.dismiss();
            console.log("after getBanks");
            let results = data.banks;
            for (let one in results) {
                let bank = {"name": results[one].description, "value": results[one].pseCode};
                this.currentItems.push(bank);
            }
            //this.createAddress();
            console.log(JSON.stringify(data));
            this.mockData();
        }, (err) => {
            this.loading.dismiss();
            // Unable to log in
            let toast = this.toastCtrl.create({
                message: this.banksErrorString,
                duration: 3000,
                position: 'top'
            });
            toast.present();
        });
    }


    showLoader() {
        this.loading = this.loadingCtrl.create({
            content: 'Estamos buscando los bancos'
        });

        this.loading.present();
    }
    showLoaderPay() {
        this.loading = this.loadingCtrl.create({
            content: 'Estamos buscando los bancos'
        });

        this.loading.present();
    }
    /**
       * The view loaded, let's query our items for the list
       */
    ionViewDidEnter() {
        this.getBanks();
    }
    payBank() {
        this.submitAttempt = true;
        if (!this.payerForm.valid) {return;}
        this.showLoaderPay();
        let shipping = this.orderData.shippingAddress;
        let container = {
            shipping_address: shipping.address,
            shipping_city: shipping.cityName,
            shipping_state: shipping.regionName,
            shipping_country: shipping.countryCode,
            shipping_postal: shipping.postal,
            shipping_phone: shipping.phone,
            doc_type: this.payer.doc_type,
            user_type: this.payer.user_type,
            financial_institution_code: this.payer.financial_institution_code,
            payer_name: this.payer.payer_name,
            payer_phone: this.payer.payer_phone,
            payer_email: this.payer.payer_email,
            payer_id: this.payer.payer_id,
            payment_id: this.orderData.payment.id,
            platform: "Food"
        };
        this.billing.payDebit(container).subscribe((data: any) => {
            this.loading.dismiss();
            console.log("after payDebit");
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
                message: this.bankPaymentErrorString,
                duration: 3000,
                position: 'top'
            });
            toast.present();
        });
    }
    mockData() {
        this.payer.doc_type = "CC";
        this.payer.user_type = "N";
        this.payer.financial_institution_code ="1022";
        this.payer.payer_name = "APPROVED";
        this.payer.payer_phone = "3105507245";
        this.payer.payer_email = "harredon01@gmail.com";
        this.payer.payer_id = "1020716535";
    }

}
