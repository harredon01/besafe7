<div class="col-md-6 col-md-offset-4" ng-hide="showForm">
    <button ng-click="newPersonalAccessToken()" class="btn btn-primary">Nuevo</button>
    <div ng-show="showToken">Token: @{{token}}</div>
</div>
<form class="form-horizontal" role="form" ng-show="showForm" name="myForm" ng-submit="save(myForm.$valid)" novalidate>
                            <input type="hidden" name="_token" value="{{ csrf_token()}}">
                            <input type="hidden" ng-model="data.id" name="id" value="">
                            <input type="hidden" ng-model="data.scopes" name="scopes" value="">
                            <div class="form-group">
                                <label class="col-md-4 control-label">Name</label>
                                <div class="col-md-6">
                                    <input type="text" ng-model="data.name" class="form-control" name="name" value="{{ old('name')}}" required>
                                    <span style="color:red" ng-show="(myForm.name.$dirty && myForm.name.$invalid) || submitted && myForm.name.$invalid">
                                        <span ng-show="submitted && myForm.name.$error.required">Porfavor ingresa un nombre para el token</span>                                
                                </div>
                            </div>

                            
                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-4">
                                    <button type="submit" class="btn btn-primary">Save</button>
                                    <button ng-click="clean()" class="btn btn-primary">Clean</button>

                                </div>
                            </div>
                        </form>