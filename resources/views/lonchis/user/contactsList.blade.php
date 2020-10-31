
                    <div>
                        Your contacts<br><br>
                        <ul>

                            <li id="contact-@{{ contact.id }}" ng-repeat="contact in contacts">
                                Type: <span class="type">@{{ contact.name }}</span><br/><a href="javascript:;" ng-click="selectGroup(contact.id)" >Select</a>
                            </li>

                        </ul>
                    </div>


