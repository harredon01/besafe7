<div class="col-md-6 col-md-offset-4" ng-hide="showForm">
    <button ng-click="newOauthClient()" class="btn btn-primary">Nuevo</button>
</div>
<form class="form-horizontal" ng-show="showForm" role="form" name="myForm" ng-submit="save(myForm.$valid)" novalidate>
                            <input type="hidden" name="_token" value="{{ csrf_token()}}">
                            <input type="hidden" ng-model="data.id" name="id" value="">
                            

                            <div class="form-group">
                                <label class="col-md-4 control-label">Name</label>
                                <div class="col-md-6">
                                    <input type="text" ng-model="data.name" class="form-control" name="name" value="{{ old('name')}}" required>
                                    <span style="color:red" ng-show="(myForm.name.$dirty && myForm.name.$invalid) || submitted && myForm.name.$invalid">
                                        <span ng-show="submitted && myForm.name.$error.required">Dale un nombre a tu cliente</span>                                
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">Redirect</label>
                                <div class="col-md-6">
                                    <input type="text" ng-model="data.redirect" class="form-control" name="redirect" value="{{ old('redirect')}}" required>
                                    <span style="color:red" ng-show="(myForm.redirect.$dirty && myForm.redirect.$invalid) || submitted && myForm.redirect.$invalid">
                                        <span ng-show="submitted && myForm.redirect.$error.required">Ingresa a donde se debe redirigir</span>
                                </div>
                            </div>
                            

                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-4">
                                    <button type="submit" class="btn btn-primary">Save</button>
                                    <button ng-click="clean()" class="btn btn-primary">Clean</button>

                                </div>
                            </div>
                        </form>