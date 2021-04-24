<?php

namespace App\Http\Controllers;
use Mail;
use Illuminate\Http\Request;
use App\Models\Merchant;
class WelcomeController extends Controller {
    /*
      |--------------------------------------------------------------------------
      | Welcome Controller
      |--------------------------------------------------------------------------
      |
      | This controller renders the "marketing page" for the application and
      | is configured to only allow guests. Like most of the other sample
      | controllers, you are free to modify or remove it as you desire.
      |
     */
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('guest');
    }

    /**
     * Show the application welcome screen to the user.
     *
     * @return Response
     */
    public function index() {
        if(config("app.views")=="petworld"){
            $merchants = Merchant::whereIn("id",[7,8,9,12,13,16,20])->orderBy("id","desc")->get();
            return view(config("app.views").'.welcome', ['vets' => $merchants]);
        } else {
            return view(config("app.views").'.welcome');
        }
        
    }

    /**
     * Test Email
     *
     * @return Response
     */
    public function email() {
        Mail::send('emails.welcome', ['key' => 'value'], function($message) {
            $message->from('noreply@hoovert.com', 'Hoove');
            $message->to('harredon01@gmail.com', 'Hoovert Arredondo')->subject('Exitoo!');
        });
    }
    
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function getBlogList(Request $request)
    {
        return view(config("app.views").'.blog');
    }
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function getBlogDetail(Request $request, $category)
    {
        return view(config("app.views").'.blog-detail');
    }
    
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function getContact(Request $request)
    {
        return view(config("app.views").'.leads.leads');
    }

}
