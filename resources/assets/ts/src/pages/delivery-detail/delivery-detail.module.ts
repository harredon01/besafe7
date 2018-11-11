import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';

import { DeliveryDetailPage } from './delivery-detail';

@NgModule({
  declarations: [
    DeliveryDetailPage,
  ],
  imports: [
    IonicPageModule.forChild(DeliveryDetailPage),
    TranslateModule.forChild()
  ],
  exports: [
    DeliveryDetailPage
  ]
})
export class DeliveryDetailPageModule { }
