<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\CleanSearch;
use App\Services\EditMerchant;
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
    

    
    /**
     * The edit profile implementation.
     *
     */
    protected $cleanSearch;
    
    /**
     * The edit profile implementation.
     *
     */
    protected $editMerchant;
    
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(CleanSearch $cleanSearch, EditMerchant $editMerchant) {
        $this->cleanSearch = $cleanSearch;
        $this->editMerchant = $editMerchant;
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
        $report = $this->editMerchant->getReportByHash($code);
        return view('report')->with('report', $report['report'])->with('images', $report['files']);
    }
    
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getLocations(Request $request) {
        $request2 = $this->cleanSearch->handleLocation($request);
        if ($request2) {
            if($request2->only("hash_id")){
                $queryBuilder = new LocationQueryBuilder(new User, $request2);
            } else {
                $queryBuilder = new LocationQueryBuilder(new Userable, $request2);
            }
            $result = $queryBuilder->build()->paginate();
            return response()->json([
                        'data' => $result->items(),
                        "total" => $result->total(),
                        "per_page" => $result->perPage(),
                        "page" => $result->currentPage(),
                        "last_page" => $result->lastPage(),
            ]);
        }
        return response()->json([
                    'status' => "error",
                    'message' => "no user id parameter allowed"
                        ], 401);
    }
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getReports(Request $request) {
        $request2 = $this->cleanSearch->handleReport($request);
        if ($request2) {
            $queryBuilder = new ReportQueryBuilder(new Report, $request2);
            $result = $queryBuilder->build()->paginate();
            return response()->json([
                        'data' => $result->items(),
                        "total" => $result->total(),
                        "per_page" => $result->perPage(),
                        "page" => $result->currentPage(),
                        "last_page" => $result->lastPage(),
            ]);
        }
        return response()->json([
                    'status' => "error",
                    'message' => "illegal parameter"
                        ], 401);
    }

}
