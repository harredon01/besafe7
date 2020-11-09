<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Auth\Guard;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EditMapObject;
use App\Services\MerchantImport;
use App\Services\CleanSearch;
use App\Models\Report;
use App\Models\Category;
use App\Querybuilders\ReportQueryBuilder;

/* use Excel;
  use App\Models\Category;
  use App\Models\Product;
  use App\Models\Merchant;

  use App\Models\PaymentMethod;
  use DB; */

class ReportController extends Controller {

    /**
     * The Guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $auth;

    /**
     * The edit profile implementation.
     *
     */
    protected $editMapObject;

    /**
     * The edit profile implementation.
     *
     */
    protected $merchantImport;

    /**
     * The edit profile implementation.
     *
     */
    protected $cleanSearch;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Guard $auth, EditMapObject $editMapObject, MerchantImport $merchantImport, CleanSearch $cleanSearch) {
        $this->editMapObject = $editMapObject;
        $this->merchantImport = $merchantImport;
        $this->auth = $auth;
        $this->cleanSearch = $cleanSearch;
        $this->middleware('auth')->except(['index', 'getNearbyReports','getReportDetail']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request, $category) {
        $request2 = $this->cleanSearch->handleReportExternal($request);
        if ($request2) {
            $category = Category::where('url', $category)->first();
            $request2 = Request::create($request2->getRequestUri() . "&category_id=" . $category->id, 'GET');
            $queryBuilder = new ReportQueryBuilder(new Report, $request2);
//            DB::enableQueryLog();
            $result = $queryBuilder->build()->paginate();
//            dd(DB::getQueryLog());
            $merchants = [
                'data' => $result->items(),
                "total" => $result->total(),
                "per_page" => $result->perPage(),
                "page" => $result->currentPage(),
                "last_page" => $result->lastPage(),
                "category" => $category
            ];
//            dd($merchants['data'][0]->toArray());
            return view(config("app.views") . '.reports.listing', ["reports" => $merchants]);
        }
        $merchants = [
            'data' => [],
            "total" => 0,
            "per_page" => 0,
            "page" => 0,
            "last_page" => 0,
        ];
        return view(config("app.views") . '.reports.listing', ["reports" => $merchants]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getNearbyReports(Request $request, $category) {
        $data = $request->all();
        $category = Category::where('url', $category)->first()->toArray();
        $validator = $this->editMapObject->validatorLat($data);
        if ($validator->fails()) {
            $this->throwValidationException(
                    $request, $validator
            );
        }
        $data['category'] = $category['id'];
        $data['type'] = "Report";
        $results = $this->editMapObject->getNearbyObjects($data);
        
        $merchants = $results['data'];
//        dd($merchants);
//        dd(DB::getQueryLog());
        $merchants = $this->editMapObject->buildIncludes($merchants, $data);
        $merchants = array_map(function ($value) {
            $value->attributes = json_decode($value->attributes);
            return (array) $value;
        }, $merchants);
        $results['data'] = $merchants;
        $results['category'] = $category;

        return view(config("app.views") . '.reports.listing', ["reports" => $results]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getReportDetail($url) {
        $user = $this->auth->user();
        $merchant = Report::where("slug",$url)->with(["files",'ratings'])->first();
        return view(config("app.views") . '.reports.detail')->with('report', $merchant);
    }

}
