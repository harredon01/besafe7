@component('mail::message')

# Hola {{$user->firstName}}!

Gracias por comprar en lonchis. Para continuar con el proceso para tu pago {{$payment->id}} debes pagar el comprobante en el metodo que escogiste. 


@component('mail::button', ['url' => $url ])
Version Web
@endcomponent
@component('mail::button', ['url' => $pdf ])
Recibo en pdf
@endcomponent


Gracias,<br>
{{ config('app.name') }}
@endcomponent