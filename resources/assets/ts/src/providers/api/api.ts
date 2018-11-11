import { HttpClient, HttpParams  } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { UserData } from '../userdata/userdata';

/**
 * Api is a generic REST Api handler. Set your API url first.
 */
@Injectable()
export class Api {
  url: string = 'http://hoovert.com/api';
  urlsite: string = 'http://hoovert.com';

  constructor(public http: HttpClient,
  public userData: UserData) {
  }

  get(endpoint: string, params?: any, reqOpts?: any) {
    if (!reqOpts) {
      reqOpts = {
        params: new HttpParams()
      };
    }

    // Support easy query params for GET requests
    if (params) {
      reqOpts.params = new HttpParams();
      for (let k in params) {
        reqOpts.params = reqOpts.params.set(k, params[k]);
      }
    }
    
    reqOpts = this.buildHeaders(reqOpts);
    return this.http.get(this.url + endpoint, reqOpts);
  }
  buildHeaders(reqOpts) {
      if(reqOpts){
          reqOpts.headers = this.userData._headers;
      } else {
          reqOpts = {
            headers: this.userData._headers
          };
      }
      return reqOpts;
    //return this.http.post(this.url + '/' + endpoint, body, reqOpts);
  }

  post(endpoint: string, body: any, reqOpts?: any) {
      console.log("body",body);
      
      console.log("Endopoint",endpoint);
     let urlF = this.url  + endpoint;
    if(endpoint == "/oauth/token"){
        urlF = this.urlsite  + endpoint;
    }
    reqOpts = this.buildHeaders(reqOpts);
    return this.http.post(urlF, body, reqOpts);
  }

  put(endpoint: string, body: any, reqOpts?: any) {
      reqOpts = this.buildHeaders(reqOpts);
    return this.http.put(this.url + endpoint, body, reqOpts);
  }

  delete(endpoint: string, reqOpts?: any) {
      reqOpts = this.buildHeaders(reqOpts);
    return this.http.delete(this.url + endpoint, reqOpts);
  }

  patch(endpoint: string, body: any, reqOpts?: any) {
      reqOpts = this.buildHeaders(reqOpts);
    return this.http.patch(this.url + endpoint, body, reqOpts);
  }
}
