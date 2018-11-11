import {Component} from '@angular/core';
import {TranslateService} from '@ngx-translate/core';
import {FormBuilder, FormGroup, Validators} from '@angular/forms';
import {IonicPage, NavController, ToastController, LoadingController, ModalController, NavParams} from 'ionic-angular';
import {OrderData} from '../../providers/orderdata/orderdata';
import {Billing} from '../../providers/billing/billing';


@IonicPage()
@Component({
    selector: 'page-checkout-card',
    templateUrl: 'checkout-card.html'
})
export class CheckoutCardPage {
    // The account fields for the login form.
    // If you're using the username field with or without email, make
    // sure to add it to the type
    card: {
        cc_branch: string,
        cc_expiration_month: string,
        cc_expiration_year: string,
        cc_name: string,
        cc_number: string,
        cc_security_code: string,
        save_card: boolean,
    } = {
            cc_branch: '',
            cc_expiration_month: '',
            cc_expiration_year: '',
            cc_name: '',
            cc_number: '',
            cc_security_code: '',
            save_card: false
        };
    payerForm: FormGroup;
    submitAttempt: boolean = false;
    dateError: boolean = false;
    cvvError: boolean = false;
    token:any;
    useToken: boolean = false;
    loading: any;

    private cardErrorString: string;
    private cardSuccessString: string;
    private cardStartingString: string;

    constructor(public navCtrl: NavController,
        public orderData: OrderData,
        public navParams: NavParams,
        public modalCtrl: ModalController,
        public billing: Billing,
        public toastCtrl: ToastController,
        public translateService: TranslateService,
        public formBuilder: FormBuilder,
        private loadingCtrl: LoadingController) {
        this.token = null;
        this.translateService.get('CHECKOUT_CARD.PAY_CC_ERROR').subscribe((value) => {
            this.cardErrorString = value;
        });
        this.translateService.get('CHECKOUT_CARD.PAY_CC_SUCCESS').subscribe((value) => {
            this.cardSuccessString = value;
        });
        this.translateService.get('CHECKOUT_CARD.PAY_CC_STARTING').subscribe((value) => {
            this.cardStartingString = value;
        });
        let c = new Date( );
        this.payerForm = formBuilder.group({
            save_card: [''],
            cc_number: ['', Validators.compose([Validators.minLength(12), Validators.pattern('[0-9-]*'), Validators.required])],
            cc_security_code: ['', Validators.compose([Validators.minLength(3),Validators.maxLength(4), Validators.pattern('[0-9]*'), Validators.required])],
            cc_branch: ['', Validators.compose([Validators.maxLength(10), Validators.pattern('[A-Z]*'), Validators.required])],
            cc_name: ['', Validators.compose([Validators.maxLength(20), Validators.pattern('[a-zA-Z]*'), Validators.required])],
            cc_expiration_month: ['', Validators.compose([Validators.maxLength(2), Validators.pattern('[0-9]*'), Validators.required, Validators.min(1), Validators.max(12)])],
            cc_expiration_year: ['', Validators.compose([Validators.maxLength(2), Validators.pattern('[0-9]*'), Validators.required, Validators.min(c.getFullYear()-2000), Validators.max(2040)])],
        });
        this.getTokens();
    }

    savePayer(item: any) {
        this.orderData.payerAddress = item;
    }




    showLoader() {
        this.loading = this.loadingCtrl.create({
            content: this.cardStartingString
        });

        this.loading.present();
    }
    /**
       * The view loaded, let's query our items for the list
       */
    ionViewDidEnter() {
        this.mockData();
    }
    creditTab(event) {
        let target = event.target || event.srcElement;
        let value = target.value;
        let branch = (/^(?:5[1-5][0-9]{2}|222[1-9]|22[3-9][0-9]|2[3-6][0-9]{2}|27[01][0-9]|2720)[0-9]{12}$/.test(value)) ? "MASTERCARD"
            : (/^4[0-9]{12}(?:[0-9]{3})?$/.test(value)) ? "VISA"
                : (/^3[47][0-9]{13}$/.test(value)) ? 'AMEX'
                    : (/^3(?:0[0-5]|[68][0-9])[0-9]{11}$/.test(value)) ? 'DINERS'
                        : (/^6(?:011|5[0-9]{2})[0-9]{12}$/.test(value)) ? 'DISCOVER'
                            : "";
        console.log("Credit branch", branch);
        this.card.cc_branch = branch;

    }
    keytab(event, maxlength: any) {
        let nextInput = event.srcElement.nextElementSibling; // get the sibling element
        console.log('nextInput', nextInput);
        var target = event.target || event.srcElement;
        console.log('target', target);
        console.log('targetvalue', target.value);
        console.log('targettype', target.nodeType);
        if (target.value.length < maxlength) {
            return;
        }
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
    getTokens() {
        this.useToken = false;
        this.billing.getRawSources().subscribe((data: any) => {
            console.log("after getBanks",data);
            if(data.source){
                if(data.source.length > 0 ){
                    this.token = data.source;
                    this.useToken = true;
                }
            }
            
        }, (err) => {

        });
    }
    payCreditCard() {
        this.submitAttempt = true;
        this.dateError = false;
        this.cvvError = false;
        if (!this.payerForm.valid) {return;}
        let d = new Date(parseInt(this.card.cc_expiration_year)+2000, parseInt(this.card.cc_expiration_month)-1  );
        let c = new Date( );
        if (d < c) {
            this.dateError = true;
            return;
        }
        if (this.card.cc_branch != "AMEX" && this.card.cc_security_code.length != 3){
            this.cvvError = true;
            return;
        }
        if (this.card.cc_branch == "AMEX" && this.card.cc_security_code.length != 4){
            this.cvvError = true;
            return;
        }
        this.showLoader();
        let shipping = this.orderData.shippingAddress;
        let buyer = this.orderData.buyerAddress;
        let payer = this.orderData.payerAddress;
        let payer_info = this.orderData.payerInfo;
        let container = {
            shipping_address: shipping.address,
            shipping_city: shipping.cityName,
            shipping_state: shipping.regionName,
            shipping_country: shipping.countryCode,
            shipping_postal: shipping.postal,
            shipping_phone: shipping.phone,
            buyer_address: buyer.address,
            buyer_city: buyer.cityName,
            buyer_state: buyer.regionName,
            buyer_country: buyer.countryCode,
            buyer_postal: buyer.postal,
            buyer_phone: buyer.phone,
            payer_address: payer.address,
            payer_city: payer.cityName,
            payer_state: payer.regionName,
            payer_country: payer.countryCode,
            payer_postal: payer.postal,
            payer_phone: payer.phone,
            payer_name: payer_info.payer_name,
            payer_email: payer_info.payer_email,
            payer_id: payer_info.payer_id,
            cc_branch: this.card.cc_branch,
            cc_expiration_month: this.card.cc_expiration_month,
            cc_expiration_year: this.card.cc_expiration_year,
            cc_name: this.card.cc_name,
            cc_number: this.card.cc_number,
            cc_security_code: this.card.cc_security_code,
            save_card: this.card.save_card,
            payment_id: this.orderData.payment.id,
            platform: "Food"
        };
        this.billing.payCreditCard(container).subscribe((data: any) => {
            this.loading.dismiss();
            console.log("after payCreditCard");
            console.log(JSON.stringify(data));
            this.orderData.clearOrder();
            this.navCtrl.popToRoot();
        }, (err) => {
            this.loading.dismiss();
            // Unable to log in
            let toast = this.toastCtrl.create({
                message: this.cardErrorString,
                duration: 3000,
                position: 'top'
            });
            toast.present();
        });
    }
    payToken() {
        this.showLoader();
        let shipping = this.orderData.shippingAddress;
        let buyer = this.orderData.buyerAddress;
        console.log("buyer",buyer);
        let payer = this.orderData.payerAddress;
        let payer_info = this.orderData.payerInfo;
        let container = {
            shipping_address: shipping.address,
            shipping_city: shipping.cityName,
            shipping_state: shipping.regionName,
            shipping_country: shipping.countryCode,
            shipping_postal: shipping.postal,
            shipping_phone: shipping.phone,
            buyer_address: buyer.address,
            buyer_city: buyer.cityName,
            buyer_state: buyer.regionName,
            buyer_country: buyer.countryCode,
            buyer_postal: buyer.postal,
            buyer_phone: buyer.phone,
            payer_address: payer.address,
            payer_city: payer.cityName,
            payer_state: payer.regionName,
            payer_country: payer.countryCode,
            payer_postal: payer.postal,
            payer_phone: payer.phone,
            payer_name: payer_info.payer_name,
            payer_email: payer_info.payer_email,
            payer_id: payer_info.payer_id,
            token: this.token,
            payment_id: this.orderData.payment.id
        };
        console.log("before payCreditCard token",container);
        this.billing.payCreditCard(container).subscribe((data: any) => {
            this.loading.dismiss();
            console.log("after payCreditCard token");
            console.log(JSON.stringify(data));
            this.orderData.clearOrder();
            this.navCtrl.popToRoot();
        }, (err) => {
            this.loading.dismiss();
            // Unable to log in
            let toast = this.toastCtrl.create({
                message: this.cardErrorString,
                duration: 3000,
                position: 'top'
            });
            toast.present();
        });
    }
    useOld() {
        this.useToken = false;
    }
    mockData() {
        this.card.cc_branch = "VISA";
        this.card.cc_expiration_month = "11";
        this.card.cc_expiration_year = "22";
        this.card.cc_name = "APPROVED";
        this.card.cc_number = "4111111111111111";
        this.card.cc_security_code = "123";
    }
}
