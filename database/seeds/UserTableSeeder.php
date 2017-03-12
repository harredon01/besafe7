<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Jobs\AddContact;
use App\Models\Group;

use App\Services\EditUserData;
use App\Services\MerchantImport;
use App\Services\EditGroup;
use App\Services\EditLocation;
use App\Services\EditAlerts;

class UserTableSeeder extends Seeder {

    /**
     * The edit profile implementation.
     *
     */
    protected $editUserData;

    /**
     * The edit group implementation.
     *
     */
    protected $editGroup;

    /**
     * The edit location implementation.
     *
     */
    protected $editLocation;

    /**
     * The edit alerts implementation.
     *
     */
    protected $editAlerts;
    
    /**
     * The edit alerts implementation.
     *
     */
    protected $merchantImport;


    public function __construct(EditUserData $editUserData, EditGroup $editGroup, EditLocation $editLocation, EditAlerts $editAlerts, MerchantImport $merchantImport) {
        $this->editGroup = $editGroup;
        $this->editLocation = $editLocation;
        $this->editUserData = $editUserData;
        $this->editAlerts = $editAlerts;
        $this->merchantImport = $merchantImport;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

        /*$data = array(
            'firstName' => "Hoovert",
            'name' => "Hoovert Arredondo",
            'lastName' => "Arredondo",
            'docType' => "Cedula",
            'cellphone' => "3105507245",
            'area_code' => "57",
            'docNum' => "1020716535",
            'email' => "harredon007@hotmail.com",
            'password' => bcrypt("123456"),
        );
        
        $user = User::create($data);

        $data = array(
            'firstName' => "Rodrigo",
            'lastName' => "Arias",
            'type' => 'billing',
            'phone' => "3105507245",
            'postal' => "57",
            'address' => "Carrera 1 este # 74 - 14",
            'city_id' => "524",
            'region_id' => "11",
            'country_id' => "1",
            'lat' => 4.654419,
            'long' => -74.049405,
        );
        $this->editUserData->createOrUpdateAddress($user, $data);
        $salt2 = str_random(40);
        $data = array(
            'firstName' => "Hoovert2",
            'name' => "Hoovert2 Arredondo2",
            'lastName' => "Arredondo2",
            'docType' => "Cedula",
            'cellphone' => "3105507245",
            'area_code' => "57",
            'docNum' => "1020716536",
            'email' => "harredon01@gmail.com",
            'password' => bcrypt("123456"),
        );
        $user = User::create($data);
        $data = array(
            'firstName' => "Hoov",
            'lastName' => "Arre",
            'type' => 'billing',
            'phone' => "3105507245",
            'postal' => "57",
            'address' => "Calle 73 # o - 39 este Apto 502",
            'lat' => 4.652879,
            'long' => -74.050242,
            'city_id' => "524",
            'region_id' => "11",
            'country_id' => "1",
        );
        $this->editUserData->createOrUpdateAddress($user, $data);
        $data = array(
            'firstName' => "Camila",
            'name' => "Camila Sandoval",
            'lastName' => "Sandoval",
            'docType' => "Cedula",
            'docNum' => "1020766426",
            'cellphone' => "3103418432",
            'area_code' => "57",
            'email' => "cami_sandoval_@hotmail.com",
            'password' => bcrypt("123456"),
        );
        $user = User::create($data);
        $data = array(
            'firstName' => "Seb",
            'lastName' => "Jara",
            'type' => 'billing',
            'address' => "Cra 5 # 72-14",
            'phone' => "3105507245",
            'postal' => "57",
            'lat' => 4.654462,
            'long' => -74.053375,
            'city_id' => "524",
            'region_id' => "11",
            'country_id' => "1",
        );
        $this->editUserData->createOrUpdateAddress($user, $data);
        $data = array(
            'firstName' => "Juan Camilo",
            'name' => "Juan Camilo Rondon",
            'lastName' => "Rondon",
            'docType' => "Cedula",
            'cellphone' => "3157833052",
            'area_code' => "57",
            'docNum' => "80874422",
            'email' => "rondoncardenas@gmail.com",
            'password' => bcrypt("123456"),
        );
        $user = User::create($data);
        $data = array(
            'firstName' => "Cami",
            'lastName' => "Sand",
            'type' => 'billing',
            'address' => "Cra 7 # 64-44",
            'phone' => "3105507245",
            'postal' => "57",
            'lat' => 4.648751,
            'long' => -74.059104,
            'city_id' => "524",
            'region_id' => "11",
            'country_id' => "1",
        );
        $this->editUserData->createOrUpdateAddress($user, $data);*/
        $this->merchantImport->importUsers("users.xlsx");
        $this->command->info('users seeded!');
        $this->merchantImport->importAddresses("address.xlsx");
        $this->command->info('Addresses seeded!');
        $this->merchantImport->importContacts("contacts.xlsx");
        $this->command->info('Contacts seeded!');
        $this->updateMedical();
        $this->updateCodes();
//        $this->createMessages();
//        $this->addFollowersUsers();
//        $this->postLocation();
//        $this->postLocation();
        //$this->createContacts();
        /* $this->addFollowersUsers();
          $this->createMessages();

          $this->createMessages();
          $this->postLocation(); */
    }

    public function createContacts() {
        $users = User::all();
        $data = array(
            'name' => "Cami"
        );
        $usert = $users[0];
        $result = $this->editGroup->saveOrCreateGroup($data, $usert);
        $group = $result;

        foreach ($users as $user) {
            foreach ($users as $user2) {
                if ($user->id == $user2->id) {
                    
                } else {
                    dispatch(new AddContact($user, $user2->id));
                }
            }
            if ($usert->id == $user->id) {
                
            } else {
                $this->editGroup->joinGroupByCode($user, $group->id);
            }
        }

        $data = array(
            'name' => "Liza"
        );

        $result = $this->editGroup->saveOrCreateGroup($data, $usert);
        $group = $result;
        foreach ($users as $user) {
            if ($usert->id == $user->id) {
                
            } else {
                $data = array(
                    'contact_id' => $user->id,
                    'group_id' => $group->id,
                );
                $this->editGroup->inviteUser($usert, $data);
            }
        }
    }

    public function UpdateMedical() {
        $users = User::all();
        $data = array(
            'gender' => "m",
            'birth' => "1986-07-10",
            'weight' => "70kg",
            'blood_type' => "o",
            'antigent' => "+",
            'surgical_history' => "surgical_history",
            'obstetric_history' => "obstetric_history",
            'medications' => "medications",
            'alergies' => "peanutbutter",
            'immunization_history' => "immunization_history",
            'medical_encounters' => "medical_encounters",
            'prescriptions' => "prescriptions",
            'emergency_name' => "emergency_name",
            'relationship' => "relationship",
            'number' => "3105507245",
            'eps' => "eps",
            'other' => "anemic",
        );
        foreach ($users as $user) {
            $this->editUserData->UpdateMedical($user, $data);
        }
    }

    public function UpdateCodes() {
        $users = User::all();
        $data = array(
            'green' => 1234,
            'red' => 4321,
        );
        foreach ($users as $user) {
            $this->editUserData->updateCodes($user, $data);
        }
    }

    public function addFollowersGroup() {
        $users = User::all();
        $group = Group::first();
        $lat = 4.641824;
        $long = -74.063759;
        foreach ($users as $user) {
            $lat = $lat + 0.00045;
            $long = $long + 0.00045;
            $data = array(
                'type' => "group",
                'follower' => $group->id,
            );
            $this->editAlerts->addFollower($data, $user);
        }
    }

    public function postLocation() {
        $users = User::all();
        $lat = 4.641824;
        $long = -74.063759;
        foreach ($users as $user) {
            for ($i = 0; $i < 10; $i++) {
                $lat = $lat + 0.00045;
                $long = $long + 0.00045;
                $data = array(
                    'lat' => $lat,
                    'long' => $long,
                    'status' => "regular",
                    'location' => array(
                        'coords' => array(
                            'latitude' => $lat,
                            'longitude' => $long,
                        )
                    ),
                );
                $this->editLocation->postLocation($data, $user);
            }
        }
    }

    public function addFollowersUsers() {
        $users = User::all();
        foreach ($users as $user) {
            foreach ($users as $user2) {
                if ($user->id == $user2->id) {
                    
                } else {
                    $data = array(
                        'type' => "user",
                        'follower' => $user2->id,
                    );

                    $this->editAlerts->addFollower($data, $user);
                }
            }
        }
    }

    public function createMessages() {
        $users = User::all();
        $users2 = User::all();
        $group = Group::first();
        foreach ($users as $user) {
            foreach ($users2 as $user2) {

                if ($user->id == $user2->id) {
                    
                } else {
                    $data = array(
                        'messageable_id' => $user2->id,
                        'user_id' => $user->id,
                        'priority' => "normal",
                        'type' => EditAlerts::USER_MESSAGE_TYPE,
                        'message' => 'this is a test message from ' . $user->name . " to " . $user2->name,
                    );

                    $this->editAlerts->postMessage($user, $data);
                }
            }
            /* $data = array(
              'user_id' => $user->id,
              'type' => EditAlerts::GROUP_MESSAGE_TYPE,
              'message' => 'this is a test message from ' . $user->name . " to the group " . $group->name,
              'messageable_id' => $group->id,
              'priority' => "normal"
              );
              $this->editAlerts->postMessage($user, $data); */
        }
    }

}
