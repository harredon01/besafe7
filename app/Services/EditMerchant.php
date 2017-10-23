<?php

namespace App\Services;

use Validator;
use App\Models\FileM;
use App\Models\Merchant;
use App\Models\User;
use App\Models\Group;
use App\Models\Report;
use Illuminate\Http\Response;
use DB;

class EditMerchant {

    const OBJECT_REPORT_GROUP = 'Report_Group';
    const OBJECT_MERCHANT_GROUP = 'Merchant_Group';
    const OBJECT_REPORT = 'Report';
    const OBJECT_MERCHANT = 'Merchant';

    /**
     * The EditAlert implementation.
     *
     */
    protected $editAlerts;

    /**
     * Create a new class instance.
     *
     * @param  EventPusher  $pusher
     * @return void
     */
    public function __construct(EditAlerts $editAlerts) {
        $this->editAlerts = $editAlerts;
    }

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
    public function deleteObject(User $user, $reportId, $type) {
        $type = "App\\Models\\" . $type;
        $object = $type::find($reportId);
        if ($user->id == $object->user_id) {
            $object->delete();
        } else {
            if ($object->group_id) {
                $members = DB::select('select user_id as id from group_user where user_id  = ? and group_id = ? and status <> "blocked" and is_admin = true ', [$user->id, $object->group_id]);
                if (sizeof($members) == 0) {
                    return null;
                } else {
                    $object->group_id = null;
                    $object->save();
                }
            }
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getObjectUser(User $user, $objectId, $type) {
        $target = "App\\Models\\" . $type;
        $object = $target::find($objectId);
        if ($object) {
            $send = false;
            if ($object->user_id == $user->id) {
                $object->mine = true;
                $send = true;
            } else if ($object->private) {
                $group = DB::table('userables')
                                ->where('user_id', $user->id)
                                ->where('userable_type', $type)
                                ->where("object_id", $object->id)->first();
                if ($group) {
                    $send = true;
                }
            } else if (!$object->private) {
                $send = true;
            }
            if ($send == true) {
                $files = FileM::where("type", $type)->where("trigger_id", $object->id)->get();
                if ($type == self::OBJECT_REPORT) {
                    if ($object->private == true && $type == "Report") {
                        $object->email == "";
                        $object->telephone == "";
                    }
                    $data = [
                        "report" => $object,
                        "files" => $files,
                    ];
                } else if ($type == self::OBJECT_MERCHANT) {
                    $data = [
                        "merchant" => $object,
                        "files" => $files,
                        "products" => $object->products()->with("productVariants")->get()
                    ];
                }


                return $data;
            }
            return ['status' => "error", "message" => $type . ' not found for user'];
        } else {
            return ['status' => "error", "message" => $type . ' not found'];
        }
    }

    public function getObjectByHash($code, $type) {
        $target = "App\\Models\\" . $type;
        $object = $target::where('hash', $code)->first();
        if ($object) {
            $files = FileM::where("type", $type)->where("trigger_id", $object->id)->get();
            if ($type == self::OBJECT_REPORT) {
                if ($object->private == true && $type == "Report") {
                    $object->email == "";
                    $object->telephone == "";
                }
                $data = [
                    "report" => $object,
                    "files" => $files,
                ];
            } else if ($type == self::OBJECT_MERCHANT) {
                $data = [
                    "merchant" => $object,
                    "files" => $files,
                ];
            }
            return $data;
        } else {
            return ['status' => "error", "message" => $type . ' not found'];
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
    public function getNearby(array $data) {
        $merchants = $this->getNearbyMerchants($data);
        $reports = $this->getNearbyReports($data);
        return array("merchants" => $merchants['merchants'], "reports" => $reports['reports']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getNearbyMerchants(array $data) {
        $radius = 1;
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
                        . "SELECT m.id, name, description, icon, minimum, lat,`long`, type, telephone, address, 
			( 6371 * acos( cos( radians( :lat ) ) *
		         cos( radians( m.lat ) ) * cos( radians(  `long` ) - radians( :long ) ) +
		   sin( radians( :lat2 ) ) * sin( radians(  m.lat  ) ) ) ) AS Distance 
                   FROM merchants m
                    WHERE
                        status = 'active'
                            AND m.private = 0
                            AND m.type <> ''
                            AND lat BETWEEN :latinf AND :latsup
                            AND `long` BETWEEN :longinf AND :longsup
                    HAVING distance < :radius order by distance asc limit 20 "
                        . "", $thedata);
        return array("merchants" => $merchants);
    }

    public function getNearbyReports(array $data) {
        $radius = 1;
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
            'radius' => $radius,
        ];
        $reports = DB::select(" "
                        . "SELECT r.id, name, description, icon, lat,`long`, type, telephone, address, report_time,
			( 6371 * acos( cos( radians( :lat ) ) *
		         cos( radians( r.lat ) ) * cos( radians(  `long` ) - radians( :long ) ) +
		   sin( radians( :lat2 ) ) * sin( radians(  r.lat  ) ) ) ) AS Distance  
                   FROM
                        reports r
                    WHERE
                        status = 'active'
                            AND r.private = 0
                            AND r.type <> ''
                            AND lat BETWEEN :latinf AND :latsup
                            AND `long` BETWEEN :longinf AND :longsup
                    HAVING distance < :radius
                    order by distance asc limit 20 "
                        . "", $thedata);
        return array("reports" => $reports);
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

    function checkGroupStatus(User $user, Group $group, array $data) {
        if ($group->is_public && $group->isActive()) {
            $members = DB::select('select user_id as id, is_admin from group_user where user_id  = ? and group_id = ? AND status <> "blocked" ', [$user->id, $group->id]);
            if (sizeof($members) == 0) {
                return null;
            } else {
                $member = $members[0];
                if ($member->is_admin) {
                    $data['status'] = "active";
                } else {
                    $data['status'] = "pending";
                }
            }
        } else if (!$group->is_public) {
            $members = DB::select('select user_id as id from group_user where user_id  = ? and group_id = ? ', [$user->id, $group->id]);
            if (sizeof($members) > 0) {
                $data['status'] = "active";
            } else {
                return null;
            }
        } else {
            return null;
        }
        return $data;
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
                $merchantid = $data['merchant_id'];
                unset($data['merchant_id']);
                Merchant::where('id', $merchantid)->update($data);
                $merchant = Merchant::find($merchantid);
                if ($merchant) {
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

    public function notifyGroup(Group $group, User $user, array $data, $type, $object) {
        if ($group) {
            if ($group->is_public && $group->isActive()) {
                if ($data['status'] == "pending") {
                    $followers = DB::select("SELECT user_id as id FROM group_user WHERE group_id=?  AND is_admin = 1 AND status <> 'blocked' and user_id <>? ", [intval($data["group_id"]), $user->id]);
                } elseif ($data['status'] == "active") {
                    $followers = DB::select("SELECT user_id as id FROM group_user WHERE group_id=?  AND status <> 'blocked' and user_id <>? ", [intval($data["group_id"]), $user->id]);
                } else {
                    return null;
                }
            } else if (!$group->is_public) {
                $followers = DB::select("SELECT user_id as id FROM group_user WHERE group_id=? and user_id <>? ", [intval($data["group_id"]), $user->id]);
            } else {
                return null;
            }
            $payload = array("class" => $type, "type" => $object->type, "object_type" => $object->type, "object_id" => $object->id, "first_name" => $user->firstName, "last_name" => $user->lastName, "group_name" => $group->name, "group_id" => $group->id);
            if ($type == "Report") {
                $type = self::OBJECT_REPORT_GROUP;
            } else if ($type == "Merchant") {
                $type = self::OBJECT_MERCHANT_GROUP;
            }
            $data = [
                "trigger_id" => $user->id,
                "message" => "",
                "subject" => "",
                "payload" => $payload,
                "type" => $type,
                "user_status" => $this->editAlerts->getUserNotifStatus($user)
            ];
            $this->editAlerts->sendMassMessage($data, $followers, $user, true);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function saveOrCreateObject(User $user, array $data, $type) {

        $group = null;
        if ($type == self::OBJECT_REPORT) {
            if ($data['anonymous']) {
                if ($data['anonymous'] == true) {
                    $data['email'] = "";
                    $data['telephone'] = "";
                }
            }
        }
        if ($data['group_id']) {
            $group = Group::find($data["group_id"]);
            if ($group) {
                $data = $this->checkGroupStatus($user, $group, $data);
                if (!$data) {
                    return ['status' => 'error', "message" => "Access check for this group failed"];
                }
                $data['private'] = true;
            }
        } else {
            $data['status']='active';
        }
        if ($data['id']) {
            foreach ($data as $key => $value) {
                if (!$value) {
                    unset($data[$key]);
                }
            }
            $object = $this->updateObject($user, $data, $type);
        } else {
            if ($type == self::OBJECT_MERCHANT) {
                $validator = $this->validatorMerchant($data);
                if ($validator->fails()) {
                    return $validator->getMessageBag();
                }
            } else if ($type == self::OBJECT_REPORT) {
                $validator = $this->validatorReport($data);
                if ($validator->fails()) {
                    return $validator->getMessageBag();
                }
            }
            $object = $this->createObject($user, $data, $type);
        }
        if ($group) {
            $this->notifyGroup($group, $user, $data, $type, $object);
        }
        return ['status' => 'success', "message" => "Result saved: " . $object->name, "object" => $object];
    }

    /**
     * returns all current shared locations for the user
     *
     * @return Location
     */
    public function updateObject(User $user, array $data, $object) {
        $object = "App\\Models\\" . $object;
        $object::where('user_id', $user->id)
                ->where('id', $data['id'])->whereIn('status', ['active', 'pending'])->update($data);
        $result = $object::find($data['id']);
        if ($result) {
            return $result;
        } else {
            return null;
        }
    }

    /**
     * returns all current shared locations for the user
     *
     * @return Location
     */
    public function updateObjectStatus(User $user, array $data, $object) {
        $object = "App\\Models\\" . $object;
        $result = $object::find($data['id']);
        if ($result && $data['status']) {
            if ($result->user_id == $user->id) {
                $result->status = $data['status'];
                $result->save();
                return ['status' => 'success', "message" => "status updated"];
            } else {
                if ($result->group_id) {
                    $group = $result->group;
                    $targetStatus = $data['status'];
                    $data = $this->checkGroupStatus($user, $group, $data);
                    if ($data['status'] == 'active') {
                        $result->status = $targetStatus;
                        $result->save();
                        return ['status' => 'success', "message" => "status updated"];
                    } else {
                        return ['status' => 'error', "message" => "you must own the report or be an admin in its hive"];
                    }
                } else {
                    return ['status' => 'error', "message" => "you must own the report or be an admin in its hive"];
                }
            }
        } else {
            return ['status' => 'error', "message" => "please submit valid object and status"];
        }
    }

    /**
     * returns all current shared locations for the user
     *
     * @return Location
     */
    public function createObject(User $user, array $data, $object) {
        $data["user_id"] = $user->id;
        $object = "App\\Models\\" . $object;
        $result = $object::create($data);
        return $result;
    }

    /**
     * returns all current shared locations for the user
     *
     * @return Location
     */
    public function getObjectHash(User $user, $id, $type) {
        $type = "App\\Models\\" . $type;
        $object = $type::find($id);
        if ($object) {
            if ($object->user_id == $user->id || !$object->private) {
                if ($object->hash) {
                    return ['status' => 'success', "hash" => $object->hash];
                }
                $hashExists = true;
                while ($hashExists) {
                    $hash = str_random(40);
                    $objects = $type::where("hash", $hash)->first();
                    if ($objects) {
                        $hashExists = true;
                    } else {
                        $hashExists = false;
                        $object->hash = $hash;
                        $object->save();
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
                    'address' => 'required|max:255',
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
    public function validatorReport(array $data) {
        return Validator::make($data, [
                    'name' => 'required|max:255',
                    'type' => 'required|max:255',
                    'report_time' => 'required|max:255',
                    'address' => 'required|max:255',
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
    public function validatorLat(array $data) {
        return Validator::make($data, [
                    'lat' => 'required',
                    'long' => 'required',
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
