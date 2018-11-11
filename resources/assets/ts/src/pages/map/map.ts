import {Component} from '@angular/core';
import {IonicPage, NavController, ModalController, NavParams, LoadingController} from 'ionic-angular';
import {Map} from '../../providers/map/map';
import {MapData} from '../../providers/mapdata/mapdata';
import {Locations} from '../../providers/locations/locations';

@IonicPage()
@Component({
    selector: 'page-map',
    templateUrl: 'map.html'
})
export class MapPage {

    public mapActive: boolean;
    loading: any;
    public sharedLocationsFetched: boolean;


    constructor(public navCtrl: NavController,
        public map: Map,
        public modalCtrl: ModalController,
        public mapData: MapData,
        public navParams: NavParams,
        private loadingCtrl: LoadingController,
        public locations: Locations) {
        this.sharedLocationsFetched = false;
    }
    showLoader() {
        this.loading = this.loadingCtrl.create({
            content: 'Estamos Comparando tu ubicacion con las zonas de cobertura'
        });

        this.loading.present();
    }

    showNotCoverage() {
        this.loading = this.loadingCtrl.create({
            content: 'Lo sentimos no tenemos cobertura en esa zona'
        });

        this.loading.present();
    }
    ionViewDidLoad(name) {
        console.log("ionViewDidLoad map page");
        this.mapData.map = this.map.createMap();
        this.map.createDefaultMarkers();
        this.mapActive = true;
    }
    ionViewWillLeave() {
        this.mapActive = false;
        console.log("Looks like I'm about to leave :(");
    }
    ionViewDidEnter() {
        console.log("ionViewDidEnter map page");
        this.mapActive = true;
        let page = 1;
        if (!this.sharedLocationsFetched) {
            this.sharedLocationsFetched = true;
            this.getSharedLocationsPage(page);
        }
        this.buildMapStatus();
    }
    getSharedLocationsPage(page: any) {
        if (this.mapActive) {
            this.locations.getSharedLocationsExternal(page).then((data) => {
                console.log("getSharedResults", data);
                if (data['last_page'] > page) {
                    console.log("Not last page");
                    page++;
                    this.getSharedLocationsPage(page);
                }
                this.map.updateMarkers("Shared", data['data']);
                this.locations.saveLocations(data['data']);
                if (data['last_page'] == page) {
                    console.log("Last page");
                    //                    if (!this.sharedLocationsFetched) {
                    //                        
                    //                    }
                    //this.navCtrl.parent.select(2);
                    if (data['total'] > 0) {
                        let vm = this;
                        setTimeout(function () {
                            let page = 1;
                            vm.getSharedLocationsPage(page);
                        }, 10000);
                    } else {
                        this.sharedLocationsFetched = false;
                    }
                }
            }, (err) => {
                this.buildMapStatus();
                console.log("getSharedLocation Error", err);
            });
        }

    }
    buildMapStatus() {
        if (!this.mapData.activeType) {
            this.mapData.activeType = "User";
        }
        if (this.mapData.activeType.length == 0) {
            this.mapData.activeType = "User";
        }
        let funcname = "handle" + this.mapData.activeType + "Active";
        console.log("trying function", funcname);
        if (typeof this[funcname] === "function") {
            this[funcname]();
        } else {
            console.log("Type not supported", funcname);
        }
    }
    handleMeActive() {

    }

    getMyLocationAddressPostal() {
        this.map.getCurrentPosition().then((resp: any) => {
            console.log("Get location response", resp);
            this.map.setMarkerPosition(resp.coords.latitude, resp.coords.longitude, this.mapData.newAddressMarker);
            this.map.setCenterMap(resp.coords.latitude, resp.coords.longitude);
            this.mapData.address.lat = resp.coords.latitude;
            this.mapData.address.long = resp.coords.longitude;
            let valid = this.map.checkIfInRoute(resp.coords.latitude, resp.coords.longitude);
            // resp.coords.latitude
            // resp.coords.longitude
            if (valid) {
                console.log("Address valid getting rest of data");
                this.map.getAddressFromLat(resp.coords.latitude, resp.coords.longitude).then((resp) => {
                    console.log("getAddressFromLat response", resp);
                    this.mapData.address.address = this.map.decodeAddressFromLatResult(resp);
                    console.log("getAddressFromLat mid");
                    this.mapData.address.postal = this.map.decodePostalFromLatResult(resp);
                    this.loading.dismiss();
                    // resp.coords.longitude
                    //                    console.log("Before timeout");
                    //                    setTimeout(function () {
                    //                        console.log("after timeout");
                    //                        vm.completeAddressData();
                    //                    }, 3000);
                }).catch((error) => {
                    console.log('Error getting location', error);
                });
            } else {
                console.log("Address out of coverage");
                this.showNotCoverage();
                let vr = this;
                setTimeout(function () {
                    vr.loading.dismiss();
                }, 3000);

            }
        }).catch((error) => {
            console.log('Error getting location', error);
        });
    }

    handleAddressActive() {
        console.log("handleAddressActive",this.mapData.activeId);
        this.showLoader();
        this.mapData.newAddressMarker.setDraggable(true);
        let vm = this;
        if (this.mapData.activeId == "-1" || this.mapData.activeId == "0") {
            this.locations.getActivePolygons("bogota").subscribe((data: any) => {
                console.log("Active routes", data);
                let routes = data.data;
                this.map.createPolygons(routes);
                this.getMyLocationAddressPostal();
            }, (err) => {
                console.log("getActiveRoutes Error", err);
            });
        }
        this.mapData.newAddressMarker.setVisible(true);
    }
    completeAddressData() {
        console.log("completeAddressData", this.mapData.address);
        let container;
        if (this.mapData.activeId == "-1" || this.mapData.activeId == "0") {
            container = {
                lat: this.mapData.address.lat,
                long: this.mapData.address.long,
                address: this.mapData.address.address,
                postal: this.mapData.address.postal,
                type: "shipping"
            }
        } else {
            container = {
                lat: this.mapData.address.lat,
                long: this.mapData.address.long,
                address: this.mapData.address.address,
                id: this.mapData.address.id,
                phone: this.mapData.address.phone,
                name: this.mapData.address.name,
                postal: this.mapData.address.postal,
                type: "shipping"
            }
        }
        let addModal = this.modalCtrl.create('AddressCreatePage', container);
            addModal.onDidDismiss(item => {
                if (item) {
                    if (this.mapData.activeId == "-1") {
                        this.navCtrl.parent.select(2);
                    } else if (this.mapData.activeId == "0") {
                        this.navCtrl.parent.select(4);
                    }
                    console.log("Process complete, address created", item);
                }
            })
            addModal.present();

    }
    cancel() {
        console.log("Cancel");
        this.mapData.newAddressMarker.setVisible(false);
        this.navCtrl.parent.select(0);
    }
    handleUserActive() {
        let container = this.mapData.getItemUser(this.mapData.activeId, "Shared");
        if (container) {
            this.map.click(container);
        } else {
            let holders = this.mapData.shared;
            if (holders.length > 0) {
                console.log("Setting map center");
                this.map.click(holders[0]);
            } else {
                console.log("No user data defaulting to me active");
                this.mapData.activeId = -1 + "";
                this.mapData.activeType = "me";
                this.handleMeActive();
            }
        }
    }

}
