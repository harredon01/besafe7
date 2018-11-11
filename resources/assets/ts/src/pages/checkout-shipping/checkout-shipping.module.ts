import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';

import { CheckoutShippingPage } from './checkout-shipping';

@NgModule({
  declarations: [
    CheckoutShippingPage,
  ],
  imports: [
    IonicPageModule.forChild(CheckoutShippingPage),
    TranslateModule.forChild()
  ],
  exports: [
    CheckoutShippingPage
  ]
})
export class CheckoutShippingPageModule { }
