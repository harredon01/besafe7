<?php

namespace App\Services;

use App\Models\User;
use App\Models\Group;
use App\Models\Medical;
use App\Models\Address;
use App\Models\Notification;
use DB;
use Validator;
use App\Models\Region;
use App\Models\City;
use App\Models\Country;
use App\Services\EditAlerts;

class EditUserData {

    const CONTACT_BLOCKED = 'contact_blocked';
    const NEW_CONTACT = 'new_contact';
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
    protected function validatorRegister(array $data) {
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
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data) {
        return User::create([
                    'firstName' => $data['firstName'],
                    'lastName' => $data['lastName'],
                    'name' => $data['firstName'] . " " . $data['lastName'],
                    'email' => $data['email'],
                    'cellphone' => $data['cellphone'],
                    'area_code' => $data['area_code'],
                    'password' => bcrypt($data['password']),
        ]);
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
                    'firstName' => 'required|max:255',
                    'lastName' => 'required|max:255',
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
        if (array_key_exists("email", $data)) {
            if ($user->email != $data['email']) {
                $finduser = User::where('email', '=', $data['email'])->first();
                if ($finduser && $finduser->id != $user->id) {
                    return array("status" => "error", "message" => "Email already registered");
                }
            }
        }
        if (array_key_exists("docNum", $data)) {
            if ($user->docNum != $data['docNum']) {
                $finduser = User::where('docNum', '=', $data['docNum'])->first();
                if ($finduser && $finduser->id != $user->id) {
                    return array("status" => "error", "message" => "Id # already registered");
                }
            }
        }
        if (array_key_exists("cellphone", $data)) {
            if ($user->cellphone != $data['cellphone']) {
                $finduser = User::where('cellphone', '=', $data['cellphone'])->where('area_code', '=', $data['area_code'])->first();
                if ($finduser && $finduser->id != $user->id) {
                    return array("status" => "error", "message" => "Celphone already registered");
                }
            }
        }
        if (array_key_exists("firstName", $data)) {
            $user->firstName = $data['firstName'];
        }
        if (array_key_exists("lastName", $data)) {
            $user->lastName = $data['lastName'];
        }
        if (array_key_exists("docType", $data)) {
            $user->docType = $data['docType'];
        }
        if (array_key_exists("docNum", $data)) {
            $user->docNum = $data['docNum'];
        }
        if (array_key_exists("email", $data)) {
            $user->email = $data['email'];
        }
        if (array_key_exists("cellphone", $data)) {
            $user->cellphone = $data['cellphone'];
        }
        if (array_key_exists("gender", $data)) {
            $user->gender = $data['gender'];
        }
        $user->name = $user->firstName . " " . $user->lastName;
        $user->save();
        return array("status" => "success", "message" => "User Profile Updated");
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
            $medical->age = intval(date('Y', time() - strtotime($medical->birth))) - 1970;
            $data["gender"] = $medical->gender;
            $data["weight"] = $medical->weight;
            $data["blood_type"] = $medical->blood_type;
            $data["antigent"] = $medical->antigent;
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
    public function getCodes(User $user) {
        $medical = DB::select("SELECT green,red from users where id = ?", [$user->id]);
        return $medical[0];
    }

    /**
     * Import Contacts.
     *
     * @param  User, array  $data
     * 
     */
    public function importContacts(User $user, array $data) {
        $imports = array();
        foreach ($data as $value) {
            $contact = User::where("cellphone", "=", $value);
            if ($contact) {
                $id = DB::table('contacts')->select('id')
                        ->where("user_id", "=", $user->id)
                        ->where("contact_id", "=", $contact->id);
                if ($id) {
                    
                } else {
                    array_push($imports, array('user_id' => $user->id, 'contact_id' => $contact->id), array('user_id' => $contact->id, 'contact_id' => $user->id));
                }
            }
        }
        DB::table('contacts')->insert($imports);
        return array("status" => "success", "message" => "Contacts imported", "last_id" => DB::getPdo()->lastInsertId());
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
        $payload = array('first_name' => $user->firstName, 'last_name' => $user->lastName);
        foreach ($data as $value) {
            $contact = User::find($value);
            if ($contact) {
                $exists = DB::select("SELECT user_id FROM contacts WHERE user_id = $user->id and contact_id = $contact->id ; ");
                if ($exists) {
                    
                } else {
                    array_push($inviteUsers, $contact);
                    array_push($imports, array('user_id' => $user->id, 'contact_id' => $value, 'level' => 'normal', "created_at" => date("Y-m-d H:i:s"), "updated_at" => date("Y-m-d H:i:s")));
                }
            }
        }
        $notification = [
            "trigger_id" => $user->id,
            "message" => "",
            "payload" => $payload,
            "type" => self::NEW_CONTACT,
            "user_status" => $this->editAlerts->getUserNotifStatus($user)
        ];
        $this->editAlerts->sendMassMessage($notification, $inviteUsers, $user, true);
        DB::table('contacts')->insert($imports);
        $lastId = DB::getPdo()->lastInsertId() + (count($imports) - 1);
        return array("status" => "success", "message" => "contacts imported", "last_id" => $lastId);
    }

    /**
     * Import Contacts.
     *
     * @param  User, array  $data
     * 
     */
    public function updateContactsLevel(User $user, array $data) {
        $importsget = array();
        $status = "";
        foreach ($data as $value) {
            $contact = User::find($value['contact_id']);
            if ($contact) {
                array_push($importsget, $value['contact_id']);
                $status = $value['level'];
            }
        }
        $users = DB::table('contacts')->whereIn('contact_id', $importsget)->where('user_id', $user->id)->update(array('level' => $status));
        ;
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
            $count = DB::table('contacts')->where('contact_id', $contactId)->where('user_id', $user->id)->count();
            if ($count == 0) {
                DB::table('contacts')->insert(array(
                    'level' => self::CONTACT_BLOCKED,
                    'user_id' => $user->id,
                    'contact_id' => $contactId,
                ));
            } else {
                $users = DB::table('contacts')->where('contact_id', $contactId)->where('user_id', $user->id)->update(array(
                    'level' => self::CONTACT_BLOCKED,
                    'user_id' => $user->id,
                    'contact_id' => $contactId,
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
        $query = "select id, firstName, lastName, email, cellphone, area_code from users where id not in (select contact_id from contacts where user_id = $user->id ) and id <> $user->id and ( ";
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
        $user->platform = $data['platform'];
        $user->token = $data['token'];
        $user->pushNotifications = 1;
        $user->save();
        return array("status" => "success", "message" => "User Gcm Token Registered");
    }

    public function createOrUpdateAddress(User $user, array $data) {
        if (array_key_exists("address_id", $data)) {
            $addressid = $data['address_id'];
            unset($data['address_id']);
            Address::where('user_id', $user->id)
                    ->where('id', $addressid)->update($data);
            $address = Address::find($addressid);
            if ($address) {
                $region = Region::find($address->region_id);
                $country = Country::find($address->country_id);
                $city = City::find($address->city_id);
                $address->cityName = $city->name;
                $address->countryName = $country->name;
                $address->regionName = $region->name;
                return array("status" => "success", "message" => "address updated", "address" => $address);
            }
        } else {
            $address = new Address($data);
            $user->addresses()->save($address);
            $region = Region::find($address->region_id);
            $country = Country::find($address->country_id);
            $city = City::find($address->city_id);
            $address->cityName = $city->name;
            $address->countryName = $country->name;
            $address->regionName = $region->name;
            if ($data['type'] == 'billing') {
                $this->setAsBillingAddress($user, $address->id);
            }
            return array("status" => "success", "message" => "address created", "address" => $address);
        }
    }

    public function deleteAddress(User $user, $addressId) {
        $address = Address::find($addressId);
        if ($address) {
            if ($address->user_id == $user->id) {
                $address->delete();
                return array("status" => "ok", "message" => "address deleted");
            }
            return array("status" => "error", "message" => "address does not belong to user");
        }
        return array("status" => "error", "message" => "address id does not exist");
    }

    public function setAsBillingAddress(User $user, $addressId) {
        $address = Address::find($addressId);
        if ($address) {
            if ($address->user_id == $user->id) {
                Address::where('user_id', $user->id)
                        ->where('type', 'billing')
                        ->where('id', '!=', $addressId)
                        ->update(['type' => 'shipping']);
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
                        array('user_id' => $user->id, 'contact_id' => $contactId, 'level' => 'normal', "created_at" => date("Y-m-d H:i:s"), "updated_at" => date("Y-m-d H:i:s"))
                );
                $payload = array('first_name' => $user->firstName, 'last_name' => $user->lastName);
                $notification = [
                    "trigger_id" => $user->id,
                    "message" => "Has sido agregado como contacto por: " . $user->name,
                    "payload" => $payload,
                    "type" => self::NEW_CONTACT,
                    "user_status" => $this->editAlerts->getUserNotifStatus($user)
                ];
                $recipients = array($contact);
                $this->editAlerts->sendMassMessage($notification, $recipients, $user, true);
                return $contact;
            }
            return $contact;
        }
        return array("status" => "error", "message" => "Contact not found");
    }

    public function deleteContact(User $user, $contactId) {
        $this->editAlerts->deleteUserNotifs($user, $contactId);
        return DB::table('contacts')
                        ->where('contacts.user_id', '=', $user->id)
                        ->where('contacts.contact_id', '=', $contactId)
                        ->delete();
    }

    public function getUserId($user_id) {
        $user = User::find($user_id);
        return $user;
    }

    public function getUserCel($celphone) {
        return User::where('cellphone', '=', $celphone)
                        ->first();
    }

    public function getAddresses(User $user) {
        $addresses = $user->addresses;
        foreach ($addresses as $address) {
            $region = Region::find($address->region_id);
            $country = Country::find($address->country_id);
            $city = City::find($address->city_id);
            $address->cityName = $city->name;
            $address->countryName = $country->name;
            $address->regionName = $region->name;
        }
        return $addresses;
    }

    public function getAddress(User $user, $id) {
        $address = $user->addresses()->where('id', $id)->get();
        return $address;
    }

}
