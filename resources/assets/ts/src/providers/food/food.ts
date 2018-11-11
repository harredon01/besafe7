import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Api } from '../api/api';
import { UserData } from '../userdata/userdata';

/*
  Generated class for the FoodProvider provider.

  See https://angular.io/guide/dependency-injection for more info on providers
  and Angular DI.
*/
@Injectable()
export class FoodProvider {

  constructor(public http: HttpClient, public api: Api, public userData: UserData) {
    console.log('Hello FoodProvider Provider');
  }


  getDeliveryByDateTimeRange(range) {
      let seq = this.api.get(`/deliveries?delivery>${range.init}&delivery<${range.end}&status=pending`).share();
      seq.subscribe((data: any) => {
        return data;
      }, err => {
        console.error('ERROR', err);
      });

      return seq;
  }

  getArticlesByDateTimeRange(range){
    let seq = this.api.get(`/articles?start_date<${range.init}&end_date>${range.end}&includes=file`).share();
    seq.subscribe((data: any) => {
      console.log(JSON.stringify(data));
      return data;
    }, err => {
      console.error('ERROR', err);
    });

    return seq;
  }

  updateDeliveryInformation(delivery){
    let seq = this.api.post(`/deliveries/options`, delivery).share();
    seq.subscribe((data: any) => {
      console.log(JSON.stringify(data));
      return data;
    }, err => {
      console.error('ERROR', err);
    });

    return seq;
  }

}
