<form class="form-horizontal" role="form" name="myForm" ng-submit="save(myForm.$valid)" novalidate>
                            <input type="hidden" name="_token" value="{{ csrf_token()}}">
                            <input type="hidden" ng-model="data.address_id" name="address_id" value="">
                            <input type="hidden" ng-model="data.section" name="section" value="profile">
                            <div class="form-group">
                                <label class="col-md-4 control-label">First Name</label>
                                <div class="col-md-6">
                                    <input type="text" ng-model="data.firstName" class="form-control" name="firstName" value="{{ old('firstName')}}" required>
                                    <span style="color:red" ng-show="(myForm.firstName.$dirty && myForm.firstName.$invalid) || submitted && myForm.firstName.$invalid">
                                        <span ng-show="submitted && myForm.firstName.$error.required">Porfavor ingresa tu nombre</span>                                
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">Last Name</label>
                                <div class="col-md-6">
                                    <input type="text" ng-model="data.lastName" class="form-control" name="lastName" value="{{ old('lastName')}}" required>
                                    <span style="color:red" ng-show="(myForm.lastName.$dirty && myForm.lastName.$invalid) || submitted && myForm.lastName.$invalid">
                                        <span ng-show="submitted && myForm.lastName.$error.required">Porfavor Ingresa el apellido</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-4 control-label">Address</label>
                                <div class="col-md-6">
                                    <input type="text" ng-model="data.address" class="form-control" name="address" value="{{ old('address')}}" required>
                                    <span style="color:red" ng-show="(myForm.address.$dirty && myForm.address.$invalid) || submitted && myForm.address.$invalid">
                                        <span ng-show="submitted && myForm.address.$error.required">Porfavor ingresa la direccion</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-4 control-label">Telefono</label>
                                <div class="col-md-6">
                                    <input type="text" ng-model="data.phone" class="form-control" name="phone" value="{{ old('phone')}}" required>
                                    <span style="color:red" ng-show="(myForm.phone.$dirty && myForm.phone.$invalid) || submitted && myForm.phone.$invalid">
                                        <span ng-show="submitted && myForm.phone.$error.required">Porfavor ingresa un telefono</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-4 control-label">Codigo Postal</label>
                                <div class="col-md-6">
                                    <input type="text" ng-model="data.postal" class="form-control" name="postal" value="{{ old('postal')}}" required>
                                    <span style="color:red" ng-show="(myForm.postal.$dirty && myForm.postal.$invalid) || submitted && myForm.postal.$invalid">
                                        <span ng-show="submitted && myForm.postal.$error.required">Porfavor ingresa un codigo postal</span>
                                </div>
                            </div>
                            <div class="form-group" ng-show="cityVisible">
                                <label class="col-md-4 control-label">City</label>
                                <div class="col-md-6">
                                    <select ng-model="data.city_id" name="city_id" required>
                                        <option ng-repeat="city in cities" value="@{{city.id}}">@{{city.name}}</option>
                                    </select>
                                    <span style="color:red" ng-show="(myForm.city_id.$dirty && myForm.city_id.$invalid) || submitted && myForm.city_id.$invalid">
                                        <span ng-show="submitted && myForm.city_id.$error.required">Porfavor Selecciona una Ciudad</span>
                                </div>
                            </div>
                            <div class="form-group" ng-show="regionVisible">
                                <label class="col-md-4 control-label">Region</label>
                                <div class="col-md-6">
                                    <select ng-model="data.region_id" name="region_id" ng-change="selectRegion(data.region_id)" required>
                                        <option ng-repeat="region in regions" value="@{{region.id}}">@{{region.name}}</option>
                                    </select>
                                    <span style="color:red" ng-show="(myForm.region_id.$dirty && myForm.region_id.$invalid) || submitted && myForm.region_id.$invalid">
                                        <span ng-show="submitted && myForm.region_id.$error.required">Porfavor Selecciona una Region</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">Country</label>
                                <div class="col-md-6">
                                    <select ng-model="data.country_id" name="country_id" ng-change="selectCountry(data.country_id)" >
                                        <option ng-repeat="country in countries" value="@{{country.id}}">@{{country.name}}</option>
                                    </select>
                                    <span style="color:red" ng-show="(myForm.country_id.$dirty && myForm.country_id.$invalid) || submitted && myForm.country_id.$invalid">
                                        <span ng-show="submitted && myForm.country_id.$error.required">Porfavor Selecciona un país</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-4">
                                    <button type="submit" class="btn btn-primary">Save</button>
                                    <button ng-click="clean()" class="btn btn-primary">Clean</button>

                                </div>
                            </div>
                        </form>