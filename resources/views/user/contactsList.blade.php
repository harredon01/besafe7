
                    <div>
                        Listed Addresses<br><br>
                        <ul>

                            <li id="group-@{{ group.id }}" ng-repeat="group in groups">
                                Type: <span class="type">@{{ group.name }}</span><br/><a href="javascript:;" ng-click="selectGroup(group.id)" >Select</a>
                            </li>

                        </ul>
                    </div>


