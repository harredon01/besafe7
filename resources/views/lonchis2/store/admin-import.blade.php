@extends(config("app.views").'.layouts.app')

@section('content')
<section class="main_content_area" ng-controller="UserProfileCtrl">
    <div class="container">   
        <div class="account_dashboard">
            <div class="row">
                @include(config("app.views").'.user.account_menu')
                <div class="col-sm-12 col-md-9 col-lg-9">
                    <!-- Tab panes -->
                    <div class="tab-content dashboard_content">
                        <div class="tab-pane fade  show active" id="dashboard">
                            <h3>Account details </h3>
                            <div class="login">
                                <div class="myaccount-content"  ng-controller="ExportsCtrl">
                                    <h3>Admin import</h3>
                                    <div class="panel panel-default">
                                        <div class="panel-heading">Cargas Masivas</div>
                                        <div class="panel-body">
                                            <form class="form-horizontal" enctype="multipart/form-data" role="form" method="POST" action="{{ url('/admin/store/global-admin')}}">
                                                {{ csrf_field()}}

                                                <div class="form-group{{ $errors->has('uploadfile') ? ' has-error' : ''}}">
                                                    <label for="uploadfile" class="col-md-4 control-label">Subir excel</label>

                                                    <div class="col-md-6">
                                                        <input id="uploadfile" type="file" class="form-control" name="uploadfile" >

                                                        @if ($errors->has('uploadfile'))
                                                        <span class="help-block">
                                                            <strong>{{ $errors->first('uploadfile')}}</strong>
                                                        </span>
                                                        @endif 
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <div class="col-md-6 col-md-offset-4">
                                                        <button type="submit" class="btn btn-primary">
                                                            Cargar
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>  
    </div>        	
</section>

@endsection
