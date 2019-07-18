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

class Contacts {

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

    public function getContact($contactId) {
        return User::find($contactId);
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
}
