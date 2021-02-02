@extends(config("app.views").'.layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2 offset-2">
            <div class="panel panel-default">
                <div class="panel-body row">
                    <div class="col-md-4 col-md-offset-1 offset-1">
                        <br/>
                        <h3 class="text">Bienvenido a nuestra comunidad</h3>
                        <br/>
                        <button class="btn btn-dark" onclick="window.location.href = '/register';" >
                            Registrate 
                        </button>
                        <br/>
                        <br/>
                        <p>O ingresa seguro y olvidate de contraseñas con: <br/></p>
                        <br/>
                        <button class="btn btn-primary" onclick="window.location.href = '/login/facebook';" >
                            Facebook
                        </button>
                        <button class="btn btn-danger" onclick="window.location.href = '/login/google';" >
                            Google
                        </button>
                        <br/>
                        <br/>
                    </div>
                    <div class="col-md-4 col-md-offset-1 offset-1">
                        
                        <br/>
                        <form class="form-horizontal" role="form" method="POST" action="{{ url('/login') }}">
                            {{ csrf_field() }}
                            
                            <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                <p>Ya tienes cuenta???</p>
                                <label for="email" class=" control-label">E-Mail</label>

                                <div  >
                                    <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus>

                                    @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                <label for="password" class="control-label">Contraseña</label>

                                <div>
                                    <input id="password" type="password" class="form-control" name="password" required>

                                    @if ($errors->has('password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-6">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : ''}}> Recordarme
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-8 col-md-offset-6">
                                    <button type="submit" class="btn btn-primary">
                                        Login
                                    </button>
<br/>
                                    <a class="btn btn-link" href="{{ url('/password/reset') }}">
                                        Olvidaste tu clave?
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="col-md-8 col-md-offset-4">

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
