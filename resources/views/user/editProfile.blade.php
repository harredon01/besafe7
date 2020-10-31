@extends(config("app.views").'layouts.app')

@section('content')
<div class="container-fluid" ng-controller="UserProfileCtrl">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<div class="panel panel-default">
				<div class="panel-heading">Hola {{$user->firstName}}, aca puedes editar tus datos
                                
                                </div>
				<div class="panel-body">
					@if (count($errors) > 0)
						<div class="alert alert-danger">
							<strong>Whoops!</strong> There were some problems with your input.<br><br>
							<ul>
								@foreach ($errors->all() as $error)
									<li>{{ $error }}</li>
								@endforeach
							</ul>
						</div> 
					@endif

					<form class="form-horizontal" role="form" method="POST" action="{{ url('/user/editProfile') }}">
						<input type="hidden" name="_token" value="{{ csrf_token() }}">

						<div class="form-group">
							<label class="col-md-4 control-label">Nombres</label>
							<div class="col-md-6">
								<input type="text" class="form-control" name="firstName" value="{{$user->firstName}}">
							</div>
						</div>
                                                
                                                <div class="form-group">
							<label class="col-md-4 control-label">Apellidos</label>
							<div class="col-md-6">
								<input type="text" class="form-control" name="lastName" value="{{ $user->lastName }}">
							</div>
						</div>
                                                <div class="form-group">
							<label class="col-md-4 control-label">Email</label>
							<div class="col-md-6">
								<input type="text" class="form-control" name="email" value="{{ $user->email }}">
							</div>
						</div>
                                                <div class="form-group">
							<label class="col-md-4 control-label">Celular</label>
							<div class="col-md-6">
								<input type="text" class="form-control" name="cellphone" value="{{ $user->cellphone }}">
							</div>
						</div>
                                                <div class="form-group">
							<label class="col-md-4 control-label">Genero</label>
							<div class="col-md-6">
								<input type="text" class="form-control" name="gender" value="{{ $user->gender }}">
							</div>
						</div>
                                                <div class="form-group">
							<label class="col-md-4 control-label">Tipo de identificacion</label>
							<div class="col-md-6">
								<input type="text" class="form-control" name="docType" value="{{ $user->docType }}">
							</div>
						</div>

						<div class="form-group">
							<label class="col-md-4 control-label"># identificacion</label>
							<div class="col-md-6">
								<input type="text" class="form-control" name="docNum" value="{{ $user->docNum }}">
							</div>
						</div>
                                                

						<div class="form-group">
							<div class="col-md-6 col-md-offset-4">
								<button type="submit" class="btn btn-primary">Save</button>

								
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
