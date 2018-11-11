import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';

import { RecipesPage } from './recipes';

@NgModule({
  declarations: [
    RecipesPage,
  ],
  imports: [
    IonicPageModule.forChild(RecipesPage),
    TranslateModule.forChild()
  ],
  exports: [
    RecipesPage
  ]
})
export class RecipesPageModule { }
