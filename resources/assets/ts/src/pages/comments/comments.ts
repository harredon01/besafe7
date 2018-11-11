import {Component} from '@angular/core';
import {TranslateService} from '@ngx-translate/core';
import {IonicPage, NavController, ToastController, NavParams} from 'ionic-angular';

import {Ratings} from '../../providers/ratings/ratings';
import {MainPage} from '../';

@IonicPage()
@Component({
    selector: 'page-comments',
    templateUrl: 'comments.html'
})
export class CommentsPage {
    // The account fields for the login form.
    // If you're using the username field with or without email, make
    // sure to add it to the type
    comment: {comment: string, rating: any, object_id: number, type: string} = {
        comment: 'El almuerzo estuvo terrible',
        rating: '5',
        object_id:-1,
        type:"Article"
    };

    // Our translated text strings
    private commentErrorString: string;
    private delivery: any;

    constructor(public navCtrl: NavController,
    public navParams: NavParams,
        public ratings: Ratings,
        public toastCtrl: ToastController,
        public translateService: TranslateService) {
        this.delivery = this.navParams.get('delivery');
        this.comment.object_id = this.delivery.type_id;
        this.translateService.get('COMMENTS.SAVE_RATING_ERROR').subscribe((value) => {
            this.commentErrorString = value;
        })


    }

    // Attempt to login in through our User service
    postComment() {
        this.ratings.postRating(this.comment).subscribe((resp) => {
            //this.navCtrl.push(MainPage);
        }, (err) => {
            //this.navCtrl.push(MainPage);
            // Unable to log in
            let toast = this.toastCtrl.create({
                message: this.commentErrorString,
                duration: 3000,
                position: 'top'
            });
            toast.present();
        });
    }
}
