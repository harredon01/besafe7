<?php

namespace App\Services;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use App\Models\CoveragePolygon;
class Geolocation {

    var $pointOnVertex = true; // Check if the point sits exactly on one of the vertices

    public function checkMerchantPolygons($latitude, $longitude, $merchant_id, $provider) {
        $point = new Point($latitude,$longitude);
        $polygon = null;
        $query = CoveragePolygon::contains('geometry', $point)->where(function($query) use ($merchant_id) {
                    $query->where('merchant_id', $merchant_id)
                            ->orWhereNull('merchant_id');
                })->orderBy("provider","desc");
        if ($provider) {
            $query->where('provider', $provider); 
            $polygon = $query->first();
        } else {
            $polygon = $query->get();
        }
        if($polygon && count($polygon)>0){
            return array("status" => "success", "message" => "Address in coverage", "polygon" => $polygon);
        } else {
            return array("status" => "error", "message" => "Address not in coverage");
        }
    }
}
