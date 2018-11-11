import {Component} from '@angular/core';
import {IonicPage, NavController, ViewController, NavParams, LoadingController, ToastController} from 'ionic-angular';
import {TranslateService} from '@ngx-translate/core';
import {User} from '../../providers/user/user';
import {OrderData} from '../../providers/orderdata/orderdata';
@IonicPage()
@Component({
    selector: 'page-buyer-select',
    templateUrl: 'buyer-select.html'
})
export class BuyerSelectPage {
    payers: any[];
    item: any;
    candidate: any;
    private checkError: string;
    loading: any;
    totalNecessary: any;

    constructor(public navCtrl: NavController,
        public viewCtrl: ViewController,
        public toastCtrl: ToastController,
        public user: User,
        public orderData: OrderData,
        public translateService: TranslateService,
        public navParams: NavParams,
        private loadingCtrl: LoadingController) {
        this.payers = [];
        this.translateService.get('CHECK_ERROR').subscribe((value) => {
            this.checkError = value;
        });
        this.totalNecessary = navParams.get('necessary') - 1;
        this.orderData.clearOrderPayers(-1);

    }

    ionViewDidLoad() {

    }
    /**
           * Send a POST request to our signup endpoint with the data
           * the user entered on the form.
           */
    checkPayer() {
        console.log("checkPayer", this.candidate);
        this.showLoader(); 
        let container = {
            "email": this.candidate,
            "credits": 1
        }
        this.user.checkCredits(container).subscribe((resp: any) => {
            this.loading.dismiss();
            console.log("Save Address result", resp);
            if (resp.status == "success") {
                this.totalNecessary--;
                let accepted = {"user_id": resp.user_id, "email": this.candidate, "credits": resp.credits};
                this.payers.push(accepted);
                this.orderData.payers.push(accepted); 
                if(this.orderData.currentOrder){
                    this.orderData.savePayer(this.orderData.currentOrder.id, resp.user_id, this.candidate);
                } else {
                    this.orderData.savePayer(null, resp.user_id, this.candidate);
                }
                
                this.candidate = "";
            } else {
                // Unable to log in
                let toast = this.toastCtrl.create({
                    message: this.checkError,
                    duration: 3000,
                    position: 'top'
                });
                toast.present();
            }
        }, (err) => {
            this.loading.dismiss();
        });

    }


    /**
     * The user cancelled, so we dismiss without sending data back.
     */
    cancel() {
        this.viewCtrl.dismiss("cancel");
    }

    /**
     * Delete an item from the list of items.
     */
    deletePayer(item) {
        this.payers.splice(this.payers.indexOf(item), 1);
        this.orderData.payers.splice(this.orderData.payers.indexOf(item.user_id), 1);
    }

    /**
     * The user is done and wants to create the item, so return it
     * back to the presenter.
     */
    done() {
        if (this.totalNecessary == 0) {
            this.viewCtrl.dismiss("done");
        } else {
            let toast = this.toastCtrl.create({
                message: "No tienes suficientes",
                duration: 3000,
                position: 'top'
            });
            toast.present();
        }


    }
    showLoader() {
        this.loading = this.loadingCtrl.create({
            content: 'Estamos verificando tu usuario'
        });

        this.loading.present();
    }
}
