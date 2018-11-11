import {Component} from '@angular/core';
import {TranslateService} from '@ngx-translate/core';
import {IonicPage, ModalController, NavController, LoadingController, ToastController} from 'ionic-angular';

import {Payment} from '../../models/payment';
import {Billing} from '../../providers/billing/billing';

@IonicPage()
@Component({
    selector: 'page-payments',
    templateUrl: 'payments.html'
})
export class PaymentsPage {
    currentItems: any[];
    private paymentsErrorString: string;
    private paymentsGetStartString: string;
    loading: any;
    page: any;
    loadMore: boolean;


    constructor(public navCtrl: NavController, public billing: Billing,
        public toastCtrl: ToastController,
        public modalCtrl: ModalController,
        public translateService: TranslateService,
        private loadingCtrl: LoadingController) {
        this.translateService.get('PAYMENTS.ERROR_GET').subscribe((value) => {
            this.paymentsErrorString = value;
        });
        this.translateService.get('PAYMENTS.GET_START').subscribe((value) => {
            this.paymentsGetStartString = value;
        });
        this.page = 0;
        this.loadMore = true;
        this.currentItems = [];
    }

    /**
     * The view loaded, let's query our items for the list
     */
    /**
       * The view loaded, let's query our items for the list
       */
    ionViewDidEnter() {
        this.getItems();
    }

    doInfinite(infiniteScroll) {
        console.log('Begin async operation');
        if (this.loadMore) {
            setTimeout(() => {
                this.getItems();
                console.log('Async operation has ended');
                infiniteScroll.complete();
            }, 500);
        } else {
            infiniteScroll.complete();
        }

    }
    /**
     * Navigate to the detail page for this item.
     */
    getItems() {
        this.page++;
        this.showLoader();
        let query = "page=" + this.page;
        this.billing.getPayments(query).subscribe((data: any) => {
            this.loading.dismiss();
            console.log("after get Deliveries");
            let results = data.data;
            if (data.page == data.last_page) {
                this.loadMore = false;
            }
            for (let one in results) {
                let container = new Payment(results[one])
                this.currentItems.push(container);
            }
            console.log(JSON.stringify(data));
        }, (err) => {
            this.loading.dismiss();
            // Unable to log in
            let toast = this.toastCtrl.create({
                message: this.paymentsErrorString,
                duration: 3000,
                position: 'top'
            });
            toast.present();
        });
    }

    /**
     * Navigate to the detail page for this item.
     */
    openItem(item: Payment) {
        this.navCtrl.push('PaymentDetailPage', {
            item: item
        });
    }
    showLoader() {
        this.loading = this.loadingCtrl.create({
            content: this.paymentsGetStartString
        });

        this.loading.present();
    }
}
