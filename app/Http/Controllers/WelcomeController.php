<?php

namespace App\Http\Controllers;
use Mail;

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
        return view(config("app.views").'.welcome');
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

}
