import {Component} from '@angular/core';
import {IonicPage, NavController, ToastController, ModalController, AlertController, Events} from 'ionic-angular';
import {TranslateService} from '@ngx-translate/core';
import {Products} from '../../providers/products/products';
import {OrderData} from '../../providers/orderdata/orderdata';
import {UserData} from '../../providers/userdata/userdata';
import {Cart} from '../../providers/cart/cart';
import {Product} from '../../models/product';

@IonicPage()
@Component({
    selector: 'page-products',
    templateUrl: 'products.html'
})
export class ProductsPage {

    products: Product[] = [];
    options: any[];
    merchantObj: {
        merchant_name: string,
        merchant_description: string,
        merchant_type: string,
    } = {
            merchant_name: "",
            merchant_description: "",
            merchant_type: "",
        };
    possibleAmounts: any[];
    merchant: any;
    page: any;
    private cartErrorString: string;

    constructor(public navCtrl: NavController,
        public productsServ: Products,
        public toastCtrl: ToastController,
        public modalCtrl: ModalController,
        public alertCtrl: AlertController,
        public cart: Cart,
        public events: Events,
        public userData: UserData,
        public orderData: OrderData,
        public translateService: TranslateService) {
        this.translateService.get('CART.ERROR_UPDATE').subscribe((value) => {
            this.cartErrorString = value;
        })
        this.page = 1;
        this.merchant = 1257;
        this.products = [];
        this.possibleAmounts = [];
        this.loadProducts();
        this.loadOptions();
        console.log("User: ", this.userData._user);
        events.subscribe('cart:deleteItem', (item) => {
            this.clearCartItem(item);
            // user and time are the same arguments passed in `events.publish(user, time)`
        });
        events.subscribe('cart:clear', () => {
            this.clearCart();
            // user and time are the same arguments passed in `events.publish(user, time)`
        });

    }
    showPrompt(item: any, missing: any) {
        const prompt = this.alertCtrl.create({
            title: 'Atencion',
            message: "Debes agregar " + missing + " depositos para comprar este plan. quieres que los agreguemos?",
            inputs: [],
            buttons: [
                {
                    text: 'No',
                    handler: data => {
                        console.log('Cancel clicked');
                    }
                },
                {
                    text: 'Si',
                    handler: data => {
                        this.addCredit(item, missing);
                    }
                }
            ]
        });
        prompt.present();
    }
    addCart(item: any) {
        console.log("Add cart", item);
        let result = this.checkProductBuyers(item);

        if (result) {
            let container = {
                "necessary": result
            };
            console.log("BuyerSelectPage", container);
            let addModal = this.modalCtrl.create('BuyerSelectPage', container);
            addModal.onDidDismiss(resp => {
                if (resp == "done") {
                    this.checkCredits(item);
                }
            })
            addModal.present();
        } else {
            this.checkCredits(item);
        }


    }
    checkCredits(item: any) {
        let result = this.checkProductCredits(item);
        console.log("Check  product credit result", result);
        if (result) {
            let pending = result;
            if (this.userData._user.credits == 0) {
                pending++;
            }
            this.showPrompt(item, pending);
            this.addCartItem(item);
        } else {
            this.addCartItem(item);
        }
    }
    checkProductCredits(product: any) {
        let variants = product.variants;
        let container = null;
        let found = false;
        for (let item in variants) {
            container = variants[item];
            if (container.id == product.variant_id) {
                found = true;
                break;
            }
        }
        console.log("prodcred", found);
        if (!found) {
            return null;
        }
        if (container.attributes.requires_credits) {
            if (container.attributes.credits > 0) {
                return container.attributes.credits;
            }
        }
        return null;
    }
    checkProductBuyers(product: any) {
        let variants = product.variants;
        let container = null;
        let found = false;
        for (let item in variants) {
            container = variants[item];
            if (container.id == product.variant_id) {
                found = true;
                break;
            }
        }
        if (!found) {
            return null;
        }
        console.log("Container check", container);
        if (container.attributes.multiple_buyers) {
            if (container.attributes.buyers > 1) {
                return container.attributes.buyers;
            }
        }
        return null;
    }
    clearCart() {
        for (let item in this.products) {
            this.products[item].inCart = false;
            this.products[item].item_id = null;
        }
        return null;
    }
    clearCartItem(item: any) {
        let product_variant_it = item.attributes.product_variant_id;
        for (let item in this.products) {
            let container = this.products[item];
            for (let variant in container.variants) {

                if (container.variants[variant].id == product_variant_it) {
                    this.products[item].inCart = false;
                    this.products[item].item_id = null;
                    return true;
                }

            }
        }
        return null;
    }

    addCredit(product: any, amount: any) {
        return new Promise((resolve, reject) => {
            console.log('addCredit: ' + amount, product);
            if (this.orderData.creditProduct) {
                let container2 = {
                    variant_id: this.orderData.creditProduct,
                    amount: amount,
                    item_id: null
                };
                this.addCartItem(container2);
                resolve(container2);
            } else {
                for (let prod in this.products) {
                    let variants = this.products[prod].variants;
                    let container = null;
                    for (let item in variants) {

                        container = variants[item];
                        console.log('addCredit: container', container);
                        if (container.attributes.is_credit) {
                            console.log('addCredit: is credit');
                            if (container.attributes.credit_for == product.id) {
                                this.orderData.creditProduct = container.id;
                                let container2 = {
                                    variant_id: container.id,
                                    amount: amount,
                                    item_id: null
                                };
                                this.products[prod].inCart = true;
                                this.products[prod].item_id = container.id;
                                this.products[prod].amount = amount;
                                this.addCartItem(container2).then((value) => {

                                }).catch((error) => {
                                    console.log('Error cartErrorString', error);
                                    let toast = this.toastCtrl.create({
                                        message: this.cartErrorString,
                                        duration: 3000,
                                        position: 'top'
                                    });
                                    toast.present();
                                });
                                resolve(container2);
                            }
                        }
                    }
                }

            }
            resolve(null);
        });
    }
    reduceCartItem(item: any) {
        item.amount--;
        this.addCartItem(item);

    }
    increaseCartItem(item: any) {
        item.amount++;
        this.addCartItem(item);

    }
    addCartItem(item: any) {
        return new Promise((resolve, reject) => {
            let container = {
                product_variant_id: item.variant_id,
                quantity: item.amount,
                item_id: item.item_id,
                merchant_id: this.merchant
            };
            console.log("Add cart item", container);
            if (container.item_id) {
                this.cart.updateCartItem(container).subscribe((resp: any) => {
                    this.orderData.cartData = resp.cart;
                    if (resp.item) {
                        item.inCart = true;
                        item.item_id = resp.item.id;
                        item.amount = resp.item.quantity;
                        this.calculateTotals();
                        resolve(resp.item);
                    } else {
                        item.inCart = false;
                        item.item_id = null;
                        item.amount = 1;
                        this.calculateTotals();
                        resolve(null);
                    }
                }, (err) => {
                    //this.navCtrl.push(MainPage);
                    // Unable to log in
                    let toast = this.toastCtrl.create({
                        message: this.cartErrorString,
                        duration: 3000,
                        position: 'top'
                    });
                    toast.present();
                    resolve(null);
                });
            } else {
                this.cart.addCartItem(container).subscribe((resp: any) => {
                    this.orderData.cartData = resp.cart;
                    if (resp.item) {
                        item.inCart = true;
                        item.item_id = resp.item.id;
                        item.amount = resp.item.quantity;
                        this.calculateTotals();
                        resolve(resp.item);
                    } else {
                        item.inCart = false;
                        item.item_id = null;
                        item.amount = 1;
                        this.calculateTotals();
                        resolve(null);
                    }
                    //this.navCtrl.push(MainPage);
                }, (err) => {
                    //this.navCtrl.push(MainPage);
                    // Unable to log in
                    let toast = this.toastCtrl.create({
                        message: this.cartErrorString,
                        duration: 3000,
                        position: 'top'
                    });
                    toast.present();
                    resolve(null);
                });
            }
        });

    }
    loadProducts() {
        this.productsServ.getProductsMerchant(this.merchant, this.page).subscribe((resp) => {
            if (resp) {
                this.products = this.productsServ.buildProductInformation(resp);
                this.merchantObj.merchant_name = this.products[0].merchant_name;
                this.merchantObj.merchant_description = this.products[0].merchant_description;
                this.merchantObj.merchant_type = this.products[0].merchant_type;
                console.log("Merchant", this.merchantObj)
                let items = this.orderData.cartData.items;
                for (let key in items) {
                    let contItem = items[key].attributes;
                    contItem.id = items[key].id;
                    contItem.quantity = items[key].quantity;
                    for (let j in this.products) {
                        let contProd = this.products[j];
                        for (let i in contProd.variants) {
                            let contVariant = contProd.variants[i];
                            if (contItem.product_variant_id == contVariant.id) {
                                contProd.inCart = true;
                                contProd.item_id = contItem.id;
                                contProd.variant_id = contItem.product_variant_id;
                                contProd.amount = contItem.quantity;
                            }
                        }
                    }
                }
                this.calculateTotals();
            }
        }, (err) => {
            // Unable to log in
        });
    }
    openCart() {
        let container = {cart: this.orderData.cartData};
        console.log("Opening Cart", container);
        let addModal = this.modalCtrl.create('CartPage', container);
        addModal.onDidDismiss(item => {
            if (item == "Checkout") {
                console.log("User: ", this.userData._user);
                this.navCtrl.push('CheckoutShippingPage');
            }

        })
        addModal.present();
    }

    loadOptions() {
        for (let i = 1; i < 31; i++) {
            let container = {"value": i};
            this.possibleAmounts.push(container);
        }
    }
    selectVariant(item: any) {
        console.log(item);
        let counter = 1;
        for (let i in item.variants) {
            let container = item.variants[i];
            if (container.id == item.variant_id) {
                item.price = container.price;
                if (item.type == "meal-plan") {
                    item.unitLunches = counter;
                    if (item.amount > 1 && item.amount < 11) {
                        item.subtotal = item.price * item.amount;
                        item.unitPrice = item.subtotal / (item.unitLunches * item.amount);
                    } else {
                        let control = item.amount / 10;
                        let counter2 = Math.floor(item.amount / 10);
                        if (control == counter2) {
                            item.subtotal = (item.price * item.amount) - ((counter2 - 1) * item.unitLunches * 11000);
                        } else {
                            item.subtotal = (item.price * item.amount) - (counter2 * item.unitLunches * 11000);
                        }
                        item.unitPrice = item.subtotal / (item.unitLunches * item.amount);
                    }
                }
            }
            counter++;
        }
    }
    calculateTotals() {
        console.log("Calculate totals");
        for (let i in this.products) {
            let container = this.products[i];
            if (container.amount > 1 && container.amount < 11) {
                container.subtotal = container.price * container.amount;

                //type meal
                if (container.type == "meal-plan") {
                    container.unitPrice = container.subtotal / (container.unitLunches * container.amount);
                }

            } else {
                if (container.type == "meal-plan") {
                    let control = container.amount / 10;
                    let counter2 = Math.floor(container.amount / 10);
                    if (control == counter2) {
                        container.subtotal = (container.price * container.amount) - ((counter2 - 1) * container.unitLunches * 11000);
                    } else {
                        container.subtotal = (container.price * container.amount) - (counter2 * container.unitLunches * 11000);
                    }
                    container.unitPrice = container.subtotal / (container.unitLunches * container.amount);
                } else {
                    container.subtotal = (container.price * container.amount);
                }

            }
        }
        console.log('resultbuildFinal', this.products);
    }
}