<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Merchant;
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        if(config("app.views")=="petworld"){
            $merchants = Merchant::whereIn("id",[7,8,9,12,13,16,20])->orderBy("id","desc")->get();
            return view(config("app.views").'.home', ['vets' => $merchants]);
        } else {
            return view(config("app.views").'.home');
        }
        
    }
    
}
