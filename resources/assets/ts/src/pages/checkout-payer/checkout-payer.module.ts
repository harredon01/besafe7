import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';

import { CheckoutPayerPage } from './checkout-payer';

@NgModule({
  declarations: [
    CheckoutPayerPage,
  ],
  imports: [
    IonicPageModule.forChild(CheckoutPayerPage),
    TranslateModule.forChild()
  ],
  exports: [
    CheckoutPayerPage
  ]
})
export class CheckoutPayerPageModule { }
