<div ng-controller="GroupsSelectionCtrl">
    <div ng-show="isActive">
        <div ng-hide="editGroup">
            Your groups<br><br>
            <ul>

                <li id="group-@{{ group.id }}" ng-repeat="group in groups">
                    Type: <span class="type">@{{ group.name }}</span><br/>

                    <a href="javascript:;" ng-click="selectGroup(group)" >Select</a>
                    <br ng-show="group.is_selected" />
                    <span ng-show="group.is_selected">Selected</span>
                </li>

            </ul>
            <div class="form-group">
                <div class="col-md-6 col-md-offset-4">
                    <button ng-click="groupToggle()" class="btn btn-primary">New group</button>
                </div>
            </div>
        </div>
        <div ng-show="editGroup">
            <form class="form-horizontal" role="form" name="myForm" ng-submit="save(myForm.$valid)" novalidate>
                <input type="hidden" name="_token" value="{{ csrf_token()}}">
                <div class="form-group">
                    <label class="col-md-4 control-label">Name</label>
                    <div class="col-md-6">
                        <input type="text" ng-model="data.name" class="form-control" name="name" value="{{ old('name')}}" required>
                        <span style="color:red" ng-show="(myForm.name.$dirty && myForm.name.$invalid) || submitted && myForm.name.$invalid">
                            <span ng-show="submitted && myForm.name.$error.required">Please name the group</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-4 control-label">Search</label>
                    <div class="col-md-6">
                        <input type="text" ng-model="searchText" class="form-control" ng-change="getContacts()">
                    </div>

                </div>
                <h2>Selected</h2>
                <ul >

                    <li  ng-repeat="contact in chosen" class="form-group">
                 
                            <label class="col-md-4 control-label"><p>@{{contact.firstName}} @{{contact.lastName}}</p>
                                <p>@{{contact.cellphone}}</p></label>
                            <div class="col-md-6">
                                <a href="javascript:;" ng-click="deleteContact(contact)" >delete</a>
                            </div>
                

                    </li>


                </ul>
                <h2>Contacts</h2>
                <ul >

                    <li  ng-repeat="contact in contacts" class="form-group">
                 
                            <label class="col-md-4 control-label"><p>@{{contact.firstName}} @{{contact.lastName}}</p>
                                <p>@{{contact.cellphone}}</p></label>
                            <div class="col-md-6">
                                <a href="javascript:;" ng-click="selectContact(contact)" >select</a>
                            </div>
                

                    </li>


                </ul>
                <p ng-show="contacts.length==0">You must first add some contacts</p>

                <div class="form-group">
                    <div class="col-md-6 col-md-offset-4">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <button ng-click="groupToggle()" class="btn btn-primary">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="form-group">
            <div class="col-md-6 col-md-offset-4">
                <button ng-click="activation()" class="btn btn-primary">Done</button>
            </div>
        </div>
    </div>
    <div ng-hide="isActive">
        <div class="form-group">
            <div class="col-md-6 col-md-offset-4">
                <button ng-click="activation()" class="btn btn-primary">Groups</button>
            </div>
        </div>
    </div>

</div>
