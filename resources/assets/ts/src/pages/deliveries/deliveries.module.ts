import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';

import { DeliveriesPage } from './deliveries';

@NgModule({
  declarations: [
    DeliveriesPage,
  ],
  imports: [
    IonicPageModule.forChild(DeliveriesPage),
    TranslateModule.forChild()
  ],
  exports: [
    DeliveriesPage
  ]
})
export class DeliveriesPageModule { }
