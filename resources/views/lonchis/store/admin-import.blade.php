@extends(config("app.views").'.layouts.app')

@section('content')
<div class="page-section sp-inner-page" ng-controller="UserProfileCtrl">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="row">
                    <!-- My Account Tab Menu Start -->
                    @include(config("app.views").'.user.account_menu')
                    <!-- My Account Tab Menu End -->

                    <!-- My Account Tab Content Start -->
                    <div class="col-lg-9 col-12 mt--30 mt-lg-0">
                        <div class="tab-content" id="myaccountContent">
                            <div class="tab-pane" style="display:block" id="account-info" role="tabpanel">
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
                            <!-- Single Tab Content End -->
                        </div>
                    </div>
                    <!-- My Account Tab Content End -->
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
