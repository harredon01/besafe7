import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';

import { AddressDetailPage } from './address-detail';

@NgModule({
  declarations: [
    AddressDetailPage,
  ],
  imports: [
    IonicPageModule.forChild(AddressDetailPage),
    TranslateModule.forChild()
  ],
  exports: [
    AddressDetailPage
  ]
})
export class AddressDetailPageModule { }
