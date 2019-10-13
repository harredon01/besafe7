<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use App\Services\FoodImport;
use App\Models\Article;
use App\Models\CoveragePolygon;

class FoodImportController extends Controller {
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
    public function __construct(Guard $auth, FoodImport $food) {
        $this->auth = $auth;
        $this->food = $food;
        $this->middleware('auth')->except('getZonesPublic');
        $this->middleware('admin')->except('getZonesPublic');
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getMessages() {
        return view('food.messages');
    }
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getDeliveries() {
        return view('food.deliveriesDashboard');
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function postMessages(Request $request) {
        $user = $this->auth->user();
        
        if ($request->hasFile('uploadfile')) {
            if ($request->file('uploadfile')->isValid()) {
                $path = $request->uploadfile->path();
                $this->food->importTranslations($path);
            }
        }

        return view('food.messages')->with('user', $user);
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getMenu() {
        $user = $this->auth->user();
        return view('food.menu')->with('user', $user);
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function postMenu(Request $request) {
        $user = $this->auth->user();
        if ($request->file('uploadfile')->isValid()) {
            //$path = $request->uploadfile->path();
            $this->food->importDishes(request()->file('uploadfile'));
        }
        return view('food.menu')->with('user', $user);
    }
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getContent() {
        $user = $this->auth->user();
        return view('food.content')->with('user', $user);
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function postContent(Request $request) {
        $user = $this->auth->user();
        if ($request->file('uploadfile')->isValid()) {
            $path = $request->uploadfile->path();
            $this->food->importContent($path);
        }
        return view('food.content')->with('user', $user);
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getZones() {
        return view('food.zones');
    }
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getZonesPublic() {
        return view('content.zonespublic');
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function postZones(Request $request) {
        $user = $this->auth->user();
        if ($request->file('uploadfile')->isValid()) {
            $path = $request->uploadfile->path();
            $this->food->importPolygons($path);
        }
        return view('food.zones')->with('user', $user);
    }

}
