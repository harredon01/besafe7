<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Unlu\Laravel\Api\QueryBuilder;
use App\Models\CoveragePolygon;
use App\Models\Merchant;
use Grimzy\LaravelMysqlSpatial\Types\MultiPolygon;
use Excel;

class ZonesController extends Controller {
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
    public function __construct() {

        $this->middleware('auth:api')->except('getZonesPublicView');
        $this->middleware('admin')->except('getZonesPublicView');
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function index() {
        
    }

    public function deleteZoneItem(Request $request, $item) {
        CoveragePolygon::where("id", $item)->delete();
        return response()->json([
                    'status' => "success",
                    "message" => "item deleted",
        ]);
    }

    public function createZoneItem(Request $request) {
        $data = $request->all();
        $cpolygon = new CoveragePolygon();
        $cpolygon->fill($data);
        $merchant = Merchant::find($data['merchant_id']);
        if ($merchant) {
            if ($merchant->lat) {
                $cpolygon->lat = $merchant->lat;
                $cpolygon->long = $merchant->long;
            }
        } else {
            return response()->json([
                        'status' => "success",
                        "message" => "Merchant not found",
            ]);
        }
        $coordPoints = json_decode($cpolygon->coverage, true);
        $totalPoints = [];
        foreach ($coordPoints as $coordPoint) {
            $pointArray = [$coordPoint['lng'], $coordPoint['lat']];
            array_push($totalPoints, $pointArray);
        }
        $result = [
            "type" => "MultiPolygon",
            "coordinates" =>
            [[$totalPoints]]
        ];
        $mp = MultiPolygon::fromJson(json_encode($result));
        $cpolygon->geometry = $mp;
        $cpolygon->save();
        return response()->json([
                    'status' => "success",
                    "message" => "item created",
                    "item" => $cpolygon
        ]);
    }

    public function updateZoneItem(Request $request, $item) {
        $data = $request->all();
        $cpolygon = CoveragePolygon::find($item);
        if ($cpolygon) {
            $cpolygon->fill($data);
            $coordPoints = json_decode($cpolygon->coverage, true);
            $totalPoints = [];
            foreach ($coordPoints as $coordPoint) {
                $pointArray = [$coordPoint['lng'], $coordPoint['lat']];
                array_push($totalPoints, $pointArray);
            }
            $result = [
                "type" => "MultiPolygon",
                "coordinates" => [[$totalPoints]]
            ];
            $mp = MultiPolygon::fromJson(json_encode($result));
            $cpolygon->geometry = $mp;
            $cpolygon->save();
        }
        return response()->json([
                    'status' => "success",
                    "message" => "item updated",
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getZones(Request $request) {
        $queryBuilder = new QueryBuilder(new CoveragePolygon, $request);
        $result = $queryBuilder->build()->paginate();
        return response()->json([
                    'data' => $result->items(),
                    "total" => $result->total(),
                    "per_page" => $result->perPage(),
                    "page" => $result->currentPage(),
                    "last_page" => $result->lastPage(),
        ]);
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getZonesView() {
        return view(config("app.views").'.food.zones');
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getZonesPublicView() {
        return view(config("app.views").'.content.zonespublic');
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
            $this->importPolygons($path);
        }
        return view(config("app.views").'.food.zones')->with('user', $user);
    }

    private function importPolygons($path) {
        $excel = Excel::load($path);
        $reader = $excel->toArray();
        foreach ($reader as $row) {
            $coverage = $row['coverage'];
            $coverage = str_replace("new google.maps.LatLng(", '{"lat":', $coverage);
            $coverage = str_replace("-74", '"lng":-74', $coverage);
            $coverage = str_replace("),", '},', $coverage);
            $coverage = str_replace(")", '}', $coverage);
            $coverage = "[" . $coverage . "]";
            $resultset = json_decode($coverage, true);
            $firstItem = $resultset[0];
            array_push($resultset, $firstItem);
            $row['coverage'] = json_encode($resultset);
            $find["id"] = $row["id"];
            CoveragePolygon::updateOrCreate($find, $row);
        }
    }

}
