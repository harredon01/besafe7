import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';

import { AddressCreatePage } from './address-create';

@NgModule({
  declarations: [
    AddressCreatePage,
  ],
  imports: [
    IonicPageModule.forChild(AddressCreatePage),
    TranslateModule.forChild()
  ],
  exports: [
    AddressCreatePage
  ]
})
export class AddressCreatePageModule { }
