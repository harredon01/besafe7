<?php

namespace App\Services;

use App\Models\Rating;
use App\Models\Favorite;
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
                if (array_key_exists('pseudo', $data)) {
                    if ($data['pseudo']) {
                        
                    } else {
                        $data['pseudonim'] = $user->name;
                    }
                }

                Rating::create([
                    'user_id' => $user->id,
                    'rating' => $data['rating'],
                    'type' => $data['type'],
                    'object_id' => $object->id,
                    'pseudonim' => $data['pseudonim'],
                    'is_report' => $data['is_report'],
                ]);

                $rating = Rating::where('type', $type)->where('object_id', $data['object_id'])->avg('rating');
                if ($type == "Report") {
                    $reports = Rating::where('type', $type)
                            ->where('object_id', $data['object_id'])
                            ->where('is_report', true)
                            ->count();
                    if ($reports > 10) {
                        $object->status = "verifying";
                    }
                }
                $object->rating = $rating;
                $object->save();
            }
        }
    }
    
    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function addFavoriteObject(array $data, User $user) {
        $type = $data['type'];
        $class = "App\\Models\\" . $type;
        if (class_exists($class)) {
            $object = $class::find($data['object_id']);
            if ($object) {
                Favorite::create([
                    'user_id' => $user->id,
                    'type' => $data['type'],
                    'object_id' => $object->id,
                ]);
            }
        }
    }
    
    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteFavoriteObject(array $data, User $user) {
        Favorite::where('user_id',$user->id)
                ->where('type',$data['type'])
                ->where('object_id',$data['object_id'])->delete();
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
    
    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorFavorite(array $data) {
        return Validator::make($data, [
                    'type' => 'required|max:255',
                    'object_id' => 'required|integer|min:1',
        ]);
    }

}
