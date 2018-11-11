import {Component} from '@angular/core';
import {TranslateService} from '@ngx-translate/core';
import {FormBuilder, FormGroup, Validators} from '@angular/forms';
import {IonicPage, NavController, ToastController, LoadingController} from 'ionic-angular';

import {User} from '../../providers';
import {MainPage} from '../';
import {HomePage} from '../home/home';

@IonicPage()
@Component({
    selector: 'page-signup',
    templateUrl: 'signup.html'
})
export class SignupPage {
    // The account fields for the login form.
    // If you're using the username field with or without email, make
    // sure to add it to the type
    account: {
        firstName: string,
        lastName: string,
        area_code: number,
        cellphone: number,
        email: string,
        password: string
        password_confirmation: string,
        language: string,
        city_id: number,
        region_id: number
        country_id: number
    } = {
            firstName: '',
            lastName: '',
            area_code: 57,
            cellphone: 0,
            email: '',
            password: '',
            password_confirmation: '',
            language: 'es',
            city_id: 524,
            region_id: 11,
            country_id: 1
        };

    registrationForm: FormGroup;
    submitAttempt: boolean = false;
    passwordError: boolean = false;
    loading: any;

    // Our translated text strings
    private signupErrorString: string;
    private signupStartString: string;

    constructor(public navCtrl: NavController,
        public user: User,
        public toastCtrl: ToastController,
        public translateService: TranslateService,
        public formBuilder: FormBuilder, private loadingCtrl: LoadingController) {

        this.translateService.get('SIGNUP.ERROR_SAVE').subscribe((value) => {
            this.signupErrorString = value;
        });
        this.translateService.get('SIGNUP.SAVE_START').subscribe((value) => {
            this.signupStartString = value;
        });
        this.registrationForm = formBuilder.group({
            password: ['', Validators.compose([Validators.maxLength(30), Validators.minLength(6), Validators.pattern("^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])(?=.{8,})"), Validators.required])],
            password_confirmation: ['', Validators.compose([Validators.maxLength(30), Validators.minLength(6), Validators.pattern("^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])(?=.{8,})"), Validators.required])],
            cellphone: ['', Validators.compose([Validators.maxLength(30), Validators.pattern('[0-9._%+-]*'), Validators.required])],
            firstName: ['', Validators.compose([Validators.maxLength(30), Validators.pattern('[a-zA-Z ]*'), Validators.required])],
            lastName: ['', Validators.compose([Validators.maxLength(30), Validators.pattern('[a-zA-Z ]*'), Validators.required])],
            email: ['', Validators.compose([Validators.maxLength(30), Validators.pattern('[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$'), Validators.required])]
        });
    }

    doSignup() {
        this.submitAttempt = true;
        this.passwordError = false;
        if (!this.registrationForm.valid) {return;}
        if (this.account.password != this.account.password_confirmation) {
            this.passwordError = true;
            return;
        }
        this.showLoader();
        // Attempt to login in through our User service
        this.user.signup(this.account).subscribe((resp) => {
            this.loading.dismiss();
            this.navCtrl.push(HomePage);
        }, (err) => {
            // Unable to sign up
            let toast = this.toastCtrl.create({
                message: this.signupErrorString,
                duration: 3000,
                position: 'top'
            });
            this.loading.dismiss();

            toast.present();
        });
    }

    showLoader() {
        this.loading = this.loadingCtrl.create({
            content: this.signupStartString
        });

        this.loading.present();
    }
}
