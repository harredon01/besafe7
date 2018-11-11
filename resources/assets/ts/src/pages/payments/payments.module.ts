import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';

import { PaymentsPage } from './payments';

@NgModule({
  declarations: [
    PaymentsPage,
  ],
  imports: [
    IonicPageModule.forChild(PaymentsPage),
    TranslateModule.forChild()
  ],
  exports: [
    PaymentsPage
  ]
})
export class PaymentsPageModule { }
