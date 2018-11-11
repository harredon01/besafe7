import { Component } from '@angular/core';
import { IonicPage, NavController } from 'ionic-angular';

@IonicPage()
@Component({
  selector: 'page-recipes',
  templateUrl: 'recipes.html'
})
export class RecipesPage {
    
    name: string;
    image:string;
    description:string;
    recipe_type: string;
    facts: string;
    date:string;

  constructor(public navCtrl: NavController) {
    

  }
}
