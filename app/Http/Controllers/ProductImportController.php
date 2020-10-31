<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use App\Services\ProductImport;

class ProductImportController extends Controller {
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

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Guard $auth, ProductImport $product) {
        $this->auth = $auth;
        $this->product = $product;
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getProducts() {
        return view(config("app.views").'.admin.store.products');
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function postProducts(Request $request) {
        $user = $this->auth->user();
        
        if ($request->hasFile('uploadfile')) {
            if ($request->file('uploadfile')->isValid()) {
                $path = $request->uploadfile->path();
                $this->product->importProducts($path);
            }
        }

        return view(config("app.views").'.admin.store.products')->with('user', $user);
    }
    
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getVariants() {
        return view(config("app.views").'.admin.store.variants');
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function postVariants(Request $request) {
        $user = $this->auth->user();
        
        if ($request->hasFile('uploadfile')) {
            if ($request->file('uploadfile')->isValid()) {
                $path = $request->uploadfile->path();
                $this->product->importVariants($path);
            }
        }

        return view(config("app.views").'.admin.store.variants')->with('user', $user);
    }
    
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getMerchants() {
        return view(config("app.views").'.admin.store.merchants');
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function postMerchants(Request $request) {
        $user = $this->auth->user();
        
        if ($request->hasFile('uploadfile')) {
            if ($request->file('uploadfile')->isValid()) {
                $path = $request->uploadfile->path();
                $this->product->importMerchants($path);
            }
        }

        return view(config("app.views").'.admin.store.merchants')->with('user', $user);
    }
    
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getCategories() {
        return view(config("app.views").'.admin.store.categories');
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function postCategories(Request $request) {
        $user = $this->auth->user();
        
        if ($request->hasFile('uploadfile')) {
            if ($request->file('uploadfile')->isValid()) {
                $path = $request->uploadfile->path();
                $this->product->importCategories($path);
            }
        }

        return view(config("app.views").'.admin.store.categories')->with('user', $user);
    }
}
