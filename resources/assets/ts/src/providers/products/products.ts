import {Injectable} from '@angular/core';

import {Product} from '../../models/product';
import {Api} from '../api/api';

@Injectable()
export class Products {
    products: Product[] = [];

    constructor(public api: Api) {}

    getProductsMerchant(merchant: string, page: string) {
        let endpoint = '/products/merchant/' + merchant + "/" + page;
        let seq = this.api.get(endpoint).share();
        seq.subscribe((data: any) => {
            console.log("after get products");
            console.log(JSON.stringify(data));
            return data;
            // If the API returned a successful response, mark the user as logged in
        }, err => {
            console.error('ERROR', err);
        });
        return seq;

    }
    buildProductInformation(items) {
        let results = [];
        let resultsVariant = [];
        let productInfo = new Product({});
        let counter = 1;
        if (items['products_variants'].length > 0) {
            productInfo.id = items['products_variants'][0].product_id;
            productInfo.name = items['products_variants'][0].prod_name;
            productInfo.description = items['products_variants'][0].prod_desc;
            productInfo.price = items['products_variants'][0].price;
            productInfo.type = items['products_variants'][0].type;
            productInfo.variant_id = items['products_variants'][0].id;
            if (items['merchant_products'].length > 0) {
                productInfo.merchant_name = items['merchant_products'][0].merchant_name;
                productInfo.merchant_description = items['merchant_products'][0].merchant_description;
                productInfo.merchant_type = items['merchant_products'][0].merchant_type;
            }

            productInfo.inCart = false;
            productInfo.subtotal = productInfo.price;
            productInfo.unitPrice = productInfo.price;
            productInfo.unitLunches = 1;
            productInfo.item_id = null;
            productInfo.amount = 1;
            productInfo.imgs = [];

        }
        for (let i = 0; i < items['products_variants'].length; i++) {
            if (items['products_variants'][i].product_id != productInfo.id) {
                productInfo.variants = resultsVariant;
                results.push(productInfo);
                productInfo = new Product({}); 
                productInfo.id = items['products_variants'][i].product_id; 
                productInfo.name = items['products_variants'][i].prod_name;
                productInfo.description = items['products_variants'][i].prod_desc;
                productInfo.price = items['products_variants'][i].price;
                productInfo.type = items['products_variants'][i].type;
                if (items['merchant_products'].length > 0) {
                    productInfo.merchant_name = items['merchant_products'][0].merchant_name;
                    productInfo.merchant_description = items['merchant_products'][0].merchant_description;
                    productInfo.merchant_type = items['merchant_products'][0].merchant_type;
                }
                productInfo.variant_id = items['products_variants'][i].id;
                productInfo.subtotal = productInfo.price;
                productInfo.unitPrice = productInfo.price;
                productInfo.inCart = false;
                productInfo.item_id = null;
                productInfo.unitLunches = 1;
                productInfo.amount = 1;
                productInfo.imgs = [];
                resultsVariant = [];
                counter = 1;
            }
            let variant: any = {};
            console.log("Variant",items['products_variants'][i]); 
            console.log("Variant",items['products_variants'][i].id)
            variant.id = items['products_variants'][i].id;
            variant.description = items['products_variants'][i].description;
            if (items['products_variants'][i].attributes.length > 0) {
                variant.attributes = JSON.parse(items['products_variants'][i].attributes);
            } else {
                variant.attributes = "";
            }
            variant.price = items['products_variants'][i].price;
            variant.unitPrice = variant.price / counter;
            resultsVariant.push(variant);
            counter++;
            if ((i + 1) >= items['products_variants'].length) {
                productInfo.variants = resultsVariant;
                results.push(productInfo);
            }
        }
        for (let j = 0; j < items['products_files'].length; j++) {
            for (let i = 0; i < results.length; i++) {
                let imgInfo: any = {};
                if (items['products_files'][j].trigger_id == results[i].id) {
                    imgInfo.file = items['products_files'][j].file;
                    results[i].imgs.push(imgInfo);
                    break;
                }
            }
        }
        console.log('resultbuild', results);
        return results;
    }

}
