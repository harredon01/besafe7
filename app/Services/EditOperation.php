<?php

namespace App\Services;

use Validator;
use App\Models\User;
use App\Models\Cargo;
use App\Models\Route;
use App\Models\Vehicle;
use App\Models\Stop;
use DB;

class EditOperation {

    const CARGO_TYPE = 'cargo';
    const ROUTE_TYPE = 'vehicle';

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
    public function saveOrCreateCargo(array $data, User $user) {
        $validator = $this->validatorCargo($data);
        if ($validator->fails()) {
            return $validator->getMessageBag();
        }
        if (array_key_exists("cargo_id", $data)) {
            $cargo = Cargo::find(intval($data['cargo_id']));
            if ($cargo && $cargo->user_id == $user->id) {
                $cargo->category_id = $data['category_id'];
                $cargo->description = $data['description'];
                $cargo->from_city_id = $data['from_city_id'];
                $cargo->from_city_name = $data['from_city_name'];
                $cargo->from_region_id = $data['from_region_id'];
                $cargo->from_region_name = $data['from_region_name'];
                $cargo->from_country_id = $data['from_country_id'];
                $cargo->from_country_name = $data['from_country_name'];
                $cargo->to_city_id = $data['to_city_id'];
                $cargo->to_city_name = $data['to_city_name'];
                $cargo->to_region_id = $data['to_region_id'];
                $cargo->to_region_name = $data['to_region_name'];
                $cargo->to_country_id = $data['to_country_id'];
                $cargo->to_country_name = $data['to_country_name'];
                $cargo->width = $data['width'];
                $cargo->offer = $data['offer'];
                $cargo->length = $data['length'];
                $cargo->height = $data['height'];
                $cargo->weight = $data['weight'];
                $cargo->status = $data['status'];
                $cargo->arrival = $data['arrival'];
                if(array_key_exists("image", $data)){
                    $cargo->image = $data['image'];
                }
                $cargo->save();
                return ['status' => 'success', "message" => 'Cargo updated', "cargo" => $cargo];
            }
            return ['status' => 'error', "message" => 'Cargo not found'];
        } else {
            $cargo = Cargo::create($data);
            $user->cargos()->save($cargo);
            return ['status' => 'success', "message" => 'Cargo created', "cargo" => $cargo];
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function saveOrCreateRoute(array $data, User $user) {
        $validator = $this->validatorRoute($data);
        if ($validator->fails()) {
            return $validator->getMessageBag();
        }
        if (array_key_exists("id", $data)) {
            $route = Route::find(intval($data['id']));
            if ($route) {
                if ($route->vehicle_id != intval($data['vehicle_id'])) {
                    $vehicle = Vehicle::find(intval($data['vehicle_id']));
                    if ($vehicle) {
                        if ($vehicle->user_id == $user->id) {
                            $route->vehicle_id = $vehicle->id;
                        } else {
                            return ['status' => 'error', "message" => 'Vehicle does not belong to user'];
                        }
                    } else {
                        return ['status' => 'error', "message" => 'Vehicle does not exist'];
                    }
                }
                $route->description = $data['description'];
                $route->width = $data['width'];
                $route->length = $data['length'];
                $route->height = $data['height'];
                $route->weight = $data['weight'];
                $route->status = $data['status'];
                $route->unit = $data['unit'];
                $route->unit_price = $data['unit_price'];
                $route->save();
                return ['status' => 'success', "message" => 'Route updated', "route" => $route];
            }
            return ['status' => 'error', "message" => 'Route not found'];
        } else {
            $route = Route::create($data);
            $user->routes()->save($route);
            return ['status' => 'success', "message" => 'Route created', "route" => $route];
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getCargosUser(User $user) {
        return $user->cargos()->orderBy('status', 'asc')->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getCargo($cargoId) {
        $cargo = Cargo::find(intval($cargoId));
        return $cargo;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getRoutesUser(User $user) {
        $user->cargos()->orderBy('status', 'asc');
        return $user;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getRoute($routeId) {
        $route = Route::find(intval($routeId));
        $route->stops;
        $route->vehicle;
        return $route;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function deleteRoute(User $user, $routeId) {
        $route = Route::find(intval($routeId));
        if ($route) {
            $vehicle = $route->vehicle;
            if ($vehicle->user_id == $user->id) {
                $route->delete();
                return ['status' => 'success', "message" => 'Route Deleted'];
            }
            return ['status' => 'error', "message" => 'Route Does not belong to user'];
        }
        return ['status' => 'error', "message" => 'Route Does not exist'];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function deleteCargo(User $user, $cargoId) {
        $cargo = Cargo::find(intval($cargoId));
        if ($cargo) {
            if ($cargo->user_id == $user->id) {
                $cargo->delete();
                return ['status' => 'success', "message" => 'Cargo Deleted'];
            }
            return ['status' => 'error', "message" => 'Cargo Does not Belong to user'];
        }
        return ['status' => 'error', "message" => 'Cargo Does not exist'];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function findOperations(array $data) {
        $validator = $this->validatorSearch($data);
        if ($validator->fails()) {
            return $validator->getMessageBag();
        }

        if ($data['search_type'] == self::CARGO_TYPE) {
            $sql = "select * from cargos where 1=1";
            if (array_key_exists("from_region_id", $data)) {
                $sql.=" and from_region_id " . $this->getDataType('from_region_id') . " " . $data['from_region_id'];
            }
            if (array_key_exists("from_city_id", $data)) {
                $sql.=" and from_city_id " . $this->getDataType('from_city_id') . " " . $data['from_city_id'];
            }
            if (array_key_exists("from_country_id", $data)) {
                $sql.=" and from_country_id " . $this->getDataType('from_country_id') . " " . $data['from_region_id'];
            }
            if (array_key_exists("to_region_id", $data)) {
                $sql.=" and to_region_id " . $this->getDataType('to_region_id') . " " . $data['to_region_id'];
            }
            if (array_key_exists("to_city_id", $data)) {
                $sql.=" and to_city_id " . $this->getDataType('to_city_id') . " " . $data['to_city_id'];
            }
            if (array_key_exists("to_country_id", $data)) {
                $sql.=" and to_country_id " . $this->getDataType('to_country_id') . " " . $data['to_country_id'];
            }
            if (array_key_exists("arrival", $data)) {
                $sql.=" and arrival " . $this->getDataType('arrival') . " " . $data['arrival'];
            }
            if (array_key_exists("weight", $data)) {
                $sql.=" and weight " . $this->getDataType('weight') . " " . $data['weight'];
            }
            if (array_key_exists("length", $data)) {
                $sql.=" and length " . $this->getDataType('length') . " " . $data['length'];
            }
        } elseif ($data['search_type'] == self::ROUTE_TYPE) {
            $sql = "SELECT routes.* FROM routes " .
                    "join stops AS stop1 on routes.id = stop1.route_id "
                    . "join stops AS stop2 on routes.id = stop2.route_id  ";
            if (array_key_exists("from_region_id", $data)) {
                $sql.=" and stop1.region_id " . $this->getDataType('from_region_id') . " " . $data['from_region_id'];
            }
            if (array_key_exists("from_city_id", $data)) {
                $sql.=" and stop1.city_id " . $this->getDataType('from_city_id') . " " . $data['from_city_id'];
            }
            if (array_key_exists("from_country_id", $data)) {
                $sql.=" and stop1.country_id " . $this->getDataType('from_region_id') . " " . $data['from_region_id'];
            }
            if (array_key_exists("to_region_id", $data)) {
                $sql.=" and stop2.region_id " . $this->getDataType('to_region_id') . " " . $data['to_region_id'];
            }
            if (array_key_exists("to_city_id", $data)) {
                $sql.=" and stop2.city_id " . $this->getDataType('to_city_id') . " " . $data['to_city_id'];
            }
            if (array_key_exists("to_country_id", $data)) {
                $sql.=" and stop2.country_id " . $this->getDataType('to_country_id') . " " . $data['to_country_id'];
            }
            if (array_key_exists("arrival", $data)) {
                $sql.=" and arrival " . $this->getDataType('arrival') . " " . $data['arrival'];
            }
            if (array_key_exists("weight", $data)) {
                $sql.=" and weight " . $this->getDataType('weight') . " " . $data['weight'];
            }
            if (array_key_exists("length", $data)) {
                $sql.=" and length " . $this->getDataType('length') . " " . $data['length'];
            }
        }


        $sql.=" and status = 1 ";
        if (array_key_exists("order_by", $data) & array_key_exists("order_dir", $data)) {
            $sql.=" order by " . $data['order_by'] . " " . $data['order_dir'];
        }
        $cargos = DB::select($sql);
        return $cargos;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function setCargoRoute(User $user, array $data) {
        $cargo = $user->cargos()->where('id', $data["cargo_id"])->get();
        if ($cargo) {
            $route = Route::find(intval($data["route_id"]));
            if ($route) {
                $route->cargos()->save($cargo);
                return array("status" => "success", "message" => "Cargo assigned to route");
            }
            return array("status" => "error", "message" => "Route does not exist");
        }
        return array("status" => "error", "message" => "User does not own cargo");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function setRouteStop(User $user, array $data) {
        $validator = $this->validatorStop($data);
        if ($validator->fails()) {
            return $validator->getMessageBag();
        }
        $route = Route::find(intval($data["route_id"]));
        if ($route) {
            if ($route->user_id == $user->id) {
                Stop::create($data);
                return array("status" => "success", "message" => "stop assigned to route");
            }
            return array("status" => "error", "message" => "Route does not belong to user");
        }
        return array("status" => "error", "message" => "Route does not exist");
    }

    public function getDataType($field) {
        $data = array(
            "weight" => ">",
            "height" => ">",
            "width" => ">",
            "length" => ">",
            "departure" => ">",
            "arrival" => ">",
            "from_city_id" => '=',
            'from_region_id' => '=',
            'from_country_id' => '=',
            'to_region_id' => '=',
            'to_country_id' => '=',
            'to_city_id' => '=',
        );
        return $data[$field];
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     * 
     */
    public function validatorCargo(array $data) {
        return Validator::make($data, [
                    'category_id' => 'required|integer',
                    'from_city_id' => 'required|integer',
                    'from_city_name' => 'required|string|max:255',
                    'from_region_id' => 'required|integer',
                    'from_region_name' => 'required|string|max:255',
                    'from_country_id' => 'required|integer',
                    'from_country_name' => 'required|string|max:255',
                    'to_city_id' => 'required|integer',
                    'to_city_name' => 'required|string|max:255',
                    'to_region_id' => 'required|integer',
                    'to_region_name' => 'required|string|max:255',
                    'to_country_id' => 'required|integer',
                    'to_country_name' => 'required|string|max:255',
                    'width' => 'required|numeric',
                    'offer' => 'required|numeric',
                    'length' => 'required|numeric',
                    'height' => 'required|numeric',
                    'weight' => 'required|numeric',
                    'status' => 'required|numeric',
                    'status' => 'required|numeric',
                    'description' => 'required|string|max:255',
        ]);
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     * 
     */
    public function validatorRoute(array $data) {
        return Validator::make($data, [
                    'vehicle_id' => 'required|integer',
                    'unit_price' => 'required|numeric',
                    'unit' => 'required|string|max:255',
                    'description' => 'required|string|max:255',
                    'weight' => 'required|numeric',
                    'width' => 'required|numeric',
                    'status' => 'required|integer',
                    'length' => 'required|numeric',
                    'height' => 'required|numeric',
        ]);
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     * 
     */
    public function validatorStop(array $data) {
        return Validator::make($data, [
                    'stop_order' => 'required|integer',
                    'route_id' => 'required|integer',
                    'city_id' => 'required|integer',
                    'region_id' => 'required|integer',
                    'country_id' => 'required|integer',
                    'arrival' => 'required|date',
                    'city_name' => 'required|string|max:255',
                    'region_name' => 'required|string|max:255',
                    'country_name' => 'required|string|max:255',
        ]);
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorSearch(array $data) {
        $rules = [];

        if (array_key_exists("from_region_id", $data)) {
            $insert = array("from_region_id" => 'required|integer');
            $rules[] = $insert;
        }
        if (array_key_exists("from_city_id", $data)) {
            $insert = array("from_city_id" => 'required|integer');
            $rules[] = $insert;
        }
        if (array_key_exists("from_country_id", $data)) {
            $insert = array("from_country_id" => 'required|integer');
            $rules[] = $insert;
        }
        if (array_key_exists("to_region_id", $data)) {
            $insert = array("to_region_id" => 'required|integer');
            $rules[] = $insert;
        }
        if (array_key_exists("to_city_id", $data)) {
            $insert = array("to_city_id" => 'required|integer');
            $rules[] = $insert;
        }
        if (array_key_exists("to_country_id", $data)) {
            $insert = array("to_country_id" => 'required|integer');
            $rules[] = $insert;
        }
        if (array_key_exists("category_id", $data)) {
            $insert = array("category_id" => 'required|integer');
            $rules[] = $insert;
        }
        if (array_key_exists("arrival", $data)) {
            $insert = array("category_id" => 'required|date');
            $rules[] = $insert;
        }
        if (array_key_exists("weight", $data)) {
            $insert = array("weight" => 'required|numeric');
            $rules[] = $insert;
        }
        if (array_key_exists("length", $data)) {
            $insert = array("length" => 'required|numeric');
            $rules[] = $insert;
        }
        if (array_key_exists("order_by", $data)) {
            $insert = array("order_by" => 'required|string|max:255');
            $rules[] = $insert;
            $insert = array("order_dir" => 'required|string|max:255');
            $rules[] = $insert;
        }
        $insert = array("search_type" => 'required|string|max:255');
        $rules[] = $insert;
        return Validator::make($data, $rules);
    }

    /**
     * Get the failed login message.
     *
     * @return string
     */
    protected function getFailedEditGroupMessage() {
        return 'There was a problem editing your group';
    }

}
