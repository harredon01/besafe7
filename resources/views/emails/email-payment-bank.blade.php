@component('mail::message')

# Gracias por tu compra

Puedes consignar el valor del pago en la cuenta de Davivienda # .
Una vez hecha la consignacion envia los datos del pago que aparecen aca, una imagen de la consignacion, y tus datos incluyendo tu correo a pagos@lonchis.com.co

# Detalles del pago

Id: {{ $payment->id }}  
Referencia: {{ $payment->reference_code }}  
Total: {{ $payment->reference_code }}  

Gracias,<br>
{{ config('app.name') }}
@endcomponent