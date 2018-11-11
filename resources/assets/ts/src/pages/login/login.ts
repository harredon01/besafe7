import {Component} from '@angular/core';
import {TranslateService} from '@ngx-translate/core';
import {IonicPage, NavController, ToastController, LoadingController} from 'ionic-angular';

import {User} from '../../providers';
import {UserData} from '../../providers/userdata/userdata';
import {MainPage} from '../';
import {SignupPage} from '../signup/signup';

@IonicPage()
@Component({
    selector: 'page-login', 
    templateUrl: 'login.html'
})
export class LoginPage {
    // The account fields for the login form.
    // If you're using the username field with or without email, make
    // sure to add it to the type
    account: {username: string, password: string, remember: boolean} = {
        username: 'harredon01@gmail.com',
        password: '123456',
        remember: false
    };

    // Our translated text strings
    private loginErrorString: string;
    private loginStartString: string;
    loading: any;

    constructor(public navCtrl: NavController,
        public user: User,
        public userData: UserData,
        public toastCtrl: ToastController,
        public translateService: TranslateService, 
        private loadingCtrl: LoadingController) {

        this.translateService.get('LOGIN.LOGIN_ERROR').subscribe((value) => {
            this.loginErrorString = value;
        });
        this.translateService.get('LOGIN.LOGIN_START').subscribe((value) => {
            this.loginStartString = value;
        });
        this.userData.getToken().then((value) => {
            console.log("getToken", value);
            if (value) {
                this.userData.setToken(value);
                this.user.getUser().subscribe((resp: any) => { 
                    if (resp) {
                        console.log("getUser", resp);
                        let push = resp.push;
                        this.userData._user = resp.user;
                        for(let item in push){
                            if(push[item].platform=="food"){
                                this.userData._user.credits = push[item].credits;
                            }
                            
                        }
                        console.log("getUser", this.userData._user);
                        
                    }
                    this.user.saveTokenServer();
                    this.navCtrl.push(MainPage);
                }, (err) => {
                    console.log("getTokenError", value);
                    this.userData.deleteToken();
                    this._loadUserData();
                    // Unable to log in
                    let toast = this.toastCtrl.create({
                        message: this.loginErrorString,
                        duration: 3000,
                        position: 'top'
                    });
                    toast.present();
                });
            } else {
                this._loadUserData();
            }
        });

    }

    // Attempt to login in through our User service
    doLogin() {
        this.showLoader();
        this.user.login(this.account).subscribe((resp) => {
            this.loading.dismiss();
            this.navCtrl.push(MainPage);
        }, (err) => {
            this.loading.dismiss();
            // Unable to log in
            let toast = this.toastCtrl.create({
                message: this.loginErrorString,
                duration: 3000,
                position: 'top'
            });
            toast.present();
        });
    }
    // Attempt to login in through our User service
    _loadUserData() {
        this.userData.getUsername().then((value) => {
            console.log("getUsername", value);
            if (value) {
                this.account.username = value;
            }
        });
        this.userData.getPassword().then((value) => {
            console.log("getPassword", value);
            if (value) {
                this.account.password = value;
            }
        });
        this.userData.getRemember().then((value) => {
            console.log("getRemember", value);
            if (value) {
                if (value == "true") {
                    this.account.remember = true;
                } else if (value == "false") {
                    this.account.remember = false;
                }
                console.log("getRememberRes", this.account.remember);
            }
        });
    }

    singup() {
        this.navCtrl.push(SignupPage)
    }


    showLoader() {
        this.loading = this.loadingCtrl.create({
            content: this.loginStartString
        });

        this.loading.present();
    }
}
