<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\CleanSearch;
use App\Services\EditMapObject;
use App\Querybuilders\ReportQueryBuilder;
use App\Querybuilders\LocationQueryBuilder;
use App\Models\User;
use App\Models\Report;
use App\Models\Userable;
use App\Models\Location;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class MapExternalController extends Controller {
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
    const OBJECT_REPORT = 'Report';
    const OBJECT_MERCHANT = 'Merchant';

    
    /**
     * The edit profile implementation.
     *
     */
    protected $cleanSearch;
    
    /**
     * The edit profile implementation.
     *
     */
    protected $editMapObject;
    
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(CleanSearch $cleanSearch, EditMapObject $editMapObject) {
        $this->cleanSearch = $cleanSearch;
        $this->editMapObject = $editMapObject;
        $this->middleware('guest');
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function index($code = null) {
        $user = User::where('hash',$code)->first();
        return view('map')->with('following', $user);
    }
    
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function report($code = null) {
        $report = $this->editMapObject->getObjectByHash($code,self::OBJECT_REPORT);
        return view('report')->with('report', $report['report'])->with('images', $report['files']);
    }
    
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function merchant($code = null) {
        $object = $this->editMapObject->getObjectByHash($code,self::OBJECT_MERCHANT);
        return view('merchant')->with('merchant', $object['merchant'])->with('images', $object['files']);
    }

}
