import {Component} from '@angular/core';
import {FormBuilder, FormGroup, Validators} from '@angular/forms';
import {IonicPage, ViewController, LoadingController, ToastController} from 'ionic-angular';
import {TranslateService} from '@ngx-translate/core';
import {User} from '../../providers/user/user';
@IonicPage()
@Component({
    selector: 'page-password-update',
    templateUrl: 'password-update.html'
})
export class PasswordUpdatePage {
    isReadyToSave: boolean;
    item: any;
    form: FormGroup;
    private passwordErrorStringSave: string;
    private passwordUpdateStartString: string;
    loading: any;

    constructor(
        public viewCtrl: ViewController,
        formBuilder: FormBuilder,
        public toastCtrl: ToastController,
        public user:User,
        public translateService: TranslateService,
        private loadingCtrl: LoadingController) {
        this.translateService.get('PASSWORD_UPDATE.ERROR_UPDATE').subscribe((value) => {
            this.passwordErrorStringSave = value;
        });
        this.translateService.get('PASSWORD_UPDATE.UPDATE_START').subscribe((value) => {
            this.passwordUpdateStartString = value;
        });
        this.form = formBuilder.group({
            email: ['', Validators.required],
            old_password: ['', Validators.required],
            password: ['', Validators.required],
        });

        // Watch the form for changes, and
        this.form.valueChanges.subscribe((v) => {
            this.isReadyToSave = this.form.valid;
        });
    }

    ionViewDidLoad() {

    }
    /**
           * Send a POST request to our signup endpoint with the data
           * the user entered on the form.
           */
    savePassword(password: any) {
        return new Promise((resolve, reject) => {
            console.log("savePassword", password);
            this.showLoader();
            this.user.updatePassword(password).subscribe((resp: any) => {
                this.loading.dismiss();
                console.log("savePassword result", resp);
                if (resp.status == "success") {
                    resolve(resp);
                } else {
                    resolve(null);
                }
            }, (err) => {
                this.loading.dismiss();
                reject(err);
            });
        });

    }


    /**
     * The user cancelled, so we dismiss without sending data back.
     */
    cancel() {
        this.viewCtrl.dismiss();
    }

    /**
     * The user is done and wants to create the item, so return it
     * back to the presenter.
     */
    done() {
        if (!this.form.valid) {return;}
        this.savePassword(this.form.value).then((value) => {
            console.log("savePassword result", value);
            if (value) {
                this.viewCtrl.dismiss(value);
            } else {
                // Unable to log in
                let toast = this.toastCtrl.create({
                    message: this.passwordErrorStringSave,
                    duration: 3000,
                    position: 'top'
                });
                toast.present();
            }
        }).catch((error) => {
            console.log('Error savePassword', error);
            let toast = this.toastCtrl.create({
                message: this.passwordErrorStringSave,
                duration: 3000,
                position: 'top'
            });
            toast.present();
        });;

    }
    showLoader() {
        this.loading = this.loadingCtrl.create({
            content: this.passwordUpdateStartString
        });
        this.loading.present();
    }
}
