@component('mail::message')

# Hola {{$user->firstName}}!

Gracias por comprar en lonchis. Para continuar con el proceso para tu pago {{$payment->id}} porfavor haz click en el siguiente link. 

@component('mail::button', ['url' => $url ])
Pagar
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent