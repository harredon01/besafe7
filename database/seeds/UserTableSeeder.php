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
use App\Services\Security;
use App\Services\ShareObject;

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
    protected $shareObject;

    /**
     * The edit alerts implementation.
     *
     */
    protected $merchantImport;

    /**
     * The edit alerts implementation.
     *
     */
    protected $security;

    public function __construct(EditUserData $editUserData, EditGroup $editGroup, EditLocation $editLocation, EditAlerts $editAlerts, ShareObject $shareObject, MerchantImport $merchantImport, Security $security) {
        $this->editGroup = $editGroup;
        $this->editLocation = $editLocation;
        $this->editUserData = $editUserData;
        $this->editAlerts = $editAlerts;
        $this->shareObject = $shareObject;
        $this->merchantImport = $merchantImport;
        $this->security = $security;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        DB::table('oauth_clients')->insert(
                ['id' => 2,
                    'name' => 'Lonchis Password Grant Client',
                    'secret' => 'pYZ6L7KzZoDEoN0kCf048M24RZvOZ7GeXKZ9Q5su',
                    'provider' => 'users',
                    'redirect' => 'http://localhost',
                    'personal_access_client' => 0,
                    'password_client' => 1,
                    'revoked' => 0,
                    'created_at' => '2020-09-15 12:57:53',
                    'updated_at' => '2020-09-15 12:57:53']
        );
        DB::table('payment_methods')->insert([
            ['id' => 1,
                'name' => 'Credit',
                'created_at' => '2020-09-15 12:57:53',
                'updated_at' => '2020-09-15 12:57:53'],
            ['id' => 2,
                'name' => 'Debit',
                'created_at' => '2020-09-15 12:57:53',
                'updated_at' => '2020-09-15 12:57:53'],
            ['id' => 3,
                'name' => 'Cash Options',
                'created_at' => '2020-09-15 12:57:53',
                'updated_at' => '2020-09-15 12:57:53'],
            ['id' => 4,
                'name' => 'Cash Delivery',
                'created_at' => '2020-09-15 12:57:53',
                'updated_at' => '2020-09-15 12:57:53'],
            ['id' => 5,
                'name' => 'Bank',
                'created_at' => '2020-09-15 12:57:53',
                'updated_at' => '2020-09-15 12:57:53'],
            ['id' => 6,
                'name' => 'Coupon',
                'created_at' => '2020-09-15 12:57:53',
                'updated_at' => '2020-09-15 12:57:53'],
                ]
        );

        $this->merchantImport->importTranslationsExcel("translations.xlsx");
        $this->command->info('Translations seeded');
        $this->merchantImport->importUsers("users.xlsx");
        $this->command->info('users seeded!');
        $this->merchantImport->importAddresses("addresses.xlsx");
        $this->command->info('Addresses seeded!');
//        $this->merchantImport->importContacts("contacts.xlsx");
//        $this->command->info('Contacts seeded!');
        $this->updateMedical();
        DB::table('push')->insert([
            ['id' => 1,
                'user_id' => 2,
                'push' => "1",
                'platform' => "Zoom",
                "object_id" => "1",
                'credits' => 0,
                'created_at' => '2020-09-15 12:57:53',
                'updated_at' => '2020-09-15 12:57:53'],
            ['id' => 2,
                'user_id' => 2,
                'push' => "1",
                'platform' => "MiPaquete",
                "object_id" => "1",
                'credits' => 0,
                'created_at' => '2020-09-15 12:57:53',
                'updated_at' => '2020-09-15 12:57:53'],
            ['id' => 3,
                'user_id' => 2,
                'push' => "1",
                'platform' => "MiPaqueteTest",
                "object_id" => "1",
                'credits' => 0,
                'created_at' => '2020-09-15 12:57:53',
                'updated_at' => '2020-09-15 12:57:53']
                ]
        );
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
                $this->editGroup->joinGroupById($user, $group->id);
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
            'antigen' => "+",
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
            $this->security->updateMedical($user, $data);
        }
    }

    public function UpdateCodes() {
        $users = User::all();
        $data = array(
            'green' => 1234,
            'red' => 4321,
        );
        foreach ($users as $user) {
            $this->security->updateCodes($user, $data);
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
            $this->shareObject->addFollower($data, $user);
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

                    $this->shareObject->addFollower($data, $user);
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
