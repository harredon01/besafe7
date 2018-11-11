import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';

import { AddressesPage } from './addresses';

@NgModule({
  declarations: [
    AddressesPage,
  ],
  imports: [
    IonicPageModule.forChild(AddressesPage),
    TranslateModule.forChild()
  ],
  exports: [
    AddressesPage
  ]
})
export class AddressesPageModule { }
