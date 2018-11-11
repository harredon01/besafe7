/**
 * A generic model that our Master-Detail pages list, create, and delete.
 *
 */
export class Product {
    id: any;
    name: any;
    description: any;
    quantity: any;
    price: any;
    unitPrice: any;
    unitLunches: any;
    type: any;
    imgs: any[];
    variants: any[];
    inCart: boolean;
    item_id: any;
    variant_id: any;
    amount: any;
    subtotal: any;
    merchant_name: any;
    merchant_description: any;
    merchant_type: any;
    constructor(fields: any) {

        // Quick and dirty extend/assign fields to this model
        for (const f in fields) {
            // @ts-ignore
            this[f] = fields[f];
        }
    }

}

export interface Product {
    [prop: string]: any;
}
