<?php

namespace App\Services;

use App\Models\User;
use App\Models\Medical;
use App\Models\Address;
use App\Models\Push;
use DB;
use Validator;
use App\Models\Region;
use App\Models\City;
use App\Models\Country;
use App\Services\EditAlerts;

class EditUserData {

    const CONTACT_BLOCKED = 'contact_blocked';
    const CONTACT_DELETED = 'contact_deleted';
    const NEW_CONTACT = 'new_contact';
    const OBJECT_USER = 'User';
    const RED_MESSAGE_TYPE = 'emergency';
    const RED_MESSAGE_END = 'emergency_end';
    const RED_MESSAGE_MEDICAL_TYPE = 'medical_emergency';

    /**
     * The Guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\Guard
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
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorRegister(array $data) {
        return Validator::make($data, [
                    'firstName' => 'required|max:255',
                    'lastName' => 'required|max:255',
                    'docNum' => 'required|max:255',
                    'docType' => 'required|max:255',
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
        $data['salt'] = str_random(40);
        $data['emailNotifications'] = 1;
        $user = User::create($data);
        $http = new \GuzzleHttp\Client;
        $response = $http->post(env('APP_URL').'/oauth/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => '1',
                'client_secret' => 'nuoLagU2jqmzWqN6zHMEo82vNhiFpbsBsqcs2DPt',
                'username' => $data['email'],
                'password' => $pass,
                'scope' => '*',
            ],
        ]);
        $json = json_decode((string) $response->getBody(), true);
        $payload = [];
        $followers = [$user];
        $data = [
            "trigger_id" => $user->id,
            "message" => "",
            "subject" => "",
            "object" => "Lonchis",
            "sign" => true,
            "payload" => $payload,
            "type" => "registration",
            "user_status" => "normal"
        ];
        $date = date("Y-m-d H:i:s");
        $this->editAlerts->sendMassMessage($data, $followers, null, false, $date, true);
        //$this->getUserCode($user);
        return ['status' => 'success', 'access_token' => $json['access_token']];
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
        if ($data['cellphone'] && $data['area_code']) {
            if ($user->cellphone != $data['cellphone']) {
                $finduser = User::where('cellphone', '=', $data['cellphone'])->where('area_code', '=', $data['area_code'])->first();
                if ($finduser && $finduser->id != $user->id) {
                    return array("status" => "error", "message" => "Celphone already registered");
                }
            }
        }
        if ($data['firstName']) {
            $user->firstName = $data['firstName'];
        }
        $user->optinMarketing = $data['optinMarketing'];
        if ($data['lastName']) {
            $user->lastName = $data['lastName'];
        }
        if ($data['docType']) {
            $user->docType = $data['docType'];
        }
        if ($data['docNum']) {
            $user->docNum = $data['docNum'];
        }
        if ($data['email']) {
            $user->email = $data['email'];
        }
        if ($data['cellphone']) {
            $user->cellphone = $data['cellphone'];
        }
        if ($data['area_code']) {
            $user->area_code = $data['area_code'];
        }
        if ($data['gender']) {
            $user->gender = $data['gender'];
        }
        $user->name = $user->firstName . " " . $user->lastName;
        $user->save();
        return array("status" => "success", "message" => "User Profile Updated","user" => $user);
    }

    public function getContact($contactId) {
        return User::find($contactId);
    }

    /**
     * Update profile data.
     *
     * @param  User, array  $data
     * 
     */
    public function updateMedical(User $user, array $data) {
        $data["user_id"] = $user->id;
        foreach ($data as $key => $value) {
            if (!$value) {
                unset($data[$key]);
            }
        }
        $medical = Medical::updateOrCreate(["user_id" => $user->id], $data);
        return array("status" => "success", "message" => "User Profile Updated");
    }

    /**
     * Update profile data.
     *
     * @param  User, array  $data
     * 
     */
    public function unlockMedical(User $user, array $data) {
        $user = User::find($data['user_id']);
        if ($user) {
            if ($user->green == $data['code']) {
                return $this->getMedical($user->id);
            }
            return array("status" => "error", "message" => "Incorrect Code");
        }
        return array("status" => "error", "message" => "User not found");
    }

    /**
     * Update profile data.
     *
     * @param  User, array  $data
     * 
     */
    public function notificationMedical(User $user, $contact_id) {
        $emergencyUser = User::find($contact_id);
        if ($emergencyUser) {
            $followers = DB::select("SELECT * FROM contacts WHERE contact_id= $user->id and level='" . self::RED_MESSAGE_TYPE . "' and user_id = ? limit 1;  ", [$emergencyUser->id]);
            if (sizeof($followers) == 1) {
                if ($emergencyUser->is_alerting == true && $emergencyUser->alert_type == "medical_emergency") {
                    return $this->getMedical($emergencyUser->id);
                }
                return array("status" => "error", "message" => "User not in medical emergency");
            }
        }

        return array("status" => "error", "message" => "User not found");
    }

    /**
     * Update profile data.
     *
     * @param  User, array  $data
     * 
     */
    public function getMedical($user_id) {
        $medical = Medical::where("user_id", $user_id)->first();
        if ($medical) {
            $data["age"] = date_diff(date_create($medical->birth), date_create('now'))->y;
            $data["gender"] = $medical->gender;
            $data["weight"] = $medical->weight;
            $data["blood_type"] = $medical->blood_type;
            $data["antigen"] = $medical->antigen;
            $data["birth"] = $medical->birth;
            $data["surgical_history"] = $medical->surgical_history;
            $data["obstetric_history"] = $medical->obstetric_history;
            $data["medications"] = $medical->medications;
            $data["alergies"] = $medical->alergies;
            $data["immunization_history"] = $medical->immunization_history;
            $data["medical_encounters"] = $medical->medical_encounters;
            $data["prescriptions"] = $medical->prescriptions;
            $data["emergency_name"] = $medical->emergency_name;
            $data["relationship"] = $medical->relationship;
            $data["number"] = $medical->number;
            $data["other"] = $medical->other;
            $data["eps"] = $medical->eps;
            return $data;
        }

//        $medical = DB::select("SELECT DATEDIFF(curdate(),birth) / 365.25 as age, m.*, u.firstName, u.lastName "
//                        . " from  medicals m join users u on u.id= m.user_id where u.id = ?", [$user_id]);
//        if (sizeof($medical) > 0) {
//            return $medical[0];
//        }
        return array("status" => "error", "message" => "Not found");
    }

    /**
     * Update profile data.
     *
     * @param  User, array  $data
     * 
     */
    public function updateCodes(User $user, array $data) {
        if (array_key_exists("green", $data)) {
            $user->green = $data['green'];
        }
        if (array_key_exists("red", $data)) {
            $user->red = $data['red'];
        }
        $user->save();
        return array("status" => "success", "message" => "User Codes Updated");
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
    public function getCodes(User $user) {
        $result = ["green" => $user->green, "red" => $user->red];
        return $result;
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
     * Import Contacts.
     *
     * @param  User, array  $data
     * 
     */
    public function importContactsId(User $user, array $data) {
        $imports = array();
        $inviteUsers = array();
        $updates = array();
        $payload = array('first_name' => $user->firstName, 'last_name' => $user->lastName);
        foreach ($data as $value) {
            $contact = User::find($value);
            if ($contact) {
                $exists = DB::select("SELECT * FROM contacts WHERE user_id = $user->id and contact_id = $contact->id LIMIT 1; ");
                if ($exists) {
                    $candidate = $exists[0];
                    if ($candidate->level == self::CONTACT_DELETED) {
                        $candidate->level = "normal";
                        array_push($updates, $candidate->id);
                    }
                } else {
                    array_push($inviteUsers, $contact);
                    array_push($imports, array('user_id' => $user->id, 'contact_id' => $value, 'level' => 'normal', "created_at" => date("Y-m-d H:i:s"), "last_significant" => date("Y-m-d H:i:s")));
                }
            }
        }
        if (count($updates) > 0) {
            DB::table('contacts')->whereIn('id', $updates)->update(["level" => "normal"]);
        }
        $lastId = 0;
        if (count($imports) > 0) {
            $notification = [
                "trigger_id" => $user->id,
                "message" => "",
                "payload" => $payload,
                "type" => self::NEW_CONTACT,
                "object" => self::OBJECT_USER,
                "sign" => true,
                "user_status" => $user->getUserNotifStatus()
            ];
            $this->editAlerts->sendMassMessage($notification, $inviteUsers, $user, true, null);
            DB::table('contacts')->insert($imports);
            $lastId = DB::getPdo()->lastInsertId() + (count($imports) - 1);
        }

        return array("status" => "success", "message" => "contacts imported", "last_id" => $lastId);
    }

    /**
     * Import Contacts.
     *
     * @param  User, array  $data
     * 
     */
    public function updateContactsLevel(User $user, array $data) {
        $is_emergency = false;
        if ($data["level"] == "emergency") {
            $is_emergency = true;
        }
        $users = DB::table('contacts')->whereIn('contact_id', $data["contacts"])->where('user_id', $user->id)->update(array('is_emergency' => $is_emergency, "last_significant" => date("Y-m-d H:i:s")));
        return array("status" => "success", "message" => "contacts imported", "result" => $users);
    }

    /**
     * Import Contacts.
     *
     * @param  User, array  $data
     * 
     */
    public function blockContact(User $user, $contactId) {
        $contact = User::find($contactId);
        if ($contact) {
            $this->editAlerts->deleteObjectNotifs($user, $contactId, "User");
            $count = DB::table('contacts')->where('contact_id', $contactId)->where('user_id', $user->id)->count();
            if ($count == 0) {
                DB::table('contacts')->insert(array(
                    'level' => self::CONTACT_BLOCKED,
                    'user_id' => $user->id,
                    'contact_id' => $contactId,
                    "created_at" => date("Y-m-d H:i:s"),
                    "last_significant" => date("Y-m-d H:i:s")
                ));
            } else {
                $users = DB::table('contacts')->where('contact_id', $contactId)->where('user_id', $user->id)->update(array(
                    'level' => self::CONTACT_BLOCKED,
                    'user_id' => $user->id,
                    'contact_id' => $contactId,
                    "last_significant" => date("Y-m-d H:i:s")
                ));
            }

            return array("status" => "success", "message" => "contact blocked");
        }
        return array("status" => "error", "message" => "contact not found");
    }

    /**
     * Import Contacts.
     *
     * @param  User, array  $data
     * 
     */
    public function unblockContact(User $user, $contactId) {
        $users = DB::table('contacts')->where('contact_id', $contactId)->where('user_id', $user->id)->update(array(
            'level' => 'normal',
            'user_id' => $user->id,
            'contact_id' => $contactId,
            'last_significant' => date("Y-m-d H:i:s")
        ));
        return array("status" => "success", "message" => "contact  unblocked");
    }

    /**
     * Import Contacts.
     *
     * @param  User, array  $data
     * 
     */
    public function checkContacts(User $user, array $data) {
        $query = "select id, firstName, lastName, email, cellphone, area_code from users where id not in (select contact_id from contacts where user_id = $user->id and level <>'contact_deleted' ) and id <> $user->id and ( ";
        $i = 0;
        $a = 0;
        $len = count($data);
        $total = array();
        foreach ($data as $value) {
            //return $value;
            //if (intval($value['area_code']) > 0 && intval($value['cellphone'])) {
            if ($i == $len - 1) {
                $query .= "( area_code = '" . $value['area_code'] . "' and cellphone = '" . $value['cellphone'] . "' ))";
                $contacts = DB::select($query);
                foreach ($contacts as $contact) {
                    array_push($total, $contact);
                }
            } else {
                if ($a < 20) {

                    $query .= "( area_code = '" . $value['area_code'] . "' and cellphone = '" . $value['cellphone'] . "' ) or ";
                } else {
                    $query .= "( area_code = '" . $value['area_code'] . "' and cellphone = '" . $value['cellphone'] . "' ))";
                    $contacts = DB::select($query);
                    foreach ($contacts as $contact) {
                        array_push($total, $contact);
                    }
                    $query = "select id, firstName, lastName, email, cellphone, area_code  from users where id not in (select contact_id from contacts where user_id = $user->id ) and id <> $user->id and ( ";
                    $a = 0;
                }
            }
            //}

            $a++;
            $i++;
        }
        /* $user->token = $query;
          $user->save();
          $contacts = DB::table('users')->select($query); */
        return array("status" => "success", "contacts" => $total);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  User, array  $data
     * 
     */
    public function updatePassword(User $user, $password) {

        $user->password = bcrypt($password['password']);
        $user->save();
        return array("status" => "success", "message" => "User Profile Updated");
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

    public function createOrUpdateAddress(User $user, array $data) {
        if (array_key_exists("address_id", $data)) {
            if ($data["address_id"]) {
                $addressid = $data['address_id'];
                unset($data['address_id']);
                $city = City::find($data['city_id']);
                $data['city'] = $city->name;
                Address::where('user_id', $user->id)
                        ->where('id', $addressid)->update($data);
                $address = Address::find($addressid);
                if ($address) {
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

    public function addContact(User $user, $contactId) {
        $contact = User::find($contactId);
        if ($contact) {
            $followers = DB::select("SELECT * FROM contacts WHERE user_id=? AND contact_id=? ", [$user->id, $contactId]);
            if (count($followers) == 0) {
                $id = DB::table('contacts')->insert(
                        array('user_id' => $user->id, 'contact_id' => $contactId, 'level' => 'normal', "created_at" => date("Y-m-d H:i:s"), "last_significant" => date("Y-m-d H:i:s"))
                );
                $payload = array('first_name' => $user->firstName, 'last_name' => $user->lastName);
                $notification = [
                    "trigger_id" => $user->id,
                    "message" => "Has sido agregado como contacto por: " . $user->name,
                    "payload" => $payload,
                    "type" => self::NEW_CONTACT,
                    "object" => self::OBJECT_USER,
                    "sign" => true,
                    "user_status" => $user->getUserNotifStatus()
                ];
                $recipients = array($contact);
                $this->editAlerts->sendMassMessage($notification, $recipients, $user, true, null);
                return $contact;
            }
            return $contact;
        }
        return array("status" => "error", "message" => "Contact not found");
    }

    public function deleteContact(User $user, $contactId) {
        $this->editAlerts->deleteObjectNotifs($user, $contactId, "User");
        return DB::table('contacts')
                        ->where('contacts.user_id', '=', $user->id)
                        ->where('contacts.contact_id', '=', $contactId)
                        ->update(array('level' => self::CONTACT_DELETED, "last_significant" => date("Y-m-d H:i:s")));
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
