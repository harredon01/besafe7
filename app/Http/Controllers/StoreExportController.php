<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use App\Services\StoreExport;
use App\Services\MerchantImport;
use App\Models\Article;
use App\Models\CoveragePolygon;

class StoreExportController extends Controller {
    /*
      |--------------------------------------------------------------------------
      | Home Controller
      |--------------------------------------------------------------------------
      |
      | This controller renders your application's "dashboard" for users that
      | are authenticated. Of course, you are free to change or remove the
      | controller as you wish. It is just here to get your app started!
      |
     */
    
    protected $store;
    
    protected $merchantImport;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Guard $auth, StoreExport $store, MerchantImport $merchantImport) {
        $this->auth = $auth;
        $this->store = $store;
        $this->merchantImport = $merchantImport;
        $this->middleware('auth')->except('getZonesPublic');
        $this->middleware('admin')->only('postAdminImport');
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getImport() {
        return view(config("app.views").'.store.import');
    }
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getAdminImport() {
        return view(config("app.views").'.store.admin-import');
    }
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getExport() {
        return view(config("app.views").'.store.import');
    }
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function postImport(Request $request) {
        $user = $this->auth->user();
        if ($request->file('uploadfile')->isValid()) {
//            $path = $request->file('uploadfile')->store('public/imports');
//            dispatch(new StoreImport($user,$path,true));
            $this->store->importGlobalExcel($user,request()->file('uploadfile'),false);
        }
        return view(config("app.views").'.store.import')->with('user', $user);
    }
    
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function postAdminImport(Request $request) {
        $user = $this->auth->user();
        if ($request->file('uploadfile')->isValid()) {
//            $path = $request->file('uploadfile')->store('public/imports');
//            dispatch(new StoreImport($user,$path,true));
            $this->store->importGlobalExcel($user,request()->file('uploadfile'),false);
        }
        return view(config("app.views").'.store.import')->with('user', $user);
    }

}
