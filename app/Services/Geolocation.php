<?php

namespace App\Services;

use Grimzy\LaravelMysqlSpatial\Types\Point;
use App\Models\CoveragePolygon;
use DB;
use Grimzy\LaravelMysqlSpatial\Types\MultiPolygon;

class Geolocation {

    var $pointOnVertex = true; // Check if the point sits exactly on one of the vertices

    public function checkMerchantPolygons($latitude, $longitude, $merchant_id, $provider) {
        $point = new Point($latitude, $longitude);
        $polygon = null;
        $query = CoveragePolygon::contains('geometry', $point)->where(function($query) use ($merchant_id) {
                    $query->where('merchant_id', $merchant_id)
                            ->orWhereNull('merchant_id');
                })->orderBy("provider", "desc");
        if ($provider) {
            $query->where('provider', $provider);
            $polygon = $query->first();
            if ($polygon) {
                return array("status" => "success", "message" => "Address in coverage", "polygon" => $polygon);
            } else {
                return array("status" => "error", "message" => "Address not in coverage");
            }
        } else {
            $polygon = $query->get();
            if ($polygon && count($polygon) > 0) {
                return array("status" => "success", "message" => "Address in coverage", "polygon" => $polygon);
            } else {
                return array("status" => "error", "message" => "Address not in coverage");
            }
        }
    }

    public function saveAll() {
        $items = CoveragePolygon::all();
        foreach ($items as $cpolygon) {
            $coordPoints = json_decode($cpolygon->coverage, true);
            if (is_array($coordPoints)) {
                $totalPoints = [];
                foreach ($coordPoints as $coordPoint) {
                    $pointArray = [$coordPoint['lng'], $coordPoint['lat']];
                    array_push($totalPoints, $pointArray);
                }
                $result = [
                    "type" => "MultiPolygon",
                    "coordinates" =>
                    [[$totalPoints]]
                ];
                $mp = MultiPolygon::fromJson(json_encode($result));
                $cpolygon->geometry = $mp;
                $cpolygon->save();
            }
        }
    }

}
