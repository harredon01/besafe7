@component('mail::message')

# Resumen de los costos de envio para ambos escenarios

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
@component('mail::button', ['url' =>config('app.url'). "/food/get_scenario_structure/preorganize/".$preData['scenario_hash']])
Generar Correos
@endcomponent
@component('mail::button', ['url' => config('app.url'). "/food/build_complete_scenario/preorganize/".$preData['scenario_hash']])
Construir
@endcomponent
@endcomponent

@component('mail::panel')
Resumen Escenario Pre:  
Estado: {{$preData['status']}}  
Total Costo: ${{ number_format($simpleData['ShippingCostEstimate'], 2, ',', '.') }}  
Ingreso Comision: ${{ number_format($simpleData['hoov_income'], 2, ',', '.') }}  
Ingreso Envio: ${{ number_format($simpleData['shipping_income'], 2, ',', '.') }}  
Total Ingreso: ${{ number_format($simpleData['total_income'], 2, ',', '.') }}  
Total Profit: ${{ number_format($simpleData['day_profit'], 2, ',', '.') }}  
Numero de rutas: {{$preData['routes']}}  
Numero de numero de almuerzos: {{$simpleData['lunches']}}  
Promedio almuerzos por ruta: {{$simpleData['lunch_route']}}  
@component('mail::button', ['url' =>config('app.url'). "/food/get_scenario_structure/simple/".$preData['scenario_hash']])
Generar Correos
@endcomponent
@component('mail::button', ['url' => config('app.url'). "/food/build_complete_scenario/simple/".$preData['scenario_hash']])
Construir
@endcomponent
@endcomponent


Gracias,<br>
{{ config('app.name') }}
@endcomponent
