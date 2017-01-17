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
    public function validator(array $data) {
        return Validator::make($data, [
                    'firstName' => 'required|max:255',
                    'lastName' => 'required|max:255',
                    'email' => 'required|email|max:255',
                    'cellphone' => 'required|max:255',
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
                $finduser = User::where('cellphone', '=', $data['cellphone'])->first();
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
        if (array_key_exists("firstName", $data) && array_key_exists("lastName", $data)) {
            $user->name = $data['firstName'] + " " + $data['lastName'];
        }
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
        $medical = $user->medical;
        if (array_key_exists("gender", $data)) {
            $gender = $data['gender'];
        } else {
            $gender = "";
        }
        if (array_key_exists("birth", $data)) {
            $birth = $data['birth'];
        } else {
            $birth = "";
        }
        if (array_key_exists("weight", $data)) {
            $weight = $data['weight'];
        } else {
            $weight = "";
        }
        if (array_key_exists("blood_type", $data)) {
            $blood_type = $data['blood_type'];
        } else {
            $blood_type = "";
        }
        if (array_key_exists("antigent", $data)) {
            $antigent = $data['antigent'];
        } else {
            $antigent = "";
        }
        if (array_key_exists("alergies", $data)) {
            $alergies = $data['alergies'];
        } else {
            $alergies = "";
        }
        if (array_key_exists("other", $data)) {
            $other = $data['other'];
        } else {
            $other = "";
        }
        if (array_key_exists("surgical_history", $data)) {
            $surgical_history = $data['surgical_history'];
        } else {
            $surgical_history = "";
        }
        if (array_key_exists("obstetric_history", $data)) {
            $obstetric_history = $data['obstetric_history'];
        } else {
            $obstetric_history = "";
        }
        if (array_key_exists("medications", $data)) {
            $medications = $data['medications'];
        } else {
            $medications = "";
        }
        if (array_key_exists("alergies", $data)) {
            $alergies = $data['alergies'];
        } else {
            $alergies = "";
        }
        if (array_key_exists("immunization_history", $data)) {
            $immunization_history = $data['immunization_history'];
        } else {
            $immunization_history = "";
        }
        if (array_key_exists("medical_encounters", $data)) {
            $medical_encounters = $data['medical_encounters'];
        } else {
            $medical_encounters = "";
        }
        if (array_key_exists("prescriptions", $data)) {
            $prescriptions = $data['prescriptions'];
        } else {
            $prescriptions = "";
        }
        if (array_key_exists("emergency_name", $data)) {
            $emergency_name = $data['emergency_name'];
        } else {
            $emergency_name = "";
        }
        if (array_key_exists("relationship", $data)) {
            $relationship = $data['relationship'];
        } else {
            $relationship = "";
        }
        if (array_key_exists("number", $data)) {
            $number = $data['number'];
        } else {
            $number = "";
        }
        if (array_key_exists("eps", $data)) {
            $eps = $data['eps'];
        } else {
            $eps = "";
        }
        if ($medical) {

            $medical->gender = $gender;
            $medical->weight = $weight;
            $medical->blood_type = $blood_type;
            $medical->antigent = $antigent;
            $medical->birth = $birth;
            $medical->surgical_history = $surgical_history;
            $medical->obstetric_history = $obstetric_history;
            $medical->medications = $medications;
            $medical->alergies = $alergies;
            $medical->immunization_history = $immunization_history;
            $medical->medical_encounters = $medical_encounters;
            $medical->prescriptions = $prescriptions;
            $medical->emergency_name = $emergency_name;
            $medical->relationship = $relationship;
            $medical->number = $number;
            $medical->other = $other;
            $medical->eps = $eps;
            $medical->save();
        } else {
            $medical = Medical::create([
                        "gender" => $gender,
                        "weight" => $weight,
                        "blood_type" => $blood_type,
                        "antigent" => $antigent,
                        "alergies" => $alergies,
                        "birth" => $birth,
                        "user_id" => $user->id,
                        'surgical_history' => $surgical_history,
                        'obstetric_history' => $obstetric_history,
                        'medications' => $medications,
                        'alergies' => $alergies,
                        'immunization_history' => $immunization_history,
                        'medical_encounters' => $medical_encounters,
                        'prescriptions' => $prescriptions,
                        'emergency_name' => $emergency_name,
                        'relationship' => $relationship,
                        'number' => $number,
                        'eps' => $eps,
                        'other' => $other
            ]);
        }

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
        $medical = DB::select("SELECT DATEDIFF(curdate(),birth) / 365.25 as age, m.*, u.firstName, u.lastName "
                        . " from  medicals m join users u on u.id= m.user_id where u.id = ?", [$user_id]);
        if (sizeof($medical) > 0) {
            return $medical[0];
        }
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
        return array("status" => "success", "message" => "Contacts imported","last_id" => DB::getPdo()->lastInsertId());
    }

    /**
     * Import Contacts.
     *
     * @param  User, array  $data
     * 
     */
    public function importContactsId(User $user, array $data) {
        $imports = array();
        $notifications = array();
        foreach ($data as $value) {
            $contact = User::find($value);
            if ($contact) {
                $exists = DB::select("SELECT user_id FROM contacts WHERE user_id = $user->id and contact_id = $contact->id ; ");
                if ($exists) {
                    
                } else {
                    array_push($imports, array('user_id' => $user->id, 'contact_id' => $value, 'level' => 'normal',"created_at"=> date("Y-m-d H:i:s"),"updated_at"=>date("Y-m-d H:i:s")));
                    $notification = [
                        "user_id" => $value,
                        "trigger_id" => $user->id,
                        "message" => "Has sido agregado como contacto por: " . $user->name,
                        "payload" => "",
                        "type" => "new_contact",
                        "subject" => "Nuevo contacto " . $user->name,
                        "user_status" => $this->editAlerts->getUserNotifStatus($user)
                    ];
                    $this->editAlerts->sendNotification($notification,true);
                }
            }
        }
        DB::table('contacts')->insert($imports);
        $lastId = DB::getPdo()->lastInsertId()+(count($imports)-1);
        return array("status" => "success", "message" => "contacts imported","last_id" => $lastId);
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
//                $file = '/home/hoovert/access.log';
//                // Open the file to get existing content
//                $current = file_get_contents($file);
//                //$daarray = json_decode(json_encode($data));
//                // Append a new person to the file
//
//                $current .= json_encode($value);
//                $current .= PHP_EOL;
//                $current .= PHP_EOL;
//                $current .= PHP_EOL;
//                $current .= PHP_EOL;
                //file_put_contents($file, $current);
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
            $address = Address::find(intval($data['address_id']));
            if ($address) {
                if ($address->user_id == $user->id) {
                    $address->firstName = $data['firstName'];
                    $address->lastName = $data['lastName'];
                    $address->address = $data['address'];
                    $address->type = $data['type'];
                    if (array_key_exists("lat", $data)) {
                        $address->lat = $data['lat'];
                    }
                    if (array_key_exists("long", $data)) {
                        $address->long = $data['long'];
                    }
                    $address->city_id = $data['city_id'];
                    $address->region_id = $data['region_id'];
                    $address->postal = $data['postal'];
                    $address->phone = $data['phone'];
                    $address->country_id = $data['country_id'];
                    $address->save();
                    return array("status" => "success", "message" => "address updated", "address" => $address);
                }
                return array("status" => "error", "message" => "address does not belong to user");
            }
            return array("status" => "error", "message" => "address does not exist");
        } else {
            if (array_key_exists("lat", $data) && array_key_exists("long", $data)) {
                $address = new Address([
                    'firstName' => $data['firstName'],
                    'lastName' => $data['lastName'],
                    'address' => $data['address'],
                    'type' => $data['type'],
                    'postal' => $data['postal'],
                    'phone' => $data['phone'],
                    'city_id' => $data['city_id'],
                    'region_id' => $data['region_id'],
                    'country_id' => $data['country_id'],
                    'lat' => $data['lat'],
                    'long' => $data['long'],
                ]);
            } else {
                $address = new Address([
                    'firstName' => $data['firstName'],
                    'lastName' => $data['lastName'],
                    'address' => $data['address'],
                    'type' => $data['type'],
                    'postal' => $data['postal'],
                    'phone' => $data['phone'],
                    'city_id' => $data['city_id'],
                    'region_id' => $data['region_id'],
                    'country_id' => $data['country_id']
                ]);
            }

            $user->addresses()->save($address);
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
            $id = DB::table('contacts')->insert(
                    array('user_id' => $user->id, 'contact_id' => $contactId, 'level' => 'normal',"created_at"=> date("Y-m-d H:i:s"),"updated_at"=>date("Y-m-d H:i:s"))
            );
            $contact->server_id = DB::getPdo()->lastInsertId();
            $notification = [
                "user_id" => $contactId,
                "trigger_id" => $user->id,
                "message" => "Has sido agregado como contacto por: " . $user->name,
                "payload" => "",
                "type" => "new_contact",
                "subject" => "Nuevo contacto " . $user->name,
                "user_status" => $this->editAlerts->getUserNotifStatus($user)
            ];
            $this->editAlerts->sendNotification($notification,false);
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

    public function deleteGroup(User $user, $groupId) {
        $group = Group::where('id', '=', $groupId)->first();
        if ($group) {
            if ($group->admin_id == $user->id) {
                //$group->delete();
                DB::table('group_user')
                        ->where('group_user.user_id', '=', $user->id)
                        ->where('group_user.group_id', '=', $groupId)
                        ->delete();
                return array("status" => "success", "message" => "user removed from group");
            } else {
                DB::table('group_user')
                        ->where('group_user.user_id', '=', $user->id)
                        ->where('group_user.group_id', '=', $groupId)
                        ->delete();
                return array("status" => "success", "message" => "user removed from group");
            }
        }
        return array("status" => "error", "message" => "group not found");
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

}
