import 'rxjs/add/operator/toPromise';
import {MapData} from '../mapdata/mapdata';
import {Geolocation} from '@ionic-native/geolocation';
import {
    GoogleMaps,
    LatLng,
    ILatLng,
    GoogleMapsEvent,
    GoogleMapOptions,
    HtmlInfoWindow,
    GeocoderResult,
    Polygon,
    Poly,
    PolylineOptions,
    Geocoder,
    Marker
} from '@ionic-native/google-maps';

import {Injectable} from '@angular/core';


/**
 * Most apps have the concept of a User. This is a simple provider
 * with stubs for login/signup/etc.
 *
 * This User provider makes calls to our API at the `login` and `signup` endpoints.
 *
 * By default, it expects `login` and `signup` to return a JSON object of the shape:
 *
 * ```json
 * {
 *   status: 'success',
 *   user: {
 *     // User fields your app needs, like "id", "name", "email", etc.
 *   }
 * }Ø
 * ```
 *
 * If the `status` field is not `success`, then an error is detected and returned.
 */
@Injectable()
export class Map {
    infoWindow: HtmlInfoWindow;
    constructor(public mapData: MapData,
        private geolocation: Geolocation, ) {
        this.infoWindow = new HtmlInfoWindow();
    }

    /**
     * Creates google maps object
     */
    createMap() {
        let mapOptions: GoogleMapOptions = {
            camera: {
                target: {
                    lat: 4.671659,
                    lng: -74.0524567
                },
                zoom: 16,
                tilt: 30
            }
        };
        let createdmap = GoogleMaps.create('map_canvas', mapOptions);
        return createdmap;
    }
    /**
     * Creates google maps object
     */
    createPolygons(routes: any[]) {
        this.mapData.polygons = [];
        for (let item in routes) {
            let container:any = routes[item];
            console.log("Route",container);
            console.log("coverage",container.coverage);
            
            let points = JSON.parse(container.coverage);
            console.log("points",points);
            let polygonPoints: ILatLng[] = points;
            container.polyPoints = polygonPoints;
            this.mapData.polygons.push(container);
            /*let options: PolylineOptions = {
                'points': polygonPoints
            };
            this.mapData.map.addPolygon(options).then((polygon: Polygon) => {
                this.mapData.addItem(polygon, "polygon");
            });*/
        }
    }

    /**
     * Creates google maps object
     */
    checkIfInRoute(lat: any, long: any) {
        let position: ILatLng = {
            lat: lat,
            lng: long
        };
        let polygons = this.mapData.polygons;
        console.log("Checking polygons", polygons);
        let valid = false;
        for (let item in polygons) {
            let container =  polygons[item];
            console.log("Checking polygon", container);
            let result = Poly.containsLocation(position, container.polyPoints);
            console.log("Checking polygon result", result);
            if (result) {
                return true;
            }
        }
        return valid;
    }
    createDefaultMarkers() {
        let defaultValuesAddress = {
            id: -1,
            user_id: -1,
            lat: 0,
            long: 0,
            content: "",
            name: "La ubicacion",
            icon: "green"
        };
        let container = this.createMarker(defaultValuesAddress, "Address");
        container.setVisible(false);
        container.setDraggable(true);
        container.on(GoogleMapsEvent.MARKER_DRAG_END)
            .subscribe(() => {
                let markerlatlong = container.getPosition();
                console.log("address marker being dragged",markerlatlong);
                let result = this.checkIfInRoute(markerlatlong.lat,markerlatlong.lng);
                if (result) {
                    console.log("Address valid getting data");
                    this.mapData.address.lat = markerlatlong.lat;
                    this.mapData.address.long = markerlatlong.lng;
                    this.getAddressFromLat(markerlatlong.lat, markerlatlong.lng).then((resp) => {
                        console.log("Address from lat", resp);
                        this.mapData.address.address = this.decodeAddressFromLatResult(resp);
                        this.mapData.address.postal = this.decodePostalFromLatResult(resp);
                    }).catch((error) => {
                        console.log('Error getting location', error);
                    });
                } else {
                    console.log("Address not in coverage");
                }
            });
        this.mapData.newAddressMarker = container;
        let defaultValuesReport = {
            id: -1,
            user_id: -1,
            lat: 0,
            long: 0,
            content: "La ubicacion",
            name: "La ubicacion donde ocurrio lo que vas a reportar",
            icon: "red"
        };
        let container2 = this.createMarker(defaultValuesReport, "NewReport");
        container2.setVisible(false);
        container2.setDraggable(true);
        container2.on(GoogleMapsEvent.MARKER_DRAG_END)
            .subscribe(() => {
                let markerlatlong = container2.getPosition();
                this.mapData.address.lat = markerlatlong.lat;
                this.mapData.address.long = markerlatlong.lng;
                this.getAddressFromLat(markerlatlong.lat, markerlatlong.lng).then((resp) => {
                    console.log("Address from lat", resp);
                    this.mapData.address.address = this.decodeAddressFromLatResult(resp);
                    this.mapData.address.postal = this.decodePostalFromLatResult(resp);
                }).catch((error) => {
                    console.log('Error getting location', error);
                });
            });
        this.mapData.newReportMarker = container2;
        let defaultValuesMe = {
            id: -1,
            user_id: -1,
            lat: 0,
            long: 0,
            content: "",
            name: "Mi ubicacion",
            icon: "blue"
        };
        let container3 = this.createMarker(defaultValuesMe, "Me");
        container3.setVisible(false);
        this.mapData.meMarker = container3;
    }
    /**
     * prepares sharer data for creating a marker
     */
    cleanShared(markerData: any) {
        console.log("Shared clean", markerData);
        let contentString = '<div id="content">' +
            '<div id="siteNotice">' +
            '</div>' +
            '<h1 id="firstHeading" class="firstHeading">Uluru</h1>' +
            '<div id="bodyContent">' +
            '<p><b>Uluru</b>, also referred to as <b>Ayers Rock</b>, is a large ' +
            'sandstone rock formation in the southern part of the ' +
            'Northern Territory, central Australia. It lies 335&#160;km (208&#160;mi) ' +
            'south west of the nearest large town, Alice Springs; 450&#160;km ' +
            '(280&#160;mi) by road. Kata Tjuta and Uluru are the two major ' +
            'features of the Uluru - Kata Tjuta National Park. Uluru is ' +
            'sacred to the Pitjantjatjara and Yankunytjatjara, the ' +
            'Aboriginal people of the area. It has many springs, waterholes, ' +
            'rock caves and ancient paintings. Uluru is listed as a World ' +
            'Heritage Site.</p>' +
            '<p>Attribution: Uluru, <a href="https://en.wikipedia.org/w/index.php?title=Uluru&oldid=297882194">' +
            'https://en.wikipedia.org/w/index.php?title=Uluru</a> ' +
            '(last visited June 22, 2009).</p>' +
            '</div>' +
            '</div>';
        markerData.content = contentString;
        return markerData;
    }
    setMarkerPosition(lat, long, marker) {
        let position: ILatLng = {
            lat: lat,
            lng: long
        };
        marker.setPosition(position);
    }
    setCenterMap(lat, long) {
        let position: ILatLng = {
            lat: lat,
            lng: long
        };
        this.mapData.map.setCameraTarget(position);
    }
    getCurrentPosition() {
        return new Promise((resolve, reject) => {
            this.geolocation.getCurrentPosition().then((resp) => {
                // resp.coords.latitude
                // resp.coords.longitude
                resolve(resp);
            }).catch((error) => {
                console.log('Error getting location', error);
                reject(error);
            });

        });
    }
    getAddressFromLat(lat, long) {
        return new Promise((resolve, reject) => {
            let position = new LatLng(lat, long);
            console.log("getAddressFromLat before geocode");
            // latitude,longitude -> address
            Geocoder.geocode({
                "position": position
            }).then((results: GeocoderResult[]) => {
                console.log('location address simple', results);
                if (results.length == 0) {
                    // Not found
                    resolve(null);
                }
                resolve(results);

            }).catch((error) => {
                console.log('Error getAddressFromLat', error);
                reject(error);
            });;
            console.log("getAddressFromLat after geocode");
        });
    }
    /**
     * prepares report data for creating a marker
     */
    decodeAddressFromLatResult(results: any) {

        let container;
        try {
            container = results[0].extra.lines[0];
        }
        catch (err) {

        }

        if (container.length == 0) {
            container = [
                results[0].subThoroughfare || "",
                results[0].thoroughfare || "",
                results[0].locality || "",
                results[0].adminArea || "",
                results[0].postalCode || "",
                results[0].country || ""].join(", ");
        }
        console.log("decodeAddressFromLatResult", container);
        return container;
    }
    /**
     * prepares report data for creating a marker
     */
    decodePostalFromLatResult(results: any) {
        
        let container;
        let found = false
        let i = 0;
        let postal = "";
        do {
            container = results[i]
            try {
                postal = container.postalCode;
                if (postal.length > 0) {
                    found = true;
                }
            }
            catch (err) {

            }
            i++;
            if (i >= results.length) {
                found = true;
            }
        }
        while (found == false);
        console.log("decodePostalFromLatResult",postal);
        return postal;
    }
    /**
     * prepares report data for creating a marker
     */
    cleanReports(markerData: any) {
        console.log("Report clean", markerData);
        return markerData;
    }
    /**
     * prepares merchant data for creating a marker
     */
    cleanMerchants(markerData: any) {
        console.log("merchant clean", markerData);
        return markerData;
    }
    /**
     * prepares stored object data for creating a marker
     */
    cleanObjects(markerData: any) {
        console.log("Object clean", markerData);
        return markerData;
    }
    /**
     * prepares stored object data for creating a marker
     */
    cleanNewReport(markerData: any) {
        console.log("Object clean", markerData);
        return markerData;
    }

    /**
     * prepares stored object data for creating a marker
     */
    cleanMe(markerData: any) {
        console.log("Object clean", markerData);
        return markerData;
    }




    /**
     * Send a POST request to our signup endpoint with the data
     * the user entered on the form.
     */
    createMarker(markerData: any, typeMarker: string) {
        let funcname = "clean" + typeMarker;
        if (typeof this[funcname] === "function") {
            markerData = this[funcname](markerData);
        } else {
            console.log("Type not supported", funcname);
        }
        let user_id = 0;
        if (typeMarker == "Shared") {
            user_id = markerData.user_id
        }
        let draggable = false;
        if(typeMarker == "Address"){
            draggable = true;
        }
        let map = this.mapData.getMap();
        let markerDataObject = {
            title: markerData.name,
            icon: markerData.icon,
            draggable: draggable,
            animation: 'DROP',
            position: {
                lat: markerData.lat,
                lng: markerData.long
            }
        };
        console.log("Create marker", markerDataObject);
        console.log("Create marker type", typeMarker);
        let marker: Marker = map.addMarkerSync(markerDataObject);
        marker.set('user_id', user_id);
        marker.set('id', markerData.id);
        marker.set('content', markerData.content);
        marker.on(GoogleMapsEvent.MARKER_CLICK).subscribe((data) => {
            let htmlInfoWindow = this.infoWindow;
            let ourmap = this.mapData.map;
            let frame: HTMLElement = document.createElement('div');
            frame.innerHTML = marker.get('content');
            htmlInfoWindow.setContent(frame, {
                width: "100px",
                height: "50px"
            });

            htmlInfoWindow.open(marker);

            //this.mapData.map.setZoom(8);
            ourmap.setCameraTarget(marker.getPosition());
        });
        return marker;
    }
    click(marker: any) {
        marker.trigger(GoogleMapsEvent.MARKER_CLICK, marker.getPosition());
    }
    /**
     * Send a POST request to our signup endpoint with the data
     * the user entered on the form.
     */
    updateMarkers(typeObj: string, markerData: any) {
        let target: Marker;
        for (let item in markerData) {
            let container = markerData[item];
            target = this.mapData.getItemUser(container.user_id, typeObj);
            if (target) {
                console.log("Updating marker", target);
                if (typeObj == "Shared") {
                    if (container.id > target['id']) {
                        let position: LatLng = new LatLng(container.latitude, container.longitude);
                        target.setPosition(position);
                        target.setTitle(container.name);
                        target['content'] = container.content;
                        target['id'] = container.id;
                    }
                }
            } else {
                console.log("Creating marker");
                let marker: Marker = this.createMarker(container, typeObj);
                this.mapData.addItem(marker, typeObj);
            }
        }
    }

    /**
     * Log the user out, which forgets the session
     */
    //  logout() {
    //    this._user = null;
    //  }

    /**
     * Process a login/signup response to store user data
     */
    //  _loggedIn(resp) {
    //    this._user = resp.user;
    //  }
}
