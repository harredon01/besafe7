<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Auth\Guard;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EditMapObject;
use App\Services\MerchantImport;
use App\Services\CleanSearch;
use App\Models\Merchant;
use App\Models\Category;
use App\Querybuilders\MerchantQueryBuilder;

/* use Excel;
  use App\Models\Category;
  use App\Models\Product;
  use App\Models\Merchant;

  use App\Models\PaymentMethod;
  use DB; */

class MerchantController extends Controller {

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
        $this->middleware('auth')->except(['index', 'getNearbyMerchants', 'getCoverageMerchants', 'getMerchantDetail', 'textSearch']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request, $category) {
        $request2 = $this->cleanSearch->handleMerchantExternal($request);
        if ($request2) {
            $category = Category::where('url', $category)->first();
            if ($category) {
                $request2 = Request::create($request2->getRequestUri() . "&category_id=" . $category->id, 'GET');
                $queryBuilder = new MerchantQueryBuilder(new Merchant, $request2);
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
                return view(config("app.views") . '.merchants.listing', ["merchants" => $merchants]);
            }
            abort(404);
        }
        $merchants = [
            'data' => [],
            "total" => 0,
            "per_page" => 0,
            "page" => 0,
            "last_page" => 0,
        ];
        return view(config("app.views") . '.merchants.listing', ["merchants" => $merchants]);
    }

    public function importMerchant() {
        $user = $this->auth->user();
        $filename = "merchant test.xlsx";
        return response()->json($this->merchantImport->importNewMerchant($user, $filename));
    }

    public function exportMerchant() {
        $user = $this->auth->user();
        $filename = "merchant test.xlsx";
        $merchantid = "5";
        return response()->json($this->merchantImport->exportMerchant($user, $filename, $merchantid));
    }

    public function exportMerchantOrders() {
        $user = $this->auth->user();
        $filename = "merchant test.xlsx";
        $merchantid = "5";
        return response()->json($this->merchantImport->exportMerchantOrders($user, $filename, $merchantid));
    }

    public function importUpdateMerchant() {
        $user = $this->auth->user();
        $filename = "Filename13.xlsx";
        $merchantid = "5";
        return response()->json($this->merchantImport->importUpdateMerchant($user, $filename, $merchantid));
    }

    public function textSearch(Request $request) {
        $data = $request->all();
        $results = $this->editMapObject->textSearchMerchant($data);
        return view(config("app.views") . '.merchants.listing', ["merchants" => $results]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getNearbyMerchants(Request $request, $categoryS) {
        $data = $request->all();
        $category = Category::where('url', $categoryS)->first()->toArray();
        if ($category) {
            $validator = $this->editMapObject->validatorLat($data);
            if ($validator->fails()) {
                return $this->index($request, $categoryS);
            }
            $data['category'] = $category['id'];
            $data['type'] = "Merchant";
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

            return view(config("app.views") . '.merchants.listing', ["merchants" => $results]);
        }
        abort(404);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getCoverageMerchants(Request $request, $category) {
        $data = $request->all();
        $validator = $this->editMapObject->validatorLat($data);
        if ($validator->fails()) {
            return $this->index($request, $category);
        }
        $category = Category::where('url', $category)->first()->toArray();
        if ($category) {
            $data['category'] = $category['id'];
            $results = $this->editMapObject->buildCoverageQuery($data);
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
            return view(config("app.views") . '.merchants.listing', ["merchants" => $results]);
        }
        abort(404);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getPaymentMethodsMerchant($id) {
        return response()->json($this->editMapObject->getPaymentMethodsMerchant($id));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getMerchantOrders($id) {
        $user = $this->auth->user();
        return view(config("app.views") . '.merchants.merchant')->with('merchant', $this->editMapObject->getMerchantOrders($user, $id));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getMerchantDetail($url) {
        $user = $this->auth->user();
        $merchant = Merchant::where("slug", $url)->with(["files", 'ratings', 'availabilities2', 'categories'])->first();
        if ($merchant) {
            $results = $this->editMapObject->getActiveCategoriesMerchant($merchant->id);
            $results = $results['data'];
            $results = array_map(function ($value) {
                return (array) $value;
            }, $results);
            return view(config("app.views") . '.merchants.detail')->with('side_categories', $results)->with("merchant", $merchant);
        }
        abort(404);
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getRegisterMerchant() {
        $user = $this->auth->user();
        return view(config("app.views") . '.merchants.editMerchant')->with('user', $user);
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function postRegisterMerchant(Request $request) {

        $this->editMapObject->createUserObject($request->all());

        return view(config("app.views") . '.merchants.complete');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(Request $request) {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        return $this->editMapObject->getMerchantOrders($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id) {
        //
    }

}
