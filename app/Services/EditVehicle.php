<?php

namespace App\Services;

use Validator;
use App\Models\User;
use App\Models\Vehicle;
use DB;

class EditVehicle {

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
    public function saveOrCreateVehicle(array $data, User $user) {
        $validator = $this->validatorVehicle($data);
        if ($validator->fails()) {
            return $validator->getMessageBag();
        }
        if (array_key_exists("vehicle_id", $data)) {
            $vehicle = Vehicle::find(intval($data['vehicle_id']));
            if ($vehicle) {

                $vehicle->axis = $data['axis'];
                $vehicle->category_id = $data['category_id'];
                $vehicle->plates = $data['plates'];
                $vehicle->vin_number = $data['vin_number'];
                $vehicle->make = $data['make'];
                $vehicle->model = $data['model'];
                $vehicle->year = $data['year'];
                $vehicle->color = $data['color'];
                $vehicle->image = $data['image'];
                $vehicle->full_length = $data['full_length'];
                $vehicle->horse_power = $data['horse_power'];
                $vehicle->description = $data['description'];
                $vehicle->cargo_width = $data['cargo_width'];
                $vehicle->cargo_length = $data['cargo_length'];
                $vehicle->cargo_height = $data['cargo_height'];
                $vehicle->cargo_weight = $data['cargo_weight'];
                $vehicle->save();

                return ['status' => 'success', "message" => 'Vehicle updated', "vehicle" => $vehicle];
            }
            return ['status' => 'error', "message" => 'Vehicle not found'];
        } else {
            $vehicle = Vehicle::create($data);
            $user->vehicles()->save($vehicle);
            return ['status' => 'success', "message" => 'Vehicle created', "vehicle" => $vehicle];
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getVehicle(User $user, $vehicleId) {
        $vehicle = Vehicle::find(intval($vehicleId));
        $vehicle->routes;
        return $vehicle;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getVehiclesUser(User $user) {
        $user->vehicles;
        return $user;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getVehicleRoutes(array $data) {
        $validator = $this->validatorRoutes($data);
        if ($validator->fails()) {
            return $validator->getMessageBag();
        }
        $skip = (intval($data['page']) - 1) * intval($data['per_page']);
        $routes = DB::table('routes')
                ->where('vehicle_id', $data['vehicle_id'])
                ->skip($skip)->take(intval($data['per_page']))
                ->orderBy($data['order_by'], $data['order_dir'])
                ->get();
        return $routes;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function deleteVehicle(User $user, $vehicleId) {
        $vehicle = Vehicle::find(intval($vehicleId));
        if ($vehicle) {
            if ($vehicle->user_id == $user->id) {
                $vehicle->delete();
                return ['status' => 'success', "message" => 'Vehicle Deleted'];
            }
            return ['status' => 'error', "message" => 'Vehicle Does not Belong to user'];
        }
        return ['status' => 'error', "message" => 'Vehicle Does not exist'];
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     *                 $vehicle->axis = $data['axis'];
     */
    public function validatorVehicle(array $data) {
        return Validator::make($data, [
                    'category_id' => 'required|integer',
                    'plates' => 'required|max:255',
                    'axis' => 'required|integer',
                    'year' => 'required|integer',
                    'vin_number' => 'required|max:255',
                    'make' => 'required|max:255',
                    'model' => 'required|max:255',
                    'color' => 'required|max:255',
                    'full_length' => 'required|numeric',
                    'horse_power' => 'required|string|max:255',
                    'description' => 'required|string|max:255',
                    'cargo_width' => 'required|numeric',
                    'cargo_length' => 'required|numeric',
                    'cargo_height' => 'required|numeric',
                    'cargo_weight' => 'required|numeric',
        ]);
    }

    /**
     * Get the failed login message.
     *
     * @return string
     */
    protected function getFailedEditVehicleMessage() {
        return 'There was a problem editing your vehicle';
    }

}
