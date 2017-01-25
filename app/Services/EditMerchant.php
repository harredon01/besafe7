<?php

namespace App\Services;

use Validator;
use App\Models\FileM;
use App\Models\Merchant;
use App\Models\User;
use App\Models\Report;
use Illuminate\Http\Response;
use DB;

class EditMerchant {

    /**
     * The Auth implementation.
     *
     */
    protected $auth;

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getMerchant($merchant_id) {
        $users = DB::select('SELECT me.*, ca.name as categoryname, pr.id as productid, pr.name as productname, pr.description as productdesc, pr.total as producttotal'
                        . ' FROM merchants me join categories ca on me.id = ca.merchant_id '
                        . 'join products pr on ca.id = pr.category_id where me.id = :id', ['id' => $merchant_id]);
        $target = array();
        foreach ($users as $user) {
            $target[$user->name]["name"] = $user->name;
            $target[$user->name]["description"] = $user->description;
            $target[$user->name]["delivery_time"] = $user->delivery_time;
            $target[$user->name]["delivery_price"] = $user->delivery_price;
            $target[$user->name]["categories"]["$user->categoryname"]["name"] = $user->categoryname;
            $target[$user->name]["categories"]["$user->categoryname"]["products"] = array('ProductName' => $user->productname, 'ProductDescription' => $user->productdesc, 'total' => $user->producttotal);
        }
        return $target;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function findMerchant($name) {
        $merchant = Merchant::where('name', 'LIKE', '%' . $name['name'] . '%')->get();
        return $merchant;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getPaymentMethodsMerchant($id) {
        $merchant = Merchant::find($id);
        $merchant->paymentMethods;
        return $merchant;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function shareReport(User $user, array $data) {
        $page = $data['page'];
        $per_page = $data['per_page'];
        $order_by = $data['order_by'];
        $order_dir = $data['order_dir'];
        $values = ($page - 1) * $per_page;
        $total = Report::where('id', '>', 0)->count();
        $reports = Report::where('id', '>', 0)->orderBy($order_by, $order_dir)->skip($values)->take($per_page)->get();
        $data = [
            "reports" => $reports,
            "user" => $user,
            "page" => $page,
            "perpage" => $per_page,
            "order_by" => $order_by,
            "order_dir" => $order_dir,
            "total" => $total
        ];
        return $data;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function deleteReport(User $user, $reportId) {
        $report = Report::find($reportId);
        if ($user->id == $report->user_id) {
            $report->delete();
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getReport(User $user, $reportId) {
        $report = Report::find($reportId);
        if ($report) {

            $files = FileM::where('type', 'report')->where("trigger_id", $reportId)->get();
            if ($report->private == true) {
                $report->email = "";
                $report->celphone = "";
                $data = [
                    "report" => $report,
                    "user" => "",
                    "files" => $files,
                ];
            } else {
                $reportingUser = User::find($report->user_id);
                $data = [
                    "report" => $report,
                    "user" => $reportingUser,
                    "files" => $files,
                ];
            }
        }

        return $data;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getReportUser(User $user, $reportId) {
        $report = Report::find($reportId);
        if ($report) {
            $send = false;
            if ($report->user_id == $user->id) {
                $send = true;
            } else if ($report->private) {
                $group = DB::table('userables')
                                ->where('user_id', $user->id)
                                ->where('userable_type', "Report")
                                ->where("object_id", $report->id)->first();
                if ($group) {
                    $send = true;
                }
            } else if (!$report->private) {
                $send = true;
            }
            if ($send == true) {
                $files = FileM::where("type", "report")->where("trigger_id", $report->id)->get();
                if ($report->private == true) {
                    $report->email == "";
                    $report->telephone == "";
                }
                $data = [
                    "report" => $report,
                    "files" => $files,
                ];
                return $data;
            }
            return ['status' => "error", "message" => 'Report not found for user'];
        } else {
            return ['status' => "error", "message" => 'Report not found'];
        }
    }

    public function getReportByHash($code) {
        $report = Report::where('hash', $code)->first();
        if ($report) {
            $files = FileM::where("type", "report")->where("trigger_id", $report->id)->get();
            if ($report->private == true) {
                $report->email == "";
                $report->telephone == "";
            }
            $data = [
                "report" => $report,
                "files" => $files,
            ];
            return $data;
        } else {
            return ['status' => "error", "message" => 'Report not found'];
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function deleteUserReport(User $user, $reportId) {
        $report = Report::find($reportId);
        if ($report) {
            if ($user->id == $report->user_id) {
                $report->delete();
            }
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function approveReport(User $user, $reportId) {
        if ($user->email == "harredon01@gmail.com") {
            $reports = Report::where('id', '=', $reportId)
                            ->get()->toArray();
            if (sizeof($reports) > 0) {
                Merchant::insert($reports);
                dd($reports);
                Report::where('id', '=', $reportId)
                        ->delete();
            }
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getMerchantOrders(User $user, $id) {
        $merchants = "";
        foreach ($user->merchants as $merchant) {
            if ($merchant->id == $id) {
                $merchant = Merchant::find($id);
                $merchant->orders;
                return $merchant;
            }
        }
        return $merchants;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getNearby(User $user, array $data) {
        $radius = $data['radius'];
        $R = 6371;
        $lat = $data['lat'];
        $long = $data['long'];
        $maxLat = $lat + rad2deg($radius / $R);
        $minLat = $lat - rad2deg($radius / $R);
        $maxLon = $long + rad2deg(asin($radius / $R) / cos(deg2rad($lat)));
        $minLon = $long - rad2deg(asin($radius / $R) / cos(deg2rad($lat)));
        $thedata = [
            'lat' => $lat,
            'lat2' => $lat,
            'long' => $long,
            'latinf' => $minLat,
            'latsup' => $maxLat,
            'longinf' => $minLon,
            'longsup' => $maxLon,
            'radius' => $radius
        ];
        $merchants = DB::select(""
                        . "SELECT merchants.id, name, description, icon, minimum, lat,`long`, type, telephone, address, 
			( 6371 * acos( cos( radians( :lat ) ) *
		         cos( radians( merchants.lat ) ) * cos( radians(  `long` ) - radians( :long ) ) +
		   sin( radians( :lat2 ) ) * sin( radians(  merchants.lat  ) ) ) ) AS Distance FROM merchants 
			
                    WHERE status = 'active' and lat BETWEEN :latinf AND :latsup
        AND `long` BETWEEN :longinf AND :longsup 
                    HAVING distance < :radius order by distance asc"
                        . "", $thedata);
        $thedata = [
            'lat' => $lat,
            'lat2' => $lat,
            'long' => $long,
            'latinf' => $minLat,
            'latsup' => $maxLat,
            'longinf' => $minLon,
            'longsup' => $maxLon,
            'radius' => $radius,
            'user_id' => $user->id,
            'user_id2' => $user->id,
        ];
        $reports = DB::select(""
                        . "SELECT r.id, name, description, icon, lat,`long`, type, telephone, address, 
			( 6371 * acos( cos( radians( :lat ) ) *
		         cos( radians( r.lat ) ) * cos( radians(  `long` ) - radians( :long ) ) +
		   sin( radians( :lat2 ) ) * sin( radians(  r.lat  ) ) ) ) AS Distance  
                   FROM
                        reports r
                            left join
                        userables u ON r.id = u.object_id
                    WHERE
                        status = 'active'
                            AND ((r.private = 0) OR (r.user_id = :user_id)
                            OR (u.user_id = :user_id2
                            AND u.userable_type = 'Report'))
                            and lat BETWEEN :latinf AND :latsup
                            AND `long` BETWEEN :longinf AND :longsup
                    HAVING distance < :radius
                    order by distance asc"
                        . "", $thedata);
        $results = array("merchants" => $merchants, "reports" => $reports);
        return $results;
    }

    function findLonBoundary($lat, $lon, $lat1, $lat2) {

        $d = $lat - $lat1;

        $d1 = $d / cos(deg2rad($lat1));
        $d2 = $d / cos(deg2rad($lat2));

        $lon1 = min($lon - $d1, $lon - $d2);
        $lon2 = max($lon + $d1, $lon + $d2);
        return array('longinf' => $lon1, 'longsup' => $lon2);
    }

    function findLatBoundary($dist, $lat, &$lat1, &$lat2) {
        $d = ($dist / 6371.01 * 2 * M_PI) * 360;
        $lat1 = $lat - $d;
        $lat2 = $lat + $d;
        return array('latinf' => $lat1, 'latsup' => $lat2);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function saveOrCreateMerchant(array $data) {
        $validator = $this->validatorMerchant($data);
        if ($validator->fails()) {
            return $validator->getMessageBag();
        }
        if (array_key_exists("merchant_id", $data)) {
            if ($data['merchant_id'] > 0) {
                $merchant = Merchant::find($data['merchant_id']);
                if ($merchant) {
                    $merchant->name = $data['name'];
                    $merchant->email = $data['email'];
                    $merchant->telefone = $data['telefone'];
                    $merchant->description = $data['description'];
                    $merchant->status = $data['status'];
                    $merchant->icon = $data['icon'];
                    $merchant->save();
                    return ['status' => "success", "message" => 'Merchant saved', "name" => $merchant->name];
                }
                return ['status' => "error", "message" => 'Merchant not found'];
            }
            return ['status' => "error", "message" => 'Merchant id invalid'];
        } else {
            $data["status"] = "active";
            $data["icon"] = "default";
            $merchant = Merchant::create($data);
            return ['status' => "", "message" => 'Merchant saved', "name" => $merchant->name];
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function saveOrCreateReport(User $user, array $data) {
        $validator = $this->validatorReport($data);
        if ($validator->fails()) {
            return $validator->getMessageBag();
        }
        if (array_key_exists("id", $data)) {
            if ($data['id'] > 0) {
                $report = Report::find($data['id']);
                if ($report) {
                    if ($report->user_id == $user->id) {
                        $report->name = $data['name'];
                        if (array_key_exists("telefone", $data)) {
                            $report->telefone = $data['telefone'];
                        }
                        if (array_key_exists("email", $data)) {
                            $report->email = $data['email'];
                        }
                        $report->description = $data['description'];
                        $report->status = $data['status'];
                        $report->address = $data['address'];
                        $report->icon = $data['icon'];
                        $report->city_id = $data['city_id'];
                        $report->region_id = $data['region_id'];
                        $report->country_id = $data['country_id'];
                        $report->type = $data['city_id'];
                        $report->lat = $data['lat'];
                        $report->long = $data['long'];
                        $report->save();
                        return ['status' => 'success', "message" => "Report saved: " . $report->name, "report_id" => $report->id];
                    }
                    return ['status' => 'error', "message" => "report does not belong to user"];
                }
                return ['status' => 'error', "message" => "report does not exist"];
            }
            return ['status' => 'error', "message" => "report id invalid"];
        } else {
            $data["status"] = "active";
            $data["icon"] = "default";
            $data["user_id"] = $user->id;
            if (!array_key_exists("email", $data)) {
                $data["email"] = $user->email;
            } else {
                if ($data["email"]) {
                    
                } else {
                    $data["email"] = $user->email;
                }
            }
            if (!array_key_exists("telephone", $data)) {
                $data["telephone"] = $user->area_code . " " . $user->cellphone;
            } else {
                if ($data["telephone"]) {
                    
                } else {
                    $data["telephone"] = $user->area_code . " " . $user->cellphone;
                }
            }
            $report = Report::create($data);
            return ['status' => 'success', "message" => "Report saved: " . $report->name, "report_id" => $report->id];
        }
    }

    /**
     * returns all current shared locations for the user
     *
     * @return Location
     */
    public function getReportHash(User $user, $id) {
        $report = Report::find($id);
        if ($report) {
            if ($report->user_id == $user->id) {
                $hashExists = true;
                while ($hashExists) {
                    $hash = str_random(40);
                    $reports = DB::select("SELECT * from reports where hash = ? ", [$hash]);
                    if ($reports) {
                        $hashExists = true;
                    } else {
                        $hashExists = false;
                        $report->hash = $hash;
                        $report->save();
                        return ['status' => 'success', "hash" => $hash];
                    }
                }
            }
            return ['status' => 'error', "message" => "report does not belong to user"];
        }
        return ['status' => 'error', "message" => "report id invalid"];
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorMerchant(array $data) {
        return Validator::make($data, [
                    'name' => 'required|max:255',
                    'telephone' => 'required|max:255',
                    'description' => 'required|max:255',
                    'email' => 'required|email|max:255|unique:merchants',
        ]);
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorReport(array $data) {
        return Validator::make($data, [
                    'name' => 'required|max:255',
                    'description' => 'required',
                    'type' => 'required|max:255',
                    'city_id' => 'required',
                    'region_id' => 'required',
                    'country_id' => 'required',
                    'lat' => 'required',
                    'long' => 'required',
        ]);
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorGetNearby(array $data) {
        return Validator::make($data, [
                    'lat' => 'required|max:255',
                    'long' => 'required|max:255',
                    'radius' => 'required|max:255',
        ]);
    }

    /**
     * Get the failed login message.
     *
     * @return string
     */
    protected function getFailedEditMerchantMessage() {
        return 'There was a problem editing the merchant';
    }

}
