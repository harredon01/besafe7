@extends('app')

@section('content')
<div class="container-fluid">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<div class="panel panel-default">
				<div class="panel-heading">Hello {{$user->firstName}}, here you can edit your profile information
                                
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
							<label class="col-md-4 control-label">First Name</label>
							<div class="col-md-6">
								<input type="text" class="form-control" name="firstName" value="{{$user->firstName}}">
							</div>
						</div>
                                                
                                                <div class="form-group">
							<label class="col-md-4 control-label">Last Name</label>
							<div class="col-md-6">
								<input type="text" class="form-control" name="lastName" value="{{ $user->lastName }}">
							</div>
						</div>
                                                <div class="form-group">
							<label class="col-md-4 control-label">Gender</label>
							<div class="col-md-6">
								<input type="text" class="form-control" name="gender" value="{{ $user->gender }}">
							</div>
						</div>
                                                <div class="form-group">
							<label class="col-md-4 control-label">Doc Type</label>
							<div class="col-md-6">
								<input type="text" class="form-control" name="docType" value="{{ $user->docType }}">
							</div>
						</div>

						<div class="form-group">
							<label class="col-md-4 control-label">Doc Number</label>
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
