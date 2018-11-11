import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';
import { DeliveryProgramPage } from './delivery-program';

@NgModule({
  declarations: [
    DeliveryProgramPage,
  ],
  imports: [
    IonicPageModule.forChild(DeliveryProgramPage),
    TranslateModule.forChild()
  ],
})
export class DeliveryProgramPageModule {}
