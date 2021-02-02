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
                } else {
                    $data['pseudonim'] = $user->name;
                }
                $isReport = false;
                if (array_key_exists('is_report', $data)) {
                    if ($data['is_report'] == true) {
                        $isReport = true;
                    }
                }
                if (!array_key_exists('comment', $data)) {
                    $data['comment'] = "";
                }
                Rating::where('type', $type)->where('object_id', $data['object_id'])->where('user_id', $user->id)->delete();
                $result = Rating::create([
                            'user_id' => $user->id,
                            'rating' => $data['rating'],
                            'type' => $class,
                            'object_id' => $object->id,
                            'pseudonim' => $data['pseudonim'],
                            'comment' => $data['comment'],
                            'is_report' => $isReport,
                ]);

                $rating = Rating::where('type', $type)->where('object_id', $data['object_id'])->avg('rating');
                $count = Rating::where('type', $type)->where('object_id', $data['object_id'])->count();
                if ($isReport) {
                    $reports = Rating::where('type', $type)
                            ->where('object_id', $data['object_id'])
                            ->where('is_report', true)
                            ->count();
                    if ($reports > 10) {
                        $object->status = "verifying";
                    }
                }
                try {
                    $object->rating = $rating;
                    $object->rating_count = $count;
                    $object->save();
                } catch (Exception $ex) {
                    
                } catch (\Illuminate\Database\QueryException $e) {
                    
                }

                return array(
                    "object_id" => $data['object_id'],
                    "type" => $type,
                    "ratings" => $rating,
                    "result" => $result
                );
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
                $fav = Favorite::where('user_id', $user->id)->where('favoritable_id', $object->id)->where('favoritable_type', $class)->first();
                if ($fav) {
                    $fav->score += 8;
                    $fav->save();
                } else {
                    Favorite::create([
                        'user_id' => $user->id,
                        'score' => 8,
                        'favoritable_type' => $class,
                        'favoritable_id' => $object->id,
                    ]);
                }

                return ['status' => "success", "message" => "favorite added"];
            }
            return ['status' => "error", "message" => "not_found"];
        }
        return ['status' => "error", "message" => "not_found"];
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteFavoriteObject(array $data, User $user) {
        if (strpos($data['type'], 'Models') !== false) {
        } else {
            $data['type'] = "App\\Models\\".$data['type'];
        }
        Favorite::where('user_id', $user->id)
                ->where('favoritable_type', $data['type'])
                ->where('favoritable_id', $data['object_id'])->delete();
        return ['status' => "success", "message" => "favorite removed"];
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorRating() {
        return [
            'type' => 'required|max:255',
            'object_id' => 'required|integer|min:1',
            'rating' => 'required|integer|min:0|max:5'
        ];
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorFavorite() {
        return [
            'type' => 'required|max:255',
            'object_id' => 'required|integer|min:1',
        ];
    }

}
