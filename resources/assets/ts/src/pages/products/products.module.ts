import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';

import { ProductsPage } from './products';

@NgModule({
  declarations: [
    ProductsPage,
  ],
  imports: [
    IonicPageModule.forChild(ProductsPage),
    TranslateModule.forChild()
  ],
  exports: [
    ProductsPage
  ]
})
export class ProductsPageModule { }
