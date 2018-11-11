import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';

import { CheckoutCardPage } from './checkout-card';

@NgModule({
  declarations: [
    CheckoutCardPage, 
  ],
  imports: [
    IonicPageModule.forChild(CheckoutCardPage),
    TranslateModule.forChild()
  ],
  exports: [
    CheckoutCardPage
  ]
})
export class CheckoutCardPageModule { }
