<?php

namespace App\Services;

use App\Models\Rating;
use App\Models\Favorite;
use App\Models\User;
use Validator;

class EditBooking {

    private function checkAvailable($type, $id) {
        return true;
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function addBookingObject(array $data, User $user) {
        $result = $this->checkAvailable($data['type'], $data['object_id']);
        if ($result) {
            $type = $data['type'];
            $class = "App\\Models\\" . $type;
            if (class_exists($class)) {
                $object = $class::find($data['object_id']);
                if ($object) {
                    
                }
            }
        }
    }
    
    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function getBookingsObject(array $data, User $user) {
        $result = $this->checkAvailable($data['type'], $data['object_id']);
        if ($result) {
            $type = $data['type'];
            $class = "App\\Models\\" . $type;
            if (class_exists($class)) {
                $object = $class::find($data['object_id']);
                if ($object) {
                    
                }
            }
        }
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteBookingObject(array $data, User $user) {
        Favorite::where('user_id', $user->id)
                ->where('favorite_type', $data['type'])
                ->where('object_id', $data['object_id'])->delete();
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorBooking() {
        return [
            'type' => 'required|max:255',
            'object_id' => 'required|integer|min:1',
        ];
    }

}
