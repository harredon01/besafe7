@extends(config("app.views").'layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Bienvenido de vuelta
                </div>
                <div class="panel-body">
                    @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        <strong>Whoops!</strong> There were some problems with your input.<br><br>
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error}}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    <div>
                        <h2>Transaction Summary</h2>
                        <table>
                            <tr>
                                <td>Transaction status</td>
                                <td>{{ $estadoTx }}</td>
                            </tr>
                            <tr>
                            <tr>
                                <td>Transaction ID</td>
                                <td>{{ $transactionId }}</td>
                            </tr>
                            <tr>
                                <td>Reference sale</td>
                                <td>{{ $reference_pol }}</td>
                            </tr>
                            <tr>
                                <td>Reference transaction</td>
                                <td>{{ $referenceCode }}</td>
                            </tr>
                            <tr>
                                @if($pseBank) 
                            <tr>
                                <td>cus </td>
                                <td>{{ $cus }} </td>
                            </tr>
                            <tr>
                                <td>Bank </td>
                                <td>{{ $pseBank }} </td>
                            </tr>
                            @endif
                            <tr>
                                <td>total amount</td>
                                <td>${{ $TX_VALUE }}</td>
                            </tr>
                            <tr>
                                <td>Currency</td>
                                <td>{{ $currency }}</td>
                            </tr>
                            <tr>
                                <td>Description</td>
                                <td>{{ $extra1 }}</td>
                            </tr>
                            <tr>
                                <td>Entity:</td>
                                <td>{{ $lapPaymentMethod }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
