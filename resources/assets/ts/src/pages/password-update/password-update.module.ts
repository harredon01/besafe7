import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';

import { PasswordUpdatePage } from './password-update';

@NgModule({
  declarations: [
    PasswordUpdatePage,
  ],
  imports: [
    IonicPageModule.forChild(PasswordUpdatePage),
    TranslateModule.forChild()
  ],
  exports: [
    PasswordUpdatePage
  ]
})
export class PasswordUpdatePageModule { }
