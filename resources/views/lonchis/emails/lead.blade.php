@component('mail::message')

{{$subject}} 
@foreach($data as $key =>$value)
@if(is_string($value))
{{$key}}:{{$value}}
@else
{{$key}}: {{json_encode($value)}}
@endif

@endforeach
Gracias,<br>
{{ config('app.name') }}
@endcomponent
