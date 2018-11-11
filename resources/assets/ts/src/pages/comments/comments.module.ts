import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';

import { CommentsPage } from './comments';

@NgModule({
  declarations: [
    CommentsPage,
  ],
  imports: [
    IonicPageModule.forChild(CommentsPage),
    TranslateModule.forChild()
  ],
  exports: [
    CommentsPage
  ]
})
export class CommentsPageModule { }
