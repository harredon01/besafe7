@extends(config("app.views").'.layouts.app')


@section('content')

<!--faq area start-->
    <div class="faq_content_area">
        <div class="container">   
            <div class="row">
                <div class="col-12">
                    <div class="faq_content_wrapper">
                        <h4>Conoce nuestras preguntas frecuentes para resolver tus dudas</h4>
                        

                    </div>
                </div>
            </div> 
        </div>    
    </div>
     <!--Accordion area-->
  

    <div class="accordion_area">
        <div class="container">
            <div class="row">
            <div class="col-12"> 
                <div id="accordionExample" class="card__accordion accordion">
                  <div class="accordion-item">
                    <h2 class="accordion-header" id="headingOne">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            ¿Puedo probar el servicio antes de adquirir un plan?
                        </button>
                      </h2>
                    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                      <div class="card-body">
                           <p>Claro! Descarga la app, regístrate y recibe a tu e-mail un correo de bienvenida con información importante sobre nuestro servicio y un  con un cupón de descuento para que pruebes tu primer almuerzo por  $ 9.700 (Entrada + plato fuerte + domicilio). En el correo encuentras las instrucciones paso a paso para redimir tu cupón.</p>
                      </div>
                    </div>
                  </div>

                  <div class="accordion-item">
                    <h2 class="accordion-header" id="headingTwo">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
                            ¿Qué planes manejan?
                        </button>
                      </h2>
                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                      <div class="card-body">
                           <ul class="disc">
                                                                                        <li>Puedes seleccionar entre planes en <b>envases retornables o envases desechables</b>, la cantidad de almuerzos lo puedes personalizar entre 1 a 89. *los planes en retornables son más económicos que los desechables</li>
                                                                                        <li>Por cada 11 almuerzos te  regalamos saldo por <b>$11.000</b>, por lo que siempre vas a conseguir el mejor precio con planes múltiplos de 11 (11,22,33,44…) sin importar que tipo de envase selecciones.</li>
                                                                                        <li>En grupo es más barato,Compra un plan con amigos y divide la cuenta en partes iguales. Entre más amigos, más ahorro.<br>Para tener en cuenta:
                                                                                            <ul>
                                                                                                <li>Todos los miembros del grupo  reciben en la misma dirección.</li>
                                                                                                <li>Todos tienen que tener la app descargada y tener una cuenta Lonchis.</li>
                                                                                                <li>Para que el plan se active, todos los miembros del grupo tienen que realizar su parte del pago.</li>
                                                                                                <li>El consumo de los almuerzos es individual,  no es necesario que todos los del grupo solicitan el mismo dia.</li>
                                                                                            </ul>
                                                                                        </li>
                                                                                    </ul>
                      </div>
                    </div>
                  </div>
                  <div class="accordion-item">
                    <h2 class="accordion-header" id="headingThree">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="true" aria-controls="collapseThree">
                            ¿Qué métodos de pago tienen disponible?
                        </button>
                      </h2>
                    <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
                      <div class="card-body">
                           <ul class="disc">
                                                                                        <li>Por Medio de la aplicación puedes pagar con PSE, Tarjeta de crédito y métodos de efectivo como Efecty y Baloto</li>
                                                                                        <li>También puedes consignar o hacer una transferencia a nuestra cuenta davivienda y enviarnos el soporte. Una vez lo hayamos verificado te activaremos el plan</li>
                                                                                    </ul>
                      </div>
                    </div>
                  </div>
                  <div class="accordion-item">
                    <h2 class="accordion-header" id="headingFour">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="true" aria-controls="collapseFour">
                            ¿Puedo Pedir para hoy mismo?
                        </button>
                      </h2>
                    <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#accordionExample">
                      <div class="card-body">
                           <ul class="disc">
                                                                                        <li>Nuestro horario de programación es hasta las 10pm del día anterior</li>
                                                                                        <li>Si tienes un plan activo y nos contactas antes de las 9am talves podemos alcanzar a incluirte en las entregas del día</li>
                                                                                        <li>De lo contrario no puedes pedir el mismo día puesto que solo funcionamos por pedido</li>
                                                                                    </ul>
                      </div>
                    </div>
                  </div>

                </div>
            </div>
        </div>
        </div>
    </div>
    <!--Accordion area end-->

    
    <!--faq area end-->
@endsection