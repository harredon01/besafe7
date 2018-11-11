import {Component} from '@angular/core';
import {TranslateService} from '@ngx-translate/core';
import {IonicPage, ModalController, NavController, LoadingController, ToastController,AlertController} from 'ionic-angular';

import {Delivery} from '../../models/delivery';
import {Deliveries} from '../../providers/deliveries/deliveries';

@IonicPage()
@Component({
    selector: 'page-deliveries',
    templateUrl: 'deliveries.html'
})
export class DeliveriesPage {
    currentItems: any[];
    private deliveriesErrorString: string;
    private deliveriesStartingGetString: string;
    private deliveryWaitingTitle: string;
    private deliveryWaitingDesc: string;
    loading: any;
    page: any;
    loadMore: boolean;


    constructor(public navCtrl: NavController, public deliveries: Deliveries,
        public toastCtrl: ToastController,
        public modalCtrl: ModalController,
        public alertCtrl: AlertController,
        public translateService: TranslateService,
        private loadingCtrl: LoadingController) {
        this.translateService.get('DELIVERIES.ERROR_GET').subscribe((value) => {
            this.deliveriesErrorString = value;
        });
        this.translateService.get('DELIVERIES.STARTING_GET').subscribe((value) => {
            this.deliveriesStartingGetString = value;
        });
        this.translateService.get('HOME.DELIVERY_WAITING_TITLE').subscribe((value) => {
            this.deliveryWaitingTitle = value;
        });
        this.translateService.get('HOME.DELIVERY_WAITING_DESC').subscribe((value) => {
            this.deliveryWaitingDesc = value;
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
        this.deliveries.getDeliveries(query).subscribe((data: any) => {
            this.loading.dismiss();
            console.log("after get Deliveries");
            let results = data.data;
            if (data.page == data.last_page) {
                this.loadMore = false;
            }
            for (let one in results) {
                let container = new Delivery(results[one])
                this.currentItems.push(container);
            }
            console.log(JSON.stringify(data));
        }, (err) => {
            this.loading.dismiss();
            // Unable to log in
            let toast = this.toastCtrl.create({
                message: this.deliveriesErrorString,
                duration: 3000,
                position: 'top'
            });
            toast.present();
        });
    }
    selectDelivery(item) {
        if (item.status == "pending") {
            this.navCtrl.push("DeliveryProgramPage", {delivery: item});
        } else if (item.status == "transit") {
            this.navCtrl.parent.select(4);
        } else if (item.status == "delivered") {
            this.navCtrl.push("CommentsPage", {delivery: item});
        } else if (item.status == "enqueue") {
            this.showPrompt()
        }

    }
    showPrompt() {
        const prompt = this.alertCtrl.create({
            title: this.deliveryWaitingTitle,
            message: this.deliveryWaitingDesc,
            inputs: [],
            buttons: [
                {
                    text: 'OK',
                    handler: data => {
                    }
                }
            ]
        });
        prompt.present();
    }

    /**
     * Navigate to the detail page for this item.
     */
    openItem(item: Delivery) {
        this.navCtrl.push('DeliveryDetailPage', {
            item: item
        });
    }
    showLoader() {
        this.loading = this.loadingCtrl.create({
            content: this.deliveriesStartingGetString
        });

        this.loading.present();
    }
}
