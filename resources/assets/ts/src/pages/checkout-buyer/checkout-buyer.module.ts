import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';

import { CheckoutBuyerPage } from './checkout-buyer';

@NgModule({
  declarations: [
    CheckoutBuyerPage,
  ],
  imports: [
    IonicPageModule.forChild(CheckoutBuyerPage),
    TranslateModule.forChild()
  ],
  exports: [
    CheckoutBuyerPage
  ]
})
export class CheckoutBuyerPageModule { }
