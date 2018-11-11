import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';

import { CheckoutBanksPage } from './checkout-banks';

@NgModule({
  declarations: [
    CheckoutBanksPage, 
  ],
  imports: [
    IonicPageModule.forChild(CheckoutBanksPage),
    TranslateModule.forChild()
  ],
  exports: [
    CheckoutBanksPage
  ]
})
export class CheckoutBanksPageModule { }
