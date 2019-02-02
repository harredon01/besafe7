@component('mail::message')

# Resumen de los costos de envio para ambos escenarios

@component('mail::panel')
Resultado mas optimo: {{$winner}}
@endcomponent

@component('mail::panel')
Resultado mas optimo: {{$winner}}
@endcomponent

@component('mail::panel')
Resumen Escenario Pre: 
Estado: {{$preData['status']}}
Total Costo: ${{ number_format($preData['ShippingCostEstimate'], 2, ',', '.') }}
Ingreso Comision: ${{ number_format($preData['hoov_income'], 2, ',', '.') }}
Ingreso Envio: ${{ number_format($preData['shipping_income'], 2, ',', '.') }}
Total Ingreso: ${{ number_format($preData['total_income'], 2, ',', '.') }}
Total Profit: ${{ number_format($preData['day_profit'], 2, ',', '.') }}
Numero de rutas: {{$preData['routes']}}
Numero de numero de almuerzos: {{$preData['lunches']}}
Promedio almuerzos por ruta: {{$preData['lunch_route']}}
@endcomponent


Gracias,<br>
{{ config('app.name') }}
@endcomponent
