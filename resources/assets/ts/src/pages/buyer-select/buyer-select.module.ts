import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';

import { BuyerSelectPage } from './buyer-select';

@NgModule({
  declarations: [
    BuyerSelectPage,
  ],
  imports: [
    IonicPageModule.forChild(BuyerSelectPage),
    TranslateModule.forChild()
  ],
  exports: [
    BuyerSelectPage
  ]
})
export class BuyerSelectPageModule { }
