import { HttpClient, HttpClientModule } from '@angular/common/http';
import { ErrorHandler, NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { Camera } from '@ionic-native/camera';
import { SplashScreen } from '@ionic-native/splash-screen';
import { StatusBar } from '@ionic-native/status-bar';
import { Geolocation } from '@ionic-native/geolocation';
import { IonicStorageModule, Storage } from '@ionic/storage';
import { TranslateLoader, TranslateModule } from '@ngx-translate/core';
import { TranslateHttpLoader } from '@ngx-translate/http-loader';
import { IonicApp, IonicErrorHandler, IonicModule } from 'ionic-angular';
import { SQLite } from '@ionic-native/sqlite';
import { InAppBrowser } from '@ionic-native/in-app-browser';
import { Chats } from '../providers/chats/chats'; 
import { Order } from '../providers/order/order'; 
import { Billing } from '../providers/billing/billing'; 
import { OneSignal } from '@ionic-native/onesignal';
import { Items } from '../providers/items/items'; 
import { Ratings } from '../providers/ratings/ratings'; 
import { Addresses } from '../providers/addresses/addresses'; 
import { Deliveries } from '../providers/deliveries/deliveries'; 
import { Settings, User, Api  } from '../providers';
import { UserData } from '../providers/userdata/userdata';
import { DatabaseService } from '../providers/database-service/database-service';
import { MapData } from '../providers/mapdata/mapdata';
import { Cart } from '../providers/cart/cart'; 
import { OrderData } from '../providers/orderdata/orderdata';
import { Map } from '../providers/map/map';
import { Locations } from '../providers/locations/locations';
import { Products } from '../providers/products/products';
import { MyApp } from './app.component';
import { GoogleMaps } from '@ionic-native/google-maps';
import {SignupPage} from '../pages/signup/signup'
import { FoodProvider } from '../providers/food/food';

// The translate loader needs to know where to load i18n files
// in Ionic's static asset pipeline.
export function createTranslateLoader(http: HttpClient) {
  return new TranslateHttpLoader(http, './assets/i18n/', '.json');
}

export function provideSettings(storage: Storage) {
  /**
   * The Settings provider takes a set of default settings for your app.
   *
   * You can add new settings options at any time. Once the settings are saved,
   * these values will not overwrite the saved values (this can be done manually if desired).
   */
  return new Settings(storage, {
    option1: true,
    option2: 'Ionitron J. Framework',
    option3: '3',
    option4: 'Hello'
  });
}

@NgModule({
  declarations: [
    MyApp,
    SignupPage
  ],
  imports: [
    BrowserModule,
    HttpClientModule,
    TranslateModule.forRoot({
      loader: {
        provide: TranslateLoader,
        useFactory: (createTranslateLoader),
        deps: [HttpClient]
      }
    }),
    IonicModule.forRoot(MyApp),
    IonicStorageModule.forRoot()
  ],
  bootstrap: [IonicApp],
  entryComponents: [
    MyApp,
    SignupPage
  ],
  providers: [
    Api,
    Items,
    Geolocation,
    User,
    GoogleMaps,
    UserData,
    Cart,
    Billing,
    Order,
    OrderData,
    Products,
    Map,
    MapData,
    Camera,
    Deliveries,
    Locations,
    SQLite,
    InAppBrowser,
    Ratings,
    Addresses,
    Chats,
    OneSignal,
    DatabaseService,
    SplashScreen,
    StatusBar,
    { provide: Settings, useFactory: provideSettings, deps: [Storage] },
    // Keep this to enable Ionic's runtime error handling during development
    { provide: ErrorHandler, useClass: IonicErrorHandler },
    FoodProvider
  ]
})
export class AppModule { }
