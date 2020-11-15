

<div>
    <div id="checkout-payment">
        <button ng-click="showMethod('CC')" class="btn btn-primary">Tarjeta de Credito</button>
        <button ng-click="showMethod('PSE')" class="btn btn-primary">Tarjeta de Debito</button>
        <button ng-click="showMethod('BALOTO')" class="btn btn-primary">Efectivo</button>
        <br/>
        <br/>
    </div>
    <div ng-hide="showResult">
        <div class="credito" ng-show="credito">
            <form class="form-horizontal" role="form" name="myForm2" ng-submit="payCreditCard(myForm2.$valid)" novalidate>
                <input type="hidden" name="_token" value="{{ csrf_token()}}">
                <input type="hidden" ng-model="data2.payment_id" name="payment_id" value="">
                


                <div class="form-group">
                    <label class="col-md-4 control-label">Número de tarjeta    (@{{data2.cc_number.length}})</label>
                    <div class="col-md-6">
                        <input type="tel" ng-keyup="creditTab($event)" placeholder="XXXX-XXXX-XXXX-XXXX" ng-model="data2.cc_number" class="form-control" name="cc_number" value="{{ old('cc_number')}}" required>
                        <span style="color:red" ng-show="(myForm2.cc_number.$dirty && myForm2.cc_number.$invalid) || submitted2 && myForm2.cc_number.$invalid">
                            <span ng-show="submitted2 && myForm2.cc_number.$error.required">Porfavor Ingresa el número de la tarjeta</span>
                    </div>
                </div>
                <div class="form-group" >
                    <label class="col-md-4 control-label">Franquisia</label>
                    <div class="col-md-6">
                        <select ng-model="data2.cc_branch" name="cc_branch"  class="form-control nice-select"  required>
                            <option value="VISA">Visa</option>
                            <option value="MASTERCARD">Master Card</option>
                            <option value="AMEX">Amex</option>
                            <option value="DINERS">Diners</option>
                            <option value="DISCOVER">Discover</option>
                            <option value="NARANJA">Naranja</option>
                            <option value="CABAL">Cabal</option>
                            <option value="ARGENCARD">Argencard</option>
                            <option value="CODENSA">Codensa</option>
                            <option value="CMR">CMR</option>
                        </select>
                        <span style="color:red" ng-show="(myForm2.cc_branch.$dirty && myForm2.cc_branch.$invalid) || submitted2 && myForm2.cc_branch.$invalid">
                            <span ng-show="submitted2 && myForm2.cc_branch.$error.required">Porfavor Selecciona la franquisia</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="col-md-4 control-label">Codigo de Seguridad</label>
                    <div class="col-md-6">
                        
                        <input type="tel" ng-keyup="keytab($event,2)" style="width: 20%;float: left;" ng-model="data2.cc_expiration_month" placeholder="MM" class="form-control" name="cc_expiration_month" value="{{ old('cc_expiration_month')}}" required>
                        <input type="tel" ng-keyup="keytab($event,2)" style="width: 20%;float: left; margin-right: 40px" ng-model="data2.cc_expiration_year" placeholder="YY" class="form-control" name="cc_expiration_year" value="{{ old('cc_expiration_year')}}" required>
                        <input type="tel" style="width: 20%;float: left; margin-left: 40px" ng-model="data2.cc_security_code" placeholder="CVV" class="form-control" name="cc_security_code" value="{{ old('cc_security_code')}}" required>
                        <span style="color:red" ng-show="(myForm2.cc_security_code.$dirty && myForm2.cc_security_code.$invalid) || submitted2 && myForm2.cc_security_code.$invalid">
                            <span ng-show="submitted2 && myForm2.cc_security_code.$error.required">Porfavor ingresa el número de seguridad</span></span>

                        <span style="color:red" ng-show="(myForm2.cc_expiration_year.$dirty && myForm2.cc_expiration_year.$invalid) || submitted2 && myForm2.cc_expiration_year.$invalid">
                            <span ng-show="submitted2 && myForm2.cc_expiration_year.$error.required">Porfavor Selecciona año de vencimiento</span></span>

                        <span style="color:red" ng-show="(myForm2.cc_expiration_month.$dirty && myForm2.cc_expiration_month.$invalid) || submitted2 && myForm2.cc_expiration_month.$invalid">
                            <span ng-show="submitted2 && myForm2.cc_expiration_month.$error.required">Porfavor Selecciona mes de vencimiento</span></span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-4 control-label">Nombre En la tarjeta</label>
                    <div class="col-md-6">
                        <input type="text" ng-model="data2.cc_name" class="form-control" ng-change="populatePayer()" name="cc_name" value="{{ old('cc_name')}}" required>
                        <span style="color:red" ng-show="(myForm2.cc_name.$dirty && myForm2.cc_name.$invalid) || submitted2 && myForm2.cc_name.$invalid">
                            <span ng-show="submitted2 && myForm2.cc_name.$error.required">Porfavor ingresa el nombre que aparece en la tarjeta</span>   </span>                              
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-4 control-label">Usar mis datos</label>
                    <div class="col-md-6">
                        <input type="checkbox" ng-model="use_user" ng-click="useUserCredit()">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-4 control-label">Correo del propietario de la tarjeta</label>
                    <div class="col-md-6">
                        <input type="email" ng-model="data2.payer_email" class="form-control" name="payer_email" value="{{ old('payer_email')}}" required>
                        <span style="color:red" ng-show="(myForm2.payer_email.$dirty && myForm2.payer_email.$invalid) || submitted2 && myForm2.payer_email.$invalid">
                            <span ng-show="submitted2 && myForm2.payer_email.$error.required">Porfavor Ingresa el correo del propietario de la tarjeta</span></span> 
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-4 control-label">Nombre del propietario de la tarjeta</label>
                    <div class="col-md-6">
                        <input type="text" ng-model="data2.payer_name" class="form-control" name="payer_name" value="{{ old('payer_name')}}" required>
                        <span style="color:red" ng-show="(myForm2.payer_name.$dirty && myForm2.payer_name.$invalid) || submitted2 && myForm2.payer_name.$invalid">
                            <span ng-show="submitted2 && myForm2.payer_name.$error.required">Porfavor Ingresa el nombre del propietario de la tarjeta</span></span> 
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-4 control-label">Cédula del propietario de la tarjeta</label>
                    <div class="col-md-6">
                        <input type="text" ng-model="data2.payer_id" class="form-control" name="payer_id" value="{{ old('payer_id')}}" required>
                        <span style="color:red" ng-show="(myForm2.payer_id.$dirty && myForm2.payer_id.$invalid) || submitted2 && myForm2.payer_id.$invalid">
                            <span ng-show="submitted2 && myForm2.payer_id.$error.required">Porfavor ingresa la cédula del propietario de la tarjeta</span></span> 
                    </div>
                </div>
                <br/>
                <div class="payer_address" ng-hide="(myForm2.payer_id.$invalid)||
                            (myForm2.payer_name.$invalid)||(myForm2.payer_email.$invalid)||
                            (myForm2.payer_email.$invalid)||(myForm2.cc_expiration_year.$invalid)">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Direccion del pagador</label>
                        <div class="col-md-6">
                            <label for="male">Igual a Direccion de envío</label>
                            <input type="radio" id="male" ng-click="fetchAddressForUse('shipping', 'payer')" style="width:20%" name="gender" value="male">
                            <label for="female">Usar direccion guardada</label>
                            <input type="radio" id="female" name="gender" ng-click="fetchAddressForUse('other', 'payer')" style="width:20%" value="female">
                            <label for="other">Nueva Direccion</label>
                            <input type="radio" id="other" name="gender" style="width:20%" value="other">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Direccion</label>
                        <div class="col-md-6">
                            <input type="text" ng-model="data2.payer_address" class="form-control" name="payer_address" value="{{ old('payer_address')}}" required>
                            <span style="color:red" ng-show="(myForm2.payer_address.$dirty && myForm2.payer_address.$invalid) || submitted2 && myForm2.payer_address.$invalid">
                                <span ng-show="submitted2 && myForm2.payer_address.$error.required">Porfavor ingresa una direccion</span></span> 
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-4 control-label">Codigo postal</label>
                        <div class="col-md-6">
                            <input type="text" ng-model="data2.payer_postal" class="form-control" name="payer_postal" value="{{ old('payer_postal')}}" required>
                            <span style="color:red" ng-show="(myForm2.payer_postal.$dirty && myForm2.payer_postal.$invalid) || submitted2 && myForm2.payer_postal.$invalid">
                                <span ng-show="submitted2 && myForm2.payer_postal.$error.required">Porfavor ingresa un codigo postal</span></span> 
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Ciudad</label>
                        <div class="col-md-6">
                            <input type="text" ng-model="data2.payer_city" class="form-control" name="payer_city" value="{{ old('payer_city')}}" required>
                            <span style="color:red" ng-show="(myForm2.payer_city.$dirty && myForm2.payer_city.$invalid) || submitted2 && myForm2.payer_city.$invalid">
                                <span ng-show="submitted2 && myForm2.payer_city.$error.required">Porfavor ingresa la ciudad</span></span> 
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Departamento</label>
                        <div class="col-md-6">
                            <input type="text" ng-model="data2.payer_state" class="form-control" name="payer_state" value="{{ old('payer_state')}}" required>
                            <span style="color:red" ng-show="(myForm2.payer_state.$dirty && myForm2.payer_state.$invalid) || submitted2 && myForm2.payer_state.$invalid">
                                <span ng-show="submitted2 && myForm2.payer_state.$error.required">Porfavor ingresa el departamento</span></span> 
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Pais</label>
                        <div class="col-md-6">
                            <input type="text" ng-model="data2.payer_country" class="form-control" name="payer_country" value="{{ old('payer_country')}}" required>
                            <span style="color:red" ng-show="(myForm2.payer_country.$dirty && myForm2.payer_country.$invalid) || submitted2 && myForm2.payer_country.$invalid">
                                <span ng-show="submitted2 && myForm2.payer_country.$error.required">Porfavor ingresa el pais</span></span> 
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Telefono</label>
                        <div class="col-md-6">
                            <input type="tel" ng-model="data2.payer_phone" class="form-control" name="payer_phone" value="{{ old('payer_phone')}}" required>
                            <span style="color:red" ng-show="(myForm2.payer_phone.$dirty && myForm2.payer_phone.$invalid) || submitted2 && myForm2.payer_phone.$invalid">
                                <span ng-show="submitted2 && myForm2.payer_phone.$error.required">Porfavor ingresa un telefono</span></span> 
                        </div>
                    </div>
                </div>
                <br/>
                <div class="buyer_address" ng-hide="(myForm2.payer_id.$invalid)||
                            (myForm2.payer_name.$invalid)||(myForm2.payer_email.$invalid)||
                            (myForm2.payer_email.$invalid)||(myForm2.cc_expiration_year.$invalid)" style="margin-top: 20px">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Direccion del comprador</label>
                        <div class="col-md-6">
                            <label for="male">Igual a Direccion de envío</label>
                            <input type="radio" id="male" ng-click="fetchAddressForUse('shipping', 'buyer')" style="width:20%" name="gender2" value="male">
                            <label for="female">Usar direccion guardada</label>
                            <input type="radio" id="female" style="width:20%" name="gender2" ng-click="fetchAddressForUse('other', 'buyer')" value="female">
                            <label for="other">Nueva Direccion</label>
                            <input type="radio" id="other" style="width:20%" name="gender2" value="other">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Direccion</label>
                        <div class="col-md-6">
                            <input type="text" ng-model="data2.buyer_address" class="form-control" name="buyer_address" value="{{ old('buyer_address')}}" required>
                            <span style="color:red" ng-show="(myForm2.buyer_address.$dirty && myForm2.buyer_address.$invalid) || submitted2 && myForm2.buyer_address.$invalid">
                                <span ng-show="submitted2 && myForm2.buyer_address.$error.required">Porfavor ingresa la direccion</span></span> 
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-4 control-label">Codigo postal</label>
                        <div class="col-md-6">
                            <input type="text" ng-model="data2.buyer_postal" class="form-control" name="buyer_postal" value="{{ old('buyer_postal')}}" required>
                            <span style="color:red" ng-show="(myForm2.buyer_postal.$dirty && myForm2.buyer_postal.$invalid) || submitted2 && myForm2.buyer_postal.$invalid">
                                <span ng-show="submitted2 && myForm2.buyer_postal.$error.required">Porfavor ingresa el codigo postal</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Ciudad</label>
                        <div class="col-md-6">
                            <input type="text" ng-model="data2.buyer_city" class="form-control" name="buyer_city" value="{{ old('buyer_city')}}" required>
                            <span style="color:red" ng-show="(myForm2.buyer_city.$dirty && myForm2.buyer_city.$invalid) || submitted2 && myForm2.buyer_city.$invalid">
                                <span ng-show="submitted2 && myForm2.buyer_city.$error.required">Porfavor ingresa la ciudad</span></span> 
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Departamento</label>
                        <div class="col-md-6">
                            <input type="text" ng-model="data2.buyer_state" class="form-control" name="buyer_state" value="{{ old('buyer_state')}}" required>
                            <span style="color:red" ng-show="(myForm2.buyer_state.$dirty && myForm2.buyer_state.$invalid) || submitted2 && myForm2.buyer_state.$invalid">
                                <span ng-show="submitted2 && myForm2.buyer_state.$error.required">Porfavor ingresa el departamento</span></span> 
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Pais</label>
                        <div class="col-md-6">
                            <input type="text" ng-model="data2.buyer_country" class="form-control" name="buyer_country" value="{{ old('buyer_country')}}" required>
                            <span style="color:red" ng-show="(myForm2.buyer_country.$dirty && myForm2.buyer_country.$invalid) || submitted2 && myForm2.buyer_country.$invalid">
                                <span ng-show="submitted2 && myForm2.buyer_country.$error.required">Porfavor ingresa el pais</span></span> 
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Telefono</label>
                        <div class="col-md-6">
                            <input type="tel" ng-model="data2.buyer_phone" class="form-control" name="buyer_phone" value="{{ old('buyer_phone')}}" required>
                            <span style="color:red" ng-show="(myForm2.buyer_phone.$dirty && myForm2.buyer_phone.$invalid) || submitted2 && myForm2.buyer_phone.$invalid">
                                <span ng-show="submitted2 && myForm2.buyer_phone.$error.required">Porfavor ingresa un telefono</span></span> 
                        </div>
                    </div>
                </div>


                <div class="form-group">
                    <div class="col-md-6 col-md-offset-4">
                        <button type="submit" class="btn btn-primary">Enviar</button>
                        <button ng-click="clean2()" class="btn btn-primary">Limpiar</button>
                        <a ng-click="fill()" href="javascript:;">Fill</a>

                    </div>
                </div>
            </form>
        </div>
        <div class="cash" ng-show="cash">
            <form  class="form-horizontal" role="form" name="myForm4" ng-submit="payCash(myForm4.$valid)" novalidate>
                <input type="hidden" name="_token" value="{{ csrf_token()}}">
                <input type="hidden" ng-model="data4.payment_id" name="payment_id" value="">
                <div >
                    <img width="50" class="w20" src="/assets/logos/payu.png" alt="PayU Latam" border="0" />
                </div>
                <div>
                    <p>Otros Medios</p>
                </div>

                <div class="cash-main-logos" >
                    <img width="250" ng-click="selectOption('BALOTO')" src="/assets/logos/baloto.jpg">
                    <img width="250" ng-click="selectOption('EFECTY')" src="/assets/logos/efecty.png">
                </div>
                <div class="cash-logos" ng-click="selectOption('OTHERS_CASH')">
                    <img width="250" src="/assets/logos/pagatodo.png">
                    <img width="250" src="/assets/logos/ac75.png">
                    <img width="250" src="/assets/logos/gana.png">
                </div>
                <div  class="cash-logos" ng-click="selectOption('OTHERS_CASH')">
                    <img width="250" src="/assets/logos/ganagana.png">
                    <img width="250" src="/assets/logos/suchance.png">
                    <img width="250" src="/assets/logos/acertemos.png">
                </div>
                <div class="cash-logos" ng-click="selectOption('OTHERS_CASH')">
                    <img width="250" src="/assets/logos/laperla.png">
                    <img width="250" src="/assets/logos/apuestas-unidas.jpg">
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label">Selecciona una opcion de efectivo</label>
                    <div class="col-md-6">
                        <select class="form-control nice-select" ng-model="data4.payment_method" name="payment_method" required>
                            <option value="BALOTO">BALOTO</option>
                            <option value="EFECTY">EFECTY</option>
                            <option value="OTHERS_CASH">OTROS</option>
                        </select>
                        <span style="color:red" ng-show="(myForm4.payment_method.$dirty && myForm4.payment_method.$invalid) || submitted4 && myForm4.payment_method.$invalid">
                            <span ng-show="submitted4 && myForm4.payment_method.$error.required">Porfavor selecciona un metodo de pago</span></span> 
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-4 control-label">Usar mis datos</label>
                    <div class="col-md-6">
                        <input type="checkbox" ng-model="use_user2" ng-click="useUserCash()">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-4 control-label">Identificacion</label>
                    <div class="col-md-6">
                        <input type="text" ng-model="data4.payer_id" class="form-control" name="payer_id" value="{{ old('payer_id')}}" required>
                        <span style="color:red" ng-show="(myForm4.payer_id.$dirty && myForm4.payer_id.$invalid) || submitted4 && myForm4.payer_id.$invalid">
                            <span ng-show="submitted4 && myForm4.payer_id.$error.required">Porfavor Ingresa tu identificacion</span></span> 
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label">Correo</label>
                    <div class="col-md-6">
                        <input type="email" ng-model="data4.payer_email" class="form-control" name="payer_email" value="{{ old('payer_email')}}" required>
                        <span style="color:red" ng-show="(myForm4.payer_email.$dirty && myForm4.payer_email.$invalid) || submitted4 && myForm4.payer_email.$invalid">
                            <span ng-show="submitted4 && myForm4.payer_email.$error.required">Porfavor Ingresa tu correo</span></span> 
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-6 col-md-offset-4">
                        <button type="submit" class="btn btn-primary">Enviar</button>
                        <button ng-click="clean()" class="btn btn-primary">Limpiar</button>
                        <a ng-click="fill3()" href="javascript:;">Fill</a>
                    </div>
                </div>
            </form>
        </div>
        <div class="debito" ng-show="debito">
            <form class="form-horizontal" role="form" name="myForm3" ng-submit="payDebitCard(myForm3.$valid)" novalidate>
                <input type="hidden" name="_token" value="{{ csrf_token()}}">
                <input type="hidden" ng-model="data3.payment_id" name="payment_id" value="">
                <div class="form-group" >
                    <label class="col-md-4 control-label">Banco</label>
                    <div class="col-md-6">
                        <select ng-model="data3.financial_institution_code" class="form-control nice-select" name="financial_institution_code" required>
                            <option ng-repeat="bank in banks" value="@{{bank.pseCode}}">@{{bank.description}}</option>
                        </select>
                        <span style="color:red" ng-show="(myForm3.financial_institution_code.$dirty && myForm3.financial_institution_code.$invalid) || submitted3 && myForm3.financial_institution_code.$invalid">
                            <span ng-show="submitted3 && myForm3.financial_institution_code.$error.required">Porfavor Selecciona tu banco</span></span> 
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-4 control-label">Usar mis datos</label>
                    <div class="col-md-6">
                        <input type="checkbox" ng-model="use_user3" ng-click="useUserDebit()">
                    </div>
                </div>
                <div class="form-group" >
                    <label class="col-md-4 control-label">Tipo de cliente</label>
                    <div class="col-md-6">
                        <select ng-model="data3.user_type"  class="form-control nice-select"  name="user_type" required>
                            <option value="N">Persona Natural</option>
                            <option value="J">Persona Juridica</option>
                        </select>
                        <span style="color:red" ng-show="(myForm3.user_type.$dirty && myForm3.user_type.$invalid) || submitted3 && myForm3.user_type.$invalid">
                            <span ng-show="submitted3 && myForm3.user_type.$error.required">Porfavor selecciona el tipo de cliente</span></span> 
                    </div>
                </div>
                <div class="form-group" >
                    <label class="col-md-4 control-label">Tipo de documento</label>
                    <div class="col-md-6">
                        <select ng-model="data3.doc_type"  class="form-control nice-select"  name="doc_type" required>
                            <option value="CC">Cédula de ciudadanía</option>
                            <option value="CE">Cédula de extranjería</option>
                            <option value="NIT">En caso de ser una empresa NIT</option>
                            <option value="TI">Tarjeta de Identidad</option>
                            <option value="PP">Pasaporte</option>
                            <option value="IDC">Identificador único de cliente</option>
                            <option value="CEL">Celular</option>
                            <option value="RC">Registro civil de nacimiento</option>
                            <option value="DE">Documento de identificación extranjero</option>
                        </select>
                        <span style="color:red" ng-show="(myForm3.doc_type.$dirty && myForm3.doc_type.$invalid) || submitted3 && myForm3.doc_type.$invalid">
                            <span ng-show="submitted3 && myForm3.doc_type.$error.required">Porfavor selecciona el tipo de documento</span></span> 
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label">Numero Documento</label>
                    <div class="col-md-6">
                        <input type="text" ng-model="data3.payer_id" class="form-control" name="payer_id" value="{{ old('payer_id')}}" required>
                        <span style="color:red" ng-show="(myForm3.payer_id.$dirty && myForm3.payer_id.$invalid) || submitted3 && myForm3.payer_id.$invalid">
                            <span ng-show="submitted3 && myForm3.payer_id.$error.required">Porfavor Ingresa el número de documento</span></span> 
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label">Correo</label>
                    <div class="col-md-6">
                        <input type="email" ng-model="data3.payer_email" class="form-control" name="payer_email" value="{{ old('payer_email')}}" required>
                        <span style="color:red" ng-show="(myForm3.payer_email.$dirty && myForm3.payer_email.$invalid) || submitted3 && myForm3.payer_email.$invalid">
                            <span ng-show="submitted3 && myForm3.payer_email.$error.required">Porfavor Ingresa el correo del propietario de la tarjeta</span></span> 
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label">Telefono</label>
                    <div class="col-md-6">
                        <input type="text" ng-model="data3.payer_phone" class="form-control" name="payer_phone" value="{{ old('payer_phone')}}" required>
                        <span style="color:red" ng-show="(myForm3.payer_phone.$dirty && myForm3.payer_phone.$invalid) || submitted3 && myForm3.payer_phone.$invalid">
                            <span ng-show="submitted3 && myForm3.payer_phone.$error.required">Porfavor Ingresa el telefono del pagador</span></span> 
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label">Nombre</label>
                    <div class="col-md-6">
                        <input type="text" ng-model="data3.payer_name" class="form-control" name="payer_name" value="{{ old('payer_name')}}" required>
                        <span style="color:red" ng-show="(myForm3.payer_name.$dirty && myForm3.payer_name.$invalid) || submitted3 && myForm3.payer_name.$invalid">
                            <span ng-show="submitted3 && myForm3.payer_name.$error.required">Porfavor Ingresa el nombre del pagador</span></span> 
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-6 col-md-offset-4">
                        <button type="submit" class="btn btn-primary">Enviar</button>
                        <button ng-click="clean()" class="btn btn-primary">Limpiar</button>
                        <a ng-click="fill2()" href="javascript:;">Fill</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div ng-show="showResult" style="color:black;font-weight: bold">
        <h4>@{{resultHeader}}</h4>
        <p>@{{resultBody}}</p><br/>
        <h4>Transaccion</h4>
        <p>Forma de pago: @{{transaction.payment_method}}</p>
        <p>Referencia pago: @{{transaction.reference_sale}}</p>
        <p>Id transaccion: @{{transaction.transaction_id}}</p>
        <p>Estado: @{{transaction.transaction_state}}</p>
        <p>Descripcion: @{{transaction.description}}</p>
        <a href="/">Terminar</a>
    </div>
</div>


