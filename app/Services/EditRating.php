<?php

namespace App\Services;

use App\Models\Rating;
use App\Models\User;
use Validator;

class EditRating {

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function addRatingObject(array $data, User $user) {
        $type = $data['type'];
        $class = "App\\Models\\" . $type;
        if (class_exists($class)) {
            $object = $class::find($data['object_id']);
            if ($object) {
                Rating::create([
                    'user_id' => $user->id,
                    'rating' => $data['rating'],
                    'type' => $data['type'],
                    'object_id' => $data['rating'],
                ]);

                $rating = Rating::where('type', $type)->where('object_id', $data['object_id'])->avg('rating');
                if( $type == "Report" ){
                    $reports = Rating::where('type', $type)
                            ->where('object_id', $data['object_id'])
                            ->where('is_report', true)
                            ->count();
                    if($reports > 10 ){
                        $object->status = "verifying";
                    }
                }
                $object->rating = $rating;
                $object->save();
            }
        }
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorRating(array $data) {
        return Validator::make($data, [
                    'type' => 'required|max:255',
                    'comment' => 'required',
                    'object_id' => 'required|integer|min:1',
                    'rating' => 'required|integer|min:0|max:5'
        ]);
    }

}
