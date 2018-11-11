import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';

import { SettingsBackPage } from './settingsback';

@NgModule({
  declarations: [
    SettingsBackPage, 
  ],
  imports: [
    IonicPageModule.forChild(SettingsBackPage),
    TranslateModule.forChild()
  ],
  exports: [
    SettingsBackPage
  ]
})
export class SettingsBackPageModule { }
