<?php

namespace App\Services;

use Validator;
use App\Models\FileM;
use App\Models\Merchant;
use App\Models\Category;
use App\Models\User;
use App\Jobs\SaveGroupsObject;
use App\Jobs\CreateMerchant;
use App\Models\Group;
use App\Models\Report;
use App\Models\Favorite;
use App\Models\Rating;
use App\Models\Availability;
use Illuminate\Http\Response;
use DB;
use Cache;

class EditMapObject {

    const MODEL_PATH = 'App\\Models\\';
    const OBJECT_REPORT_GROUP = 'Report_Group';
    const OBJECT_MERCHANT_GROUP = 'Merchant_Group';
    const OBJECT_REPORT_ACCESS_GROUP = 'Report_Access_Group';
    const OBJECT_MERCHANT_ACCESS_GROUP = 'Merchant_Access_Group';
    const OBJECT_REPORT = 'Report';
    const OBJECT_GROUP = 'Group';
    const OBJECT_USER = 'User';
    const OBJECT_MERCHANT = 'Merchant';
    const GROUP_PENDING = 'group_pending';
    const GROUP_BLOCKED = 'group_blocked';
    const GROUP_MERCHANT_TABLE = 'group_merchant';
    const GROUP_REPORT_TABLE = 'group_report';
    const CONTACT_BLOCKED = 'contact_blocked';
    const OBJECT_PAGESIZE = 50;

    /**
     * The EditAlert implementation.
     *
     */
    protected $notifications;

    /**
     * The EditAlert implementation.
     *
     */
    protected $editGroup;

    /**
     * Create a new class instance.
     *
     * @param  EventPusher  $pusher
     * @return void
     */
    public function __construct(EditGroup $editGroup) {
        $this->notifications = app('Notifications');
        $this->editGroup = $editGroup;
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
        return ['status' => "success", "data" => $merchant->paymentMethods];
    }

    public function getCategoriesMerchant($id, $type) {
        $categories = DB::select(" "
                        . "SELECT * FROM categories WHERE id IN ( SELECT DISTINCT(category_id)"
                        . " from categorizable where categorizable_type='App//Models//Product') and name like '%:name%' limit 15"
                        . "", ['name' => $type]);
        //DB::enableQueryLog();
        //dd(DB::getQueryLog());
        return ['status' => "success", "data" => $categories];
    }
    public function getActiveCategoriesMerchant($id) {
        //DB::enableQueryLog();
        $categories = DB::select(" "
                        . "SELECT 
    c.*, COUNT(categorizable_id) AS tots
FROM
    categorizables ca
        JOIN
    categories c ON c.id = ca.category_id
WHERE
    categorizable_type = 'App\\\Models\\\Product'
        AND categorizable_id IN (SELECT 
            product_id
        FROM
            merchant_product mp
                JOIN
            products p ON mp.product_id = p.id
        WHERE
            merchant_id = :merchant_id AND p.isActive = TRUE)
GROUP BY category_id"
                        . "", ['merchant_id' => $id]);
        //DB::enableQueryLog();
        //dd(DB::getQueryLog());
        return ['status' => "success", "data" => $categories];
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
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function deleteObjectFromGroup(User $user, $objectId, $groupId, $type) {
        $type = "App\\Models\\" . $type;
        $object = $type::find($objectId);
        if ($object) {
            $members = DB::select('select user_id as id from group_user where user_id  = ? and group_id = ? and level <> "' . self::GROUP_BLOCKED . '"  && level <> "' . self::GROUP_PENDING . '" and is_admin = true ', [$user->id, $object->group_id]);
            if (sizeof($members) == 0) {
                return null;
            } else {
                if ($type == "Report") {
                    DB::table('group_report')->where('report_id', $objectId)->where('group_id', $groupId)->delete();
                } else if ($type == "Merchant") {
                    DB::table('group_merchant')->where('merchant_id', $objectId)->where('group_id', $groupId)->delete();
                }
            }
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getObjectUser($user, $objectId, $type) {
        if (false) {
            $data = Cache::remember($type . '_' . $objectId, 100, function ()use ($type, $objectId) {
                        $target = "App\\Models\\" . $type;
                        $object = $target::find($objectId);
                        $files = FileM::where("type", $type)->where("trigger_id", $object->id)->get();
                        $ratings = Rating::where("type", $type)->where("object_id", $object->id)->orderBy('id', 'desc')->limit(20)->get();
                        if ($type == self::OBJECT_REPORT) {
                            if ($object->private == true && $type == "Report") {
                                $object->email == "";
                                $object->telephone == "";
                            }
                            $data = [
                                "report" => $object,
                                "files" => $files,
                                "ratings" => $ratings
                            ];
                        } else if ($type == self::OBJECT_MERCHANT) {
                            $availability = Availability::where("bookable_type", self::MODEL_PATH . $type)->where("bookable_id", $object->id)->limit(25)->get();
                            $data = [
                                "merchant" => $object,
                                "files" => $files,
                                "ratings" => $ratings,
                                "availabilities" => $availability,
                                "access" => $object->checkAdminAccess($user->id)
                            ];
                        }
                        return $data;
                    });
        } else {
            $target = "App\\Models\\" . $type;
            $object = $target::find($objectId);
            $files = FileM::where("type", $type)->where("trigger_id", $object->id)->get();
            $ratings = Rating::where("type", $type)->where("object_id", $object->id)->orderBy('id', 'desc')->limit(20)->get();
            if ($type == self::OBJECT_REPORT) {
                if ($object->private == true && $type == "Report") {
                    $object->email = "";
                    $object->telephone = "";
                }
                $data = [
                    "report" => $object,
                    "files" => $files,
                    "ratings" => $ratings
                ];
            } else if ($type == self::OBJECT_MERCHANT) {
                $availability = Availability::where("bookable_type", self::MODEL_PATH . $type)->where("bookable_id", $object->id)->limit(25)->get();
                $access = false;
                if ($user) {
                    $access = $object->checkAdminAccess($user->id);
                }
                $data = [
                    "merchant" => $object,
                    "files" => $files,
                    "ratings" => $ratings,
                    "availabilities" => $availability,
                    "access" => $access
                ];
            }
        }

        $object = null;
        if ($type == self::OBJECT_REPORT) {
            $object = $data['report'];
        } else if ($type == self::OBJECT_MERCHANT) {
            $object = $data['merchant'];
        }

        if ($object) {
            $send = false;
            if ($object->private) {
                if ($user) {
                    $send = $object->checkUserAccess($user);
                } else {
                    $send = false;
                }
            } else {
                $send = true;
            }
            if ($send == true) {
                $data['favorite'] = false;
                if ($user) {
                    $favor = Favorite::where('user_id', $user->id)->where("favorite_type", $type)->where("object_id", $object->id)->first();
                    if ($favor) {
                        $data['favorite'] = true;
                    }
                }
                $data['status'] = 'success';
                return $data;
            }
            return ['status' => "error", "message" => $type . ' not found for user'];
        } else {
            return ['status' => "error", "message" => $type . ' not found'];
        }
    }

    public function getObject($data) {
        $object = null;
        if (array_key_exists('object_id', $data) && array_key_exists('type', $data)) {
            if ($data['object_id'] && $data['type']) {
                $target = "App\\Models\\" . $data['type'];
                $object = $target::where('id', $data['object_id'])->where('private', false)->first();
            }
        }
        if (!$object) {
            return ['status' => "error", "message" => $data['type'] . ' not found'];
        }
        $includes_files = false;
        $includes_ratings = false;
        $includes_availabilities = false;
        if (array_key_exists("includes", $data)) {
            if ($data['includes']) {
                $includes = $data['includes'];
                $includes = explode(',', $includes);
                foreach ($includes as $value) {
                    if ($value == 'ratings') {
                        $includes_ratings = true;
                    } else if ($value == 'files') {
                        $includes_files = true;
                    } else if ($value == 'availabilities') {
                        $includes_availabilities = true;
                    }
                }
            }
        }
        $files = [];
        if ($includes_files) {
            $files = FileM::where("type", self::MODEL_PATH . $data['type'])->where("trigger_id", $object->id)->get();
        }
        $ratings = [];
        if ($includes_ratings) {
            $ratings = Rating::where("type", $data['type'])->where("object_id", $object->id)->orderBy('id', 'desc')->limit(20)->get();
        }
        $availabilities = [];
        if ($includes_availabilities) {
            $availabilities = Availability::where("bookable_type", self::MODEL_PATH . $data['type'])->where("bookable_id", $object->id)->limit(25)->get();
        }
        if ($data['type'] == self::OBJECT_REPORT) {
            $object->email = "";
            $object->telephone = "";
            $data = [
                "report" => $object,
                "files" => $files,
                "ratings" => $ratings
            ];
        } else if ($data['type'] == self::OBJECT_MERCHANT) {
            $data = [
                "merchant" => $object,
                "files" => $files,
                "ratings" => $ratings,
                "availabilities" => $availabilities,
                "access" => false
            ];
        }
        if (array_key_exists('favorite_id', $data)) {
            if ($data['favorite_id']) {
                $favor = Favorite::where('user_id', $data['favorite_id'])->where("favorite_type", $data['type'])->where("object_id", $object->id)->first();
                if ($favor) {
                    $data['favorite'] = true;
                }
            }
        }
        return $data;
    }

    public function sendCartItem(User $user, array $data) {
        $validator = $this->validatorMerchantCartItem($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $target = User::find($data['customer_id']);
        $followers = [$target];
        if (count($followers) > 0) {
            $payload = [
                "name" => $data['item_name'],
                "description" => $data['item_description'],
                "price" => $data['item_price'],
                "tax" => $data['item_tax'],
                "shipping" => $data['item_shipping'],
                "quantity" => $data['item_quantity'],
                "merchant_id" => $data['object_id'],
                "merchant_name" => $data['merchant_name'],
            ];
            $data = [
                "trigger_id" => $data['object_id'],
                "message" => "",
                "subject" => "Visita tu correo para enterarte de nuestros menus de esta semana",
                "object" => "Merchant",
                "sign" => true,
                "payload" => $payload,
                "type" => 'merchant_item',
                "user_status" => "normal"
            ];
            $date = date_create();
            $date = date_format($date, "Y-m-d");
            $this->notifications->sendMassMessage($data, $followers, null, true, $date, false);
            foreach ($followers as $user) {
                Mail::to($target->email)->send(new Newsletter());
            }
        }
    }

    public function getObjectByHash($code, $type) {
        $data = Cache::remember($type . '_hash_' . $code, 100, function ()use ($type, $code) {
                    $target = "App\\Models\\" . $type;
                    $object = $target::where('hash', $code)->first();
                    if ($object) {
                        $files = FileM::where("type", $type)->where("trigger_id", $object->id)->get();
                        $ratings = Rating::where("type", $type)->where("object_id", $object->id)->orderBy('id', 'desc')->limit(20)->get();
                        if ($type == self::OBJECT_REPORT) {
                            if ($object->private == true && $type == "Report") {
                                $object->email == "";
                                $object->telephone == "";
                            }
                            $data = [
                                "report" => $object,
                                "files" => $files,
                                "ratings" => $ratings
                            ];
                        } else if ($type == self::OBJECT_MERCHANT) {
                            $data = [
                                "merchant" => $object,
                                "files" => $files,
                                "ratings" => $ratings
                            ];
                        }
                        return $data;
                    } else {
                        return ['status' => "error", "message" => $type . ' not found'];
                    }
                });
        return $data;
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
        $data['type'] = "Merchant";
        $merchants = $this->getNearbyObjects($data);

        $data['type'] = "Report";
        $reports = $this->getNearbyObjects($data);
        return array("merchants" => $merchants['data'], "reports" => $reports['data']);
    }
    public function textSearchMerchant($data) {
        if (!isset($data['q'])) {
            $data['q'] = "";
        }
        if (isset($data['lat'])) {
            $query = Merchant::search($data['q'])->whereIn('merchants.id', function($query) use($data) {
                        if (isset($data['lat'])) {
                            $point = 'POINT(' . $data['long'] . ' ' . $data['lat'] . ')';
                            $query->select('merchant_id')
                                    ->from('coverage_polygons')
                                    ->whereRaw('ST_Contains( geometry , ST_GeomFromText(?))', [$point]);
                        }
                        //$query->select('merchants.id');
                    })->where('private', false)->whereIn('status', ['active', 'online', 'busy']);
        } else {
            $query = Merchant::search($data['q'])->where('private', false)->whereIn('status', ['active', 'online', 'busy']);
        }

        if (isset($data['categories'])) {
            $categories = explode(",", $data['categories']);
            $query->leftJoin('categorizables', 'merchants.id', '=', 'categorizables.categorizable_id')
                    ->whereIn('categorizables.category_id', $categories)
                    ->where('categorizables.categorizable_type', "App\\Models\\Merchant");
        }
        $countQuery = $query;
        $pageRes = $this->paginateQueryFromArray($query, $data);
        $query = $pageRes['query'];
        $merchants = $query->get();
        $total = $countQuery->count();
        $results['category'] = null;
        $results['data'] = $merchants;
        $results['page'] = $pageRes['page'];
        $results['last_page'] = ceil($total / $pageRes['per_page']);
        $results['per_page'] = $pageRes['per_page'];
        $results['total'] = $total;
        return $results;
    }
    
    public function textSearchReport($data) {
        if (!isset($data['q'])) {
            $data['q'] = "";
        }
        $query = Report::search($data['q']);

        if (isset($data['categories'])) {
            $categories = explode(",", $data['categories']);
            $query->leftJoin('categorizables', 'reports.id', '=', 'categorizables.categorizable_id')
                    ->whereIn('categorizables.category_id', $categories)
                    ->where('categorizables.categorizable_type', "App\\Models\\Report");
        }
        $countQuery = $query;
        $pageRes = $this->paginateQueryFromArray($query, $data);
        $query = $pageRes['query'];
        $reports = $query->get();
        $total = $countQuery->count();
        $results['category'] = null;
        $results['data'] = $reports;
        $results['page'] = $pageRes['page'];
        $results['last_page'] = ceil($total / $pageRes['per_page']);
        $results['per_page'] = $pageRes['per_page'];
        $results['total'] = $total;
        return $results;
    }
    
    public function paginateQueryFromArray($query,$data){
        $page = null;
        if (array_key_exists("page", $data)) {
            if ($data['page']) {
                $page = $data['page'];
            }
        }
        $per_page = null;
        if (array_key_exists("per_page", $data)) {
            if ($data['per_page']) {
                $per_page = $data['per_page'];
            }
        }

        if ($per_page) {
            $query->take($per_page);
        } else {
            $per_page = self::OBJECT_PAGESIZE;
            $query->take(self::OBJECT_PAGESIZE);
        }
        if ($page) {
            $skip = null;
            if ($per_page) {
                $skip = ($page - 1 ) * ($per_page);
            } else {
                $skip = ($page - 1 ) * (self::OBJECT_PAGESIZE);
            }
            $query->skip($skip);
        } else {
            $page = 1;
        }
        return ["query"=>$query,"page"=>$page,"per_page"=>$per_page];
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function buildCoverageQuery(array $data) {
        $lat = $data['lat'];
        $long = $data['long'];
        $category = false;
        $per_page = 25;
        $page = 1;
        if (array_key_exists("category", $data)) {
            if ($data["category"]) {
                $category = true;
            }
        }
        if (array_key_exists("page", $data)) {
            if ($data["page"]) {
                $page = $data["page"];
            }
        }
        if (array_key_exists("per_page", $data)) {
            if ($data["per_page"]) {
                $per_page = $data["per_page"];
            }
        }
        $offset = ($page-1)*$per_page;

        $thedata = [
            'point' => 'POINT(' . $long . ' ' . $lat . ')',
            'limit' => $per_page
        ];
        $additionalQuery = '';
        if ($category) {
            $thedata["category"] = $data["category"];
            $additionalQuery = ' AND id in (SELECT categorizable_id FROM categorizables where category_id in (:category) and categorizable_type = "App\\\Models\\\Merchant") ';
        }
//        DB::enableQueryLog();
        $merchants = DB::select(" "
                        . "SELECT id, name, description, icon, lat,`long`, type, telephone,slug,email, address,rating,rating_count,unit_cost,attributes FROM merchants "
                ." where private = 0 AND status in ('online','active','busy') AND "
                . " id in (SELECT merchant_id FROM coverage_polygons WHERE ST_Contains(`geometry`, ST_GeomFromText(:point)) ) "
                        . $additionalQuery
                        . " LIMIT ".$offset.", :limit", $thedata);
        unset($thedata['limit']);
        $merchantsCount = DB::select(" "
                        . "SELECT COUNT(merchants.id) as total FROM merchants "
                ." where private = 0 AND status in ('online','active','busy') AND "
                . " id in (SELECT merchant_id FROM coverage_polygons WHERE ST_Contains(`geometry`, ST_GeomFromText(:point)) ) "
                        . $additionalQuery, $thedata);
        return array("data" => $merchants,"page"=>$page,"per_page"=>$per_page,"total"=>$merchantsCount[0]->total,"last_page"=>ceil($merchantsCount[0]->total/$per_page));
    }
    
    public function getRelation(array $parentIds, $object, $idColumn) {
//DB::enableQueryLog();
        $results = DB::table($object)
                ->whereIn($idColumn, $parentIds)
                ->orderBy($idColumn)
                ->get();
        // dd(DB::getQueryLog());
        return $results->toArray();
    }

    public function organizeRelation(array $objects, array $relations, $resource, $idColumn) {
        foreach ($relations as $rel) {
            //$rel = json_decode(json_encode((array) $rel), true);

            foreach ($objects as &$item) {
                //dd($item);
                if ($item->id == $rel->$idColumn) {
                    if (property_exists($item, $resource)) {
                        array_push($item->$resource, $rel);
                    } else {
                        $item->$resource = [$rel];
                    }
                }
            }
        }
        return $objects;
    }

    public function getNearbyObjects(array $data) {
        $radius = 6300;
        $R = 6371;
        $lat = $data['lat'];
        $long = $data['long'];
        $type = "";
        $joins = "";
        $joinsWhere = "";
        $category = false;
        if (array_key_exists("category", $data)) {
            if ($data["category"]) {
                $category = true;
            }
        }
        $additionalFields = "";
        if ($data['type'] == "Merchant") {
            $type = "merchants";
            $additionalFields = " type, telephone, address,email,rating,rating_count,unit_cost,attributes, ";
            if ($category) {
                $joins = " join categorizables cm on r.id = cm.categorizable_id ";
                $joinsWhere = " AND cm.category_id in (:category) AND cm.categorizable_type = 'App\\\Models\\\Merchant' ";
            }
        } else if ($data['type'] == "Report") {
            $type = "reports";
            $additionalFields = " type, telephone, address, report_time,attributes,rating,rating_count, ";
            if ($category) {
                $joins = " join categorizables cr on r.id = cr.categorizable_id ";
                $joinsWhere = " AND cr.category_id in (:category) AND cr.categorizable_type = 'App\\\Models\\\Report' ";
            }
        }
        $page = 1;
        if (array_key_exists("page", $data)) {
            if ($data["page"]) {
                $page = $data["page"];
            }
        }
        $per_page = 25;
        if (array_key_exists("per_page", $data)) {
            if ($data["per_page"]) {
                $per_page = $data["per_page"];
            }
        }
        $offset = ($page-1)*$per_page;

        $maxLat = $lat + rad2deg($radius / $R);
        $minLat = $lat - rad2deg($radius / $R);
        $maxLon = $long + rad2deg(asin($radius / $R) / cos(deg2rad($lat)));
        $minLon = $long - rad2deg(asin($radius / $R) / cos(deg2rad($lat)));
        $thedata = [
            'lat' => $lat,
            'lat2' => $lat,
            'long' => $long,
//            'latinf' => $minLat,
//            'latsup' => $maxLat,
//            'longinf' => $minLon,
//            'longsup' => $maxLon,
//            'radius' => $radius,
            'limit' => $per_page
        ];
        if ($category) {
            $thedata["category"] = $data["category"];
        }
//        DB::enableQueryLog();
        
        $reports = DB::select(" "
                        . "SELECT r.id, name, description, icon, lat,`long`,slug, " . $additionalFields . " 
			( 6371 * acos( cos( radians( :lat ) ) *
		         cos( radians( r.lat ) ) * cos( radians(  `long` ) - radians( :long ) ) +
		   sin( radians( :lat2 ) ) * sin( radians(  r.lat  ) ) ) ) AS Distance  
                   FROM
                    " . $type . " r " . $joins . "
                    WHERE
                        status in ('active','online','busy')
                            AND r.private = 0

                            " . $joinsWhere . "

                    order by distance asc "
                 . " LIMIT ".$offset.", :limit", $thedata);
//        dd(DB::getQueryLog());
        unset($thedata['limit']);
        $reportsCount = DB::select(" "
                        . "SELECT count(r.id) as total,
			( 6371 * acos( cos( radians( :lat ) ) *
		         cos( radians( r.lat ) ) * cos( radians(  `long` ) - radians( :long ) ) +
		   sin( radians( :lat2 ) ) * sin( radians(  r.lat  ) ) ) ) AS Distance  
                   FROM
                    " . $type . " r " . $joins . "
                    WHERE
                        status in ('active','online','busy')
                            AND r.private = 0
                            " . $joinsWhere . "", $thedata);
//        dd($reports);
        unset($thedata['limit']);
        return array("data" => $reports,"page"=>$page,"per_page"=>$per_page,"total"=>$reportsCount[0]->total,"last_page"=>ceil($reportsCount[0]->total/$per_page));
    }

    function buildIncludes($merchants, $data) {
        if (array_key_exists('includes', $data)) {
            if ($data['includes']) {
                $relatedObjects = explode(',', $data['includes']);
                $merchantIds = array_column($merchants, 'id');
                $object = "";
                $idColumn = "";
                foreach ($relatedObjects as $item) {
                    if ($item == 'availabilities') {
                        $object = "bookable_availabilities";
                        $idColumn = "bookable_id";
                    }
                    $relationships = $this->getRelation($merchantIds, $object, $idColumn);
                    $merchants = $this->organizeRelation($merchants, $relationships, $item, $idColumn);
                }
            }
        }
        return $merchants;
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
        if ($group->isPublicActive()) {
            $member = $group->checkMemberType($user);
            if ($member) {
                if ($member->level == self::CONTACT_BLOCKED) {
                    return null;
                }
                if ($member->is_admin) {
                    $data['status'] = "active";
                } else {
                    $data['status'] = "pending";
                }
            } else {
                return null;
            }
        } else if (!$group->is_public) {
            $member = $group->checkMemberType($user);
            if ($member) {
                if ($member->level == self::CONTACT_BLOCKED) {
                    return null;
                }
                $data['status'] = "active";
            } else {
                return null;
            }
        } else {
            return null;
        }
        return $data;
    }

    public function notifyGroup(Group $group, User $user, array $data, $type, $object) {
        if ($group) {
            if ($group->isPublicActive()) {
                if ($data['status'] == "pending") {
                    $followers = $group->getAllAdminMembersNonUserBlockedButActive($user);
                } elseif ($data['status'] == "active") {
                    $followers = $group->getAllMembersNonUserBlockedButActive($user);
                } else {
                    return null;
                }
            } else if (!$group->is_public) {
                $followers = $group->getAllMembersNonUserBlockedButActive($user);
            } else {
                return null;
            }
            $payload = array("class" => $type, "type" => $object->type, "object_type" => $object->type, "object_name" => $object->name, "object_id" => $object->id, "first_name" => $user->firstName, "last_name" => $user->lastName, "group_name" => $group->name, "group_id" => $group->id);
            if ($type == "Report" && $data['status'] == "active") {
                $type = self::OBJECT_REPORT_GROUP;
            } else if ($type == "Report" && $data['status'] == "pending") {
                $type = self::OBJECT_REPORT_ACCESS_GROUP;
            } else if ($type == "Merchant" && $data['status'] == "active") {
                $type = self::OBJECT_MERCHANT_GROUP;
            } else if ($type == "Merchant" && $data['status'] == "pending") {
                $type = self::OBJECT_MERCHANT_ACCESS_GROUP;
            }
            $data = [
                "trigger_id" => $user->id,
                "message" => "",
                "subject" => "",
                "payload" => $payload,
                "object" => self::OBJECT_GROUP,
                "sign" => true,
                "type" => $type,
                "user_status" => $user->getUserNotifStatus()
            ];
            $this->notifications->sendMassMessage($data, $followers, $user, true, null);
        }
    }

    public function notifyGroups(User $user, array $data, $type, $object, $groups) {
        foreach ($groups as $item) {
            $group = Group::find($item);
            if ($group) {
                $this->notifyGroup($group, $user, $data, $type, $object);
            }
        }
    }

    public function saveToGroups(User $user, array $data, $type, $object) {
        if ($data['groups']) {
            $groups = $data['groups'];
            if ($type == self::OBJECT_MERCHANT) {
                DB::table(self::GROUP_MERCHANT_TABLE)->where("merchant_id", $object->id)
                        ->whereNotIn("group_id", $groups)->delete();
            } else if ($type == self::OBJECT_REPORT) {
                DB::table(self::GROUP_REPORT_TABLE)->where("report_id", $object->id)
                        ->whereNotIn("group_id", $groups)->delete();
            }

            $result = [];
            $finalgroups = [];

            foreach ($groups as $item) {
                $group = Group::find($item);
                if ($group) {
                    $total = 0;
                    if ($type == self::OBJECT_MERCHANT) {
                        $total = $group->merchants()->where('merchants.id', $object->id)->count();
                    } else if ($type == self::OBJECT_REPORT) {
                        $total = $group->reports()->where('reports.id', $object->id)->count();
                    }
                    if ($total > 0) {
                        continue;
                    }
                    $statcheck = [];
                    $statcheck = $this->checkGroupStatus($user, $group, $data);
                    if ($statcheck) {
                        $prospect['group_id'] = $item;
                        $prospect['status'] = $statcheck['status'];
                        $prospect['created_at'] = date("Y-m-d h:i:sa");
                        $prospect['updated_at'] = date("Y-m-d h:i:sa");
                        if ($type == self::OBJECT_MERCHANT) {
                            $prospect['merchant_id'] = $object->id;
                        } else if ($type == self::OBJECT_REPORT) {
                            $prospect['report_id'] = $object->id;
                        }
                        array_push($result, $prospect);
                        array_push($finalgroups, $item);
                    }
                }
            }
            if (count($result) > 0) {
                if ($type == self::OBJECT_MERCHANT) {
                    DB::table(self::GROUP_MERCHANT_TABLE)->insert($result);
                } else if ($type == self::OBJECT_REPORT) {
                    DB::table(self::GROUP_REPORT_TABLE)->insert($result);
                }
                $this->notifyGroups($user, $data, $type, $object, $finalgroups);
            }
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
            if (array_key_exists('anonymous', $data)) {
                if ($data['anonymous']) {
                    if ($data['anonymous'] == true) {
                        $data['email'] = "";
                        $data['telephone'] = "";
                    }
                }
            }
        }
        $attributes = [];
        $fields = ["service", "experience", "specialty"];
        foreach ($fields as $value) {
            $services = [];
            for ($x = 1; $x <= 11; $x++) {
                if (array_key_exists($value . $x, $data)) {
                    if ($data[$value . $x]) {
                        $container = ["name" => $data[$value . $x]];
                        array_push($services, $container);
                    }
                    unset($data[$value . $x]);
                }
            }
            if (count($services) > 0) {
                $attributes[$value] = $services;
            }
        }

        $attributes['booking_requires_auth'] = false;
        $attributes['years_experience'] = 1;
        $attributes['max_per_hour'] = 1;
        $fields2 = ['booking_requires_auth', 'years_experience', 'max_per_hour', 'virtual_meeting', 'virtual_provider',
            'type_pet', 'google_calendar', 'store_active', 'booking_active', 'has_store'];
        foreach ($fields2 as $value) {
            if (array_key_exists($value, $data)) {
                if ($data[$value]) {
                    $attributes[$value] = $data[$value];
                }
                unset($data[$value]);
            }
        }

        $data['attributes'] = $attributes;
        if ($type == self::OBJECT_MERCHANT) {
            if (!array_key_exists("unit", $data)) {
                $data['unit'] = "hour";
            }
            if (!array_key_exists("unit_cost", $data)) {
                $data["unit_cost"] = 0;
            }
//        if (!array_key_exists("status", $data)) {
//            $data['status'] = "pending";
//        }
            if (!array_key_exists("base_cost", $data)) {
                $data['base_cost'] = 0;
            }
            if (!array_key_exists("unit_cost", $data)) {
                $data['unit_cost'] = 0;
            }
            dispatch(new CreateMerchant($user));
            $data['currency'] = "COP";
        }
        if (array_key_exists('id', $data)) {
            if ($data['id'] && $data['id'] > 0) {
                foreach ($data as $key => $value) {
                    if (!$value) {
                        unset($data[$key]);
                    }
                }
                $object = $this->updateObject($user, $data, $type);
                if ($object) {
                    return ['status' => 'success', "message" => "Result saved ", "object" => $object];
                } else {
                    return ['status' => 'error', "message" => "access denied"];
                }
            }
        }
        if (array_key_exists('private', $data)) {
            if (!$data["private"]) {
                $data["private"] = 0;
            }
        } else {
            $data["private"] = 0;
        }


        $data['status'] = 'active';
        if ($type == self::OBJECT_MERCHANT) {
            $validator = $this->validatorMerchant($data);
            if ($validator->fails()) {
                return array("status" => "error", "message" => $validator->getMessageBag());
            }
        } else if ($type == self::OBJECT_REPORT) {
            $validator = $this->validatorReport($data);
            if ($validator->fails()) {
                return array("status" => "error", "message" => $validator->getMessageBag());
            }
        }
        $object = $this->createObject($user, $data, $type);
        return ['status' => 'success', "message" => "Result saved: " . $object->name, "object" => $object];
    }

    /**
     * returns all current shared locations for the user
     *
     * @return Location
     */
    public function updateObject(User $user, array $data, $type) {
        $object = "App\\Models\\" . $type;

        $checker = $object::find($data['id']);
        if ($checker) {
            if ($checker->checkAdminAccess($user->id)) {
                Cache::forget($type . '_' . $data['id']);
                if (array_key_exists("groups", $data)) {
                    //$this->saveToGroups($user, $data, $type,$object);
                    dispatch(new SaveGroupsObject($user, $data, $type, $object));
                }
                $data['updated_at'] = date_add(date_create(), date_interval_create_from_date_string(date('Z') . " seconds"));
                $object::where('id', $data['id'])->whereIn('status', ['active', 'online', 'inactive', 'pending'])->update($data);
                $result = $this->getObjectUser($user, $data['id'], $type);
                if ($result) {
                    return $result;
                }
            }
        }
        return null;
    }

    /**
     * returns all current shared locations for the user
     *
     * @return Location
     */
    public function updateStatus(User $user, array $data) {
        $object = "App\\Models\\" . $data['type'];
        $checker = $object::find($data['object_id']);
        if ($checker) {
            if ($checker->status == "active" || $checker->status == "online" || $checker->status == "busy") {
                if ($checker->checkAdminAccess($user->id)) {
                    Cache::forget('Merchant_' . $data['object_id']);
                    $checker->status = $data['status'];
                    $checker->save();
                    return ['status' => 'success', "message" => "status updated"];
                }
            }
        }
        return ['status' => 'error', "message" => "failed"];
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
            if (array_key_exists("group_id", $data)) {
                if ($data['group_id']) {

                    $group = Group::find($data['group_id']);
                    if ($group) {
                        $targetStatus = $data['status'];
                        $data = $this->checkGroupStatus($user, $group, $data);
                        if ($data['status'] == 'active') {
                            $attributes['status'] = $targetStatus;
                            $result->groups()->updateExistingPivot($group->id, $attributes);
                            return ['status' => 'success', "message" => "status updated"];
                        } else {
                            return ['status' => 'error', "message" => "you must own the report or be an admin in its hive"];
                        }
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
    public function createObject(User $user, array $data, $type) {
        $object = "App\\Models\\" . $type;

        $result = $object::create($data);
        if ($type == "Merchant") {
            $user->merchants()->save($result);
        } else if ($type == "Report") {
            $user->reports()->save($result);
        }

        if (array_key_exists("groups", $data)) {
            //$this->saveToGroups($user, $data, $type, $result);
            dispatch(new SaveGroupsObject($user, $data, $type, $result));
        }

        return $result;
    }

    /**
     * returns all current shared locations for the user
     *
     * @return Location
     */
    public function createUserObject(array $data) {
        $user = User::create([
                    "firstName" => $data['firstName'],
                    "lastName" => $data['lastName'],
                    "name" => $data['firstName'] . ' ' . $data['lastName'],
                    "email" => $data['lastName'],
                    "cellphone" => $data['lastName'],
                    "lastName" => $data['lastName'],
                    "area_code" => $data['area_code'],
                    "docType" => $data['docType'],
                    "docNum" => $data['docNum'],
                    'password' => bcrypt($data['password']),
        ]);

        $object = "App\\Models\\" . $data['type'];
        $result = $object::create($data);
        $user->merchants()->save($result);
        return $result;
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
                    'name' => 'required|max:255'
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
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorMerchantCartItem(array $data) {
        return Validator::make($data, [
                    "item_name" => 'required',
                    "item_description" => 'required',
                    "item_price" => 'required',
                    "item_tax" => 'required',
                    "item_quantity" => 'required',
                    "object_id" => 'required',
                    "customer_id" => 'required',
                    "merchant_name" => 'required',
        ]);
    }

    /**
     * Get the failed login message.
     *
     * @return string
     */
    protected function getFailedEditMapObjectMessage() {
        return 'There was a problem editing the merchant';
    }

}
