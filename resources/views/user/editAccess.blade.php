@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Hola {{$user->firstName}}, aca puedes controlar el acceso a tu informacion por tu seguridad. 

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
                    <div ng-controller="TokensCtrl">
                        <div class="replace-personal">
                            @include('user.tokenList')
                        </div>

                    </div>
                    <div class="clear"></div>
                    <div ng-controller="PersonalTokensCtrl">
                        <div >

                            @include('user.createPersonalTokenForm')

                        </div>
                    </div>
                    <div class="clear"></div>
                    <div ng-controller="OauthClientsCtrl">
                        <div class="replace-client">
                            @include('user.oauthClientList')
                        </div>
                        <div class="clear"></div>

                        <div >

                            @include('user.editOauthClientForm')

                        </div>
                    </div>
                    <div class="clear"></div>





                </div>
            </div>
        </div>
    </div>
</div>
@endsection
