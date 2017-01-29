<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use App\Models\Region;
use App\Models\Country;
use App\Models\City;
use View;

trait EditProfileUsers {

    /**
     * The Guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $auth;

    /**
     * The edit profile implementation.
     *
     */
    protected $editUserData;

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function getEditProfile() {
        $user = $this->auth->user();
        return view('user.editProfile')->with('user', $user);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postEditProfile(Request $request) {
        $user = $this->auth->user();
        $validator = $this->editUserData->validator($request->all());

        if ($validator->fails()) {
            $this->throwValidationException(
                    $request, $validator
            );
        }
        $this->editUserData->update($user, $request->all());
        return redirect($this->editProfilePath());
    }

    /**
     * Show the application login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function getEditAddress() {
        $user = $this->auth->user();
        $addresses = $this->editUserData->getAddresses($user);
        $regions = Region::all();
        $city = Region::all();
        $countries = Country::all();
        return view('user.editAddress')
                        ->with('user', $user)
                        ->with('regions', $regions)
                        ->with('countries', $countries)
                        ->with('addresses', $addresses);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postEditAddress(Request $request) {
        $user = $this->auth->user();
        $validator = $this->editUserData->validatorAddress($request->all());

        if ($validator->fails()) {
            $this->throwValidationException(
                    $request, $validator
            );
        }
        return response()->json($this->editUserData->createOrUpdateAddress($user, $request->all()));
    }
    
    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postSetAsBillingAddress($address) {
        $user = $this->auth->user();
        return response()->json($this->editUserData->setAsBillingAddress($user,$address));
    }
    
    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function deleteAddress($address) {
        $user = $this->auth->user();
        return response()->json($this->editUserData->deleteAddress($user,$address));
    }

    /**
     * Get the failed login message.
     *
     * @return string
     */
    protected function getFailedEditProfileMessage() {
        return 'There was a problem editing your profile';
    }

    /**
     * Get the failed login message.
     *
     * @return string
     */
    protected function getFailedEditAddressMessage() {
        return 'There was a problem editing your address';
    }

    /**
     * Get the path to thevedit profile route.
     *
     * @return string
     */
    public function editProfilePath() {
        return property_exists($this, 'editProfilePath') ? $this->editProfilePath : '/user/editProfile';
    }

    /**
     * Get the path to thevedit profile route.
     *
     * @return string
     */
    public function editAddressPath() {
        return property_exists($this, 'editAddressPath') ? $this->editProfilePath : '/user/editAddress';
    }

}
