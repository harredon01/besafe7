import {Component} from '@angular/core';
import {FormBuilder, FormGroup, Validators} from '@angular/forms';
import {IonicPage, NavController, NavParams, LoadingController} from 'ionic-angular';
import {FoodProvider} from '../../providers/food/food';


/**
 * Generated class for the DeliveryProgramPage page.
 *
 * See https://ionicframework.com/docs/components/#navigation for more info on
 * Ionic pages and navigation.
 */

@IonicPage()
@Component({
    selector: 'page-delivery-program',
    templateUrl: 'delivery-program.html',
})
export class DeliveryProgramPage {
    private loading: any;
    private listArticles: any = [];
    private foodTypeSelected: any = {}
    private foodTypeSelectedId: any;
    private attributes: any = [];
    private initFoodId: any;
    private standarFoodId: any;
    private delivery: any;
    dateError: boolean = false;
    submitAttempt: boolean = false;
    deliveryForm: FormGroup;
    starterDish: string;
    mainDish: string;
    type: string;

    private detailDelivery: {type: string, starter: string, main: string} = {
        type: 'vegetariano', starter: 'opcion 1', main: 'plato 1'
    }
    private saveDelivery: {
        day: number,
        month: number,
        year: number,
        type_id: number,
        starter_id: number,
        main_id: number,
        dessert_id: number,
        delivery_id: number,
        observation: string,
        details: any
    } = {
            day: 0,
            month: 0,
            year: 0,
            type_id: 0,
            starter_id: 0,
            main_id: 0,
            dessert_id: 1,
            delivery_id: 0,
            observation: '',
            details: this.detailDelivery
        };

    private deliveryParams: any;
    constructor(public navCtrl: NavController,
        public navParams: NavParams,
        public food: FoodProvider,
        private loadingCtrl: LoadingController,
        public formBuilder: FormBuilder, ) {
        this.starterDish = "";
        this.mainDish = "";
        this.type = "";
        this.deliveryParams = this.navParams.get('delivery');
        this.saveDelivery.delivery_id = this.deliveryParams.id;
        let date = new Date(this.deliveryParams.delivery);
        this.deliveryParams.delivery = date;
        this.saveDelivery.day = date.getDate();
        this.saveDelivery.month = date.getMonth() + 1;
        this.saveDelivery.year = date.getFullYear();
        this.deliveryForm = formBuilder.group({
            lunch_type: ['', Validators.compose([Validators.required])],
            starter: ['', Validators.compose([Validators.required])],
            main_dish: ['', Validators.compose([Validators.required])]
        });
    }

    ionViewDidLoad() {
        console.log('ionViewDidLoad DeliveryProgramPage');
        this.getArticles();
    }

    getArticles() {
        this.showLoader('Estamos obteniendo tus solicitudes');
        //this.food.getArticlesByDateTimeRange({init: this.saveDelivery.year+'-'+this.saveDelivery.month+"-"+this.saveDelivery.day, end: this.saveDelivery.year+'-'+this.saveDelivery.month+"-"+this.saveDelivery.day}).subscribe((resp) => {
        this.food.getArticlesByDateTimeRange({init: '2018-09-04', end: '2018-09-04'}).subscribe((resp) => {
            this.listArticles = resp["data"]
            this.loading.dismiss();
        }, (err) => {
            this.loading.dismiss();
        });
    }
    keytab(event, maxlength: any) {
        this.deliveryParams.delivery = new Date("20" + this.saveDelivery.year + "-" + this.saveDelivery.month + "-" + this.saveDelivery.day);
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

    showLoader(messages) {
        this.loading = this.loadingCtrl.create({
            content: messages
        });

        this.loading.present();
    }

    selectFoodType() {
        this.foodTypeSelected = this.listArticles.find(i => i.id == this.saveDelivery.type_id);
        this.attributes = JSON.parse(this.foodTypeSelected.attributes);
        this.detailDelivery.type = this.foodTypeSelected.name;
        this.type = this.foodTypeSelected.name;
        this.saveDelivery.details.type = this.foodTypeSelected.name;
    }

    selectInitFood() {
        this.detailDelivery.starter = this.attributes.entradas.find(i => i.codigo == this.saveDelivery.starter_id);
        this.detailDelivery.starter = this.detailDelivery.starter['valor'];
        this.starterDish = this.detailDelivery.starter['descripcion'];
        this.saveDelivery.details.starter = this.detailDelivery.starter['valor'];
    }

    selectStandarFood() {
        this.detailDelivery.main = this.attributes.plato.find(i => i.codigo == this.saveDelivery.main_id);
        this.detailDelivery.main = this.detailDelivery.main['valor'];
        this.mainDish = this.detailDelivery.main['descripcion'];
        this.saveDelivery.details.starter = this.detailDelivery.main['descripcion'];
    }

    updateDelivery() {
        this.submitAttempt = true;
        this.dateError = false;
        if (!this.deliveryForm.valid) {return;}
        let d = new Date(this.saveDelivery.year + 2000, this.saveDelivery.month - 1);
        let c = new Date();
        if (d < c) {
            this.dateError = true;
            return;
        }
        this.showLoader('Estamos actualizando tu información');
        this.food.updateDeliveryInformation(this.saveDelivery).subscribe((resp) => {
            this.loading.dismiss();
            this.navCtrl.pop();
        }, (err) => {
            this.loading.dismiss();
        });
    }

}
