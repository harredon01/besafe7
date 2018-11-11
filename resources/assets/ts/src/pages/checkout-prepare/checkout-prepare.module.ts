import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';

import { CheckoutPreparePage } from './checkout-prepare';

@NgModule({
  declarations: [
    CheckoutPreparePage,
  ],
  imports: [
    IonicPageModule.forChild(CheckoutPreparePage),
    TranslateModule.forChild()
  ],
  exports: [
    CheckoutPreparePage
  ]
})
export class CheckoutPreparePageModule { }
