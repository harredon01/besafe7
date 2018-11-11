import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';

import { AddressSelectPage } from './address-select';

@NgModule({
  declarations: [
    AddressSelectPage,
  ],
  imports: [
    IonicPageModule.forChild(AddressSelectPage),
    TranslateModule.forChild()
  ],
  exports: [
    AddressSelectPage
  ]
})
export class AddressSelectPageModule { }
