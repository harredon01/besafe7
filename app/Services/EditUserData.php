<?php

namespace App\Services;

use App\Models\User;
use App\Models\Address;
use App\Models\Push;
use App\Models\Condition;
use Illuminate\Support\Facades\Mail;
use App\Mail\Register;
use DB;
use Validator;
use App\Models\Region;
use App\Jobs\SendMessage;
use App\Jobs\PostRegistration;
use App\Models\City;
use App\Models\Country;

class EditUserData {

    const CONTACT_BLOCKED = 'contact_blocked';
    const CONTACT_DELETED = 'contact_deleted';
    const NEW_CONTACT = 'new_contact';
    const OBJECT_USER = 'User';
    const RED_MESSAGE_TYPE = 'emergency';
    const RED_MESSAGE_END = 'emergency_end';
    const RED_MESSAGE_MEDICAL_TYPE = 'medical_emergency';

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorRegister(array $data) {
        return Validator::make($data, [
                    'firstName' => 'required|max:255',
                    'lastName' => 'required|max:255',
                    'cellphone' => 'required|max:255',
                    'area_code' => 'required|max:255',
                    'email' => 'required|email|max:255|unique:users',
                    'password' => 'required|min:6|confirmed',
        ]);
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorCredits(array $data) {
        return Validator::make($data, [
                    'email' => 'required|max:255',
                    'credits' => 'required|max:255'
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    public function create(array $data) {
        $pass = $data['password'];
        $data['password'] = bcrypt($data['password']);
        $data['name'] = $data['firstName'] . " " . $data['lastName'];
        $data['emailNotifications'] = 1;
        unset($data['password_confirmation']);
        $user = User::create($data);
        $http = new \GuzzleHttp\Client;
        $response = $http->post(env('APP_URL') . '/oauth/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => env('APP_CLIENT_ID'),
                'client_secret' => env('APP_CLIENT_SECRET'),
                'username' => $data['email'],
                'password' => $pass,
                'scope' => '*',
            ],
        ]);
        $json = json_decode((string) $response->getBody(), true);
        dispatch(new PostRegistration($user));
        //$this->getUserCode($user);
        return ['status' => 'success', 'access_token' => $json['access_token']];
    }

    public function postRegistration(User $user) {
        Mail::to($user->email)->send(new Register($this->generateWelcomeCoupon($user)));
        $admin = User::find(77);
        $message = "Hola " . $user->firstName . " somos lonchis. Recuerda que tienes un cupon en tu correo de bienevenida. Estamos aca para servirte y ayudarte a que disfrutes nuestro servicio. Tienes alguna duda o hay algo que podamos hacer por ti";
        $package = [
            "type" => "user_message",
            "name" => "Servicio al cliente",
            "message" => $message,
            "from_id" => $admin->id,
            "to_id" => $user->id,
            "status" => "unread",
            "target_id" => $user->id,
            "created_at" => date("Y-m-d H:i:s")
        ];
        SendMessage::dispatch($admin, $package)->delay(now()->addMinutes(2));
    }

    /**
     * returns all current shared locations for the user
     *
     * @return Location
     */
    public function getUserCode(User $user) {
        $hashExists = true;
        while ($hashExists) {
            $hash = str_random(20);
            $users = DB::select("SELECT * from users where code = ? ", [$hash]);
            if ($users) {
                $hashExists = true;
            } else {
                $hashExists = false;
                $user->code = $hash;
                $user->save();
            }
        }
        return $hash;
    }

    /**
     * returns all current shared locations for the user
     *
     * @return Location
     */
    private function generateWelcomeCoupon(User $user) {
        $attributes = [
            "user_id" => $user->id
        ];
        $coupon = trim($user->firstName) . "&Lonchis" . $user->id;
        $coupon = str_replace(' ', '', $coupon);
        $condition = Condition::create([
                    "name" => "Descuento bienvenida",
                    "type" => "coupon",
                    "target" => "total",
                    "value" => "-8000",
                    "coupon" => $coupon,
                    "isReusable" => 0,
                    "used" => 0,
                    "attributes" => json_encode($attributes),
                    "status" => "active",
                    "order" => 0
        ]);
        return $coupon;
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorPassword(array $data) {
        return Validator::make($data, [
                    'password' => 'required|confirmed|min:6',
        ]);
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorAddress(array $data) {
        return Validator::make($data, [
                    'name' => 'required|max:255',
                    'address' => 'required|max:255',
                    'city_id' => 'required|max:255',
                    'region_id' => 'required|max:255',
                    'country_id' => 'required|max:255',
        ]);
    }

    /**
     * Update profile data.
     *
     * @param  User, array  $data
     * 
     */
    public function update(User $user, array $data) {
        if ($data['email']) {
            if ($user->email != $data['email']) {
                $finduser = User::where('email', '=', $data['email'])->first();
                if ($finduser && $finduser->id != $user->id) {
                    return array("status" => "error", "message" => "Email already registered");
                }
            }
        }
        if ($data['docNum']) {
            if ($user->docNum != $data['docNum']) {
                $finduser = User::where('docNum', '=', $data['docNum'])->first();
                if ($finduser && $finduser->id != $user->id) {
                    return array("status" => "error", "message" => "Id # already registered");
                }
            }
        }
        if ($data['cellphone']) {
            if ($user->cellphone != $data['cellphone']) {
                $finduser = User::where('cellphone', '=', $data['cellphone'])->first();
                if ($finduser && $finduser->id != $user->id) {
                    return array("status" => "error", "message" => "Celphone already registered");
                }
            }
        }
        $user->fill($data);
        $user->name = $user->firstName . " " . $user->lastName;
        $user->save();
        return array("status" => "success", "message" => "User Profile Updated", "user" => $user);
    }

    /**
     * Update profile data.
     *
     * @param  User, array  $data
     * 
     */
    public function cleanServer(User $user) {
        $followers = DB::select("SELECT * FROM userables WHERE userable_id = $user->id and userable_type='Location' limit 1;  ");
        if (sizeof($followers) < 1) {
            $locations = DB::select("SELECT * FROM locations WHERE user_id = $user->id limit 1;  ");
            if (sizeof($locations) > 0) {
                $user->is_tracking = 0;
                $user->hash = "";
                $user->trip = 0;
                $user->save();
            }
        }
        return array("status" => "success", "message" => "User Codes Updated");
    }

    /**
     * Update profile data.
     *
     * @param  User, array  $data
     * 
     */
    public function checkUserCredits(User $user, array $data) {
        $validator = $this->validatorCredits($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $candidate = User::where("email", $data['email'])->get();
        if (count($candidate) > 0) {
            $candidate = $candidate[0];
            if ($candidate) {
                $push = $candidate->push()->where("platform", $data['platform'])->first();
                if ($push) {
                    return ["result" => true, "user_id" => $candidate->id, "status" => "success", "credits" => $push->credits];
                }
                return ["result" => true, "user_id" => $candidate->id, "status" => "success", "credits" => 0];
            }
        }

        return ["result" => false, "status" => "info"];
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  User, array  $data
     * 
     */
    public function registerToken(User $user, array $data) {
        if (array_key_exists("platform", $data) && array_key_exists("token", $data)) {
            $result = $user->push()->where('platform', $data['platform'])->first();
            if ($result) {
                $result->object_id = $data['token'];
                $result->save();
            } else {
                Push::create([
                    "user_id" => $user->id,
                    "platform" => $data['platform'],
                    "object_id" => $data['token']
                ]);
                $user->pushNotifications = 1;
                $user->save();
            }
            return array("status" => "success", "message" => "User Gcm Token Registered");
        }
        return array("status" => "error", "message" => "Bad request");
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  User, array  $data
     * 
     */
    public function registerPhone(User $user, array $data) {
        if (array_key_exists("cellphone", $data) && array_key_exists("area_code", $data)) {
            $user->cellphone = $data['cellphone'];
            $user->area_code = $data['area_code'];
            $user->save();
            return array("status" => "success", "message" => "User phone registered");
        }
        return array("status" => "error", "message" => "Bad request");
    }

    public function createOrUpdateAddress(User $user, array $data) {
        if (array_key_exists("address_id", $data)) {
            if ($data["address_id"]) {
                $addressid = $data['address_id'];
                unset($data['address_id']);
                $city = City::find($data['city_id']);
                $data['city'] = $city->name;
                $address = Address::find($addressid);
                if ($address) {
                    if ($address->user_id == $user->id) {
                        $address->fill($data);
                        $address->save();
                    } else {
                        return array("status" => "error", "message" => "access_denied");
                    }
                    $region = Region::find($address->region_id);
                    $country = Country::find($address->country_id);
                    if ($city) {
                        $address->cityName = $city->name;
                    }
                    if ($region) {
                        $address->regionName = $region->name;
                    }
                    if ($country) {
                        $address->countryName = $country->name;
                    }
                    return array("status" => "success", "message" => "address updated", "address" => $address);
                }
                return array("status" => "error", "message" => "address not found");
            }
        }
        $city = City::find($data['city_id']);
        $data['city'] = $city->name;
        $address = new Address($data);
        $user->addresses()->save($address);
        $region = Region::find($address->region_id);
        $country = Country::find($address->country_id);
        if ($city) {
            $address->cityName = $city->name;
        }
        if ($region) {
            $address->regionName = $region->name;
        }
        if ($country) {
            $address->countryName = $country->name;
            $address->countryCode = $country->code;
        }
        return array("status" => "success", "message" => "address created", "address" => $address);
    }

    public function deleteAddress(User $user, $addressId) {
        $address = Address::find($addressId);
        if ($address) {
            if ($address->user_id == $user->id) {
                $address->delete();
                return array("status" => "success", "message" => "address deleted");
            }
            return array("status" => "error", "message" => "address does not belong to user");
        }
        return array("status" => "error", "message" => "address id does not exist");
    }

    public function setAsBillingAddress(User $user, $addressId) {
        $address = Address::find($addressId);
        if ($address) {
            if ($address->user_id == $user->id) {
                $address->type = 'billing';

                $address->save();
                return array("status" => "ok", "message" => "address set as billing address");
            }
            return array("status" => "error", "message" => "address does not belong to user");
        }
        return array("status" => "error", "message" => "address id does not exist");
    }

    public function getUserId($user_id) {
        $user = User::find($user_id);
        return $user;
    }

    public function getUserCel($celphone) {
        return User::where('cellphone', '=', $celphone)
                        ->first();
    }

    public function getAddresses(User $user, $type = null) {
        if ($type) {
            if ($type == "shipping") {
                $addresses = $user->addresses()->where("lat", ">", 0)->get();
            } else {
                $addresses = $user->addresses()->where("type", "like", "%" . $type . "%")->get();
            }
        } else {
            $addresses = $user->addresses;
        }

        foreach ($addresses as $address) {
            $region = Region::find($address->region_id);
            $country = Country::find($address->country_id);
            $city = City::find($address->city_id);
            $address->cityName = $city->name;
            $address->countryName = $country->name;
            $address->countryCode = $country->code;
            $address->regionName = $region->name;
        }
        return $addresses;
    }

    public function getAddress(User $user, $id) {
        $address = $user->addresses()->where('id', $id)->get();
        return $address;
    }

}
