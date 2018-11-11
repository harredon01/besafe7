import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';

import { CheckoutCashPage } from './checkout-cash';

@NgModule({
  declarations: [
    CheckoutCashPage, 
  ],
  imports: [
    IonicPageModule.forChild(CheckoutCashPage),
    TranslateModule.forChild()
  ],
  exports: [
    CheckoutCashPage
  ]
})
export class CheckoutCashPageModule { }
