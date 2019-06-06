<?php

namespace App\Services;

use App\Models\CoveragePolygon;
use App\Models\Address;

class Geolocation {

    var $pointOnVertex = true; // Check if the point sits exactly on one of the vertices

    public function pointLocation() {
        $pointLocation = new pointLocation();
        $points = array("50 70", "70 40", "-20 30", "100 10", "-10 -10", "40 -20", "110 -20");
        $polygon = array("-50 30", "50 70", "100 50", "80 10", "110 -10", "110 -30", "-20 -50", "-30 -40", "10 -10", "-10 10", "-30 -20", "-50 30");
// The last point's coordinates must be the same as the first one's, to "close the loop"
        foreach ($points as $key => $point) {
            echo "point " . ($key + 1) . " ($point): " . $pointLocation->pointInPolygon($point, $polygon) . "<br>";
        }
    }

    public function pointInPolygon($point, $vertices, $pointOnVertex = true) {
        $this->pointOnVertex = $pointOnVertex;

        // Transform string coordinates into arrays with x and y values
//        $point = $this->pointStringToCoordinates($point);
//        $vertices = array();
//        foreach ($polygon as $vertex) {
//            $vertices[] = $this->pointStringToCoordinates($vertex);
//        }

        // Check if the point sits exactly on a vertex
        if ($this->pointOnVertex == true and $this->pointOnVertex($point, $vertices) == true) {
            return "vertex";
        }

        // Check if the point is inside the polygon or on the boundary
        $intersections = 0;
        $vertices_count = count($vertices);

        for ($i = 1; $i < $vertices_count; $i++) {
            $vertex1 = $vertices[$i - 1];
            $vertex2 = $vertices[$i];
            if ($vertex1['y'] == $vertex2['y'] and $vertex1['y'] == $point['y'] and $point['x'] > min($vertex1['x'], $vertex2['x']) and $point['x'] < max($vertex1['x'], $vertex2['x'])) { // Check if point is on an horizontal polygon boundary
                return "boundary";
            }
            if ($point['y'] > min($vertex1['y'], $vertex2['y']) and $point['y'] <= max($vertex1['y'], $vertex2['y']) and $point['x'] <= max($vertex1['x'], $vertex2['x']) and $vertex1['y'] != $vertex2['y']) {
                $xinters = ($point['y'] - $vertex1['y']) * ($vertex2['x'] - $vertex1['x']) / ($vertex2['y'] - $vertex1['y']) + $vertex1['x'];
                if ($xinters == $point['x']) { // Check if point is on the polygon boundary (other than horizontal)
                    return "boundary";
                }
                if ($vertex1['x'] == $vertex2['x'] || $point['x'] <= $xinters) {
                    $intersections++;
                }
            }
        }
        // If the number of edges we passed through is odd, then it's in the polygon. 
        if ($intersections % 2 != 0) {
            return "inside";
        } else {
            return "outside";
        }
    }
    
    public function prepareQuadrantLimits($lat, $long, $x) {
        $R = 6371;
        if ($x < 4) {
            $radiusInf = 0;
            $radius = 7;
        } else {
            $radiusInf = 3;
            $radius = 7;
        }
        $maxLat = $lat + rad2deg($radius / $R);
        $minLat = $lat - rad2deg($radius / $R);
        $maxLon = $long + rad2deg(asin($radius / $R) / cos(deg2rad($lat)));
        $minLon = $long - rad2deg(asin($radius / $R) / cos(deg2rad($lat)));
        if ($x == 0 || $x == 4) {
            $thedata = [
                'lat' => $lat,
                'lat2' => $lat,
                'long' => $long,
                'latinf' => $lat,
                'latsup' => $maxLat,
                'longinf' => $long,
                'longsup' => $maxLon,
                'radiusInf' => $radiusInf,
                'radius' => $radius
            ];
        } else if ($x == 1 || $x == 5) {
            $thedata = [
                'lat' => $lat,
                'lat2' => $lat,
                'long' => $long,
                'latinf' => $minLat,
                'latsup' => $lat,
                'longinf' => $long,
                'longsup' => $maxLon,
                'radiusInf' => $radiusInf,
                'radius' => $radius
            ];
        } else if ($x == 2 || $x == 6) {
            $thedata = [
                'lat' => $lat,
                'lat2' => $lat,
                'long' => $long,
                'latinf' => $minLat,
                'latsup' => $lat,
                'longinf' => $minLon,
                'longsup' => $long,
                'radiusInf' => $radiusInf,
                'radius' => $radius
            ];
        } else if ($x == 3 || $x == 7) {
            $thedata = [
                'lat' => $lat,
                'lat2' => $lat,
                'long' => $long,
                'latinf' => $lat,
                'latsup' => $maxLat,
                'longinf' => $minLon,
                'longsup' => $long,
                'radiusInf' => $radiusInf,
                'radius' => $radius
            ];
        }
        return $thedata;
    }
    
    public function prepareRouteModel(array $thedata, $results, $preOrganize, $x, $polygon) {
//        dd($thedata);
        $thedata['polygon'] = $polygon;
        $deliveries = DB::select(""
                        . "SELECT DISTINCT(d.id), d.delivery,d.details,d.user_id,d.address_id,status,shipping, lat,`long`, 
			( 6371 * acos( cos( radians( :lat ) ) *
		         cos( radians( lat ) ) * cos( radians(  `long` ) - radians( :long ) ) +
		   sin( radians( :lat2 ) ) * sin( radians(  lat  ) ) ) ) AS Distance 
                   FROM deliveries d join order_addresses a on d.address_id = a.id
                    WHERE
                        status = 'transit'
                            AND d.user_id = 1
                            AND lat >= :latinf AND lat < :latsup
                            AND `long` >= :longinf AND `long` < :longsup
                            AND a.polygon_id = :polygon order by Distance asc"
                        . "", $thedata);
        //echo "Query params: ". json_encode($thedata). PHP_EOL;
        //echo "Query results: " . count($deliveries) . PHP_EOL;
        $stops = $this->turnDeliveriesIntoStops($deliveries, $preOrganize);

        if ($preOrganize) {
            $results = $this->createRoutes($stops, $results, $x, 'preorganize', true);
        } else {
            $results = $this->createRoutes($stops, $results, $x, 'simple', true);
        }
        //dd($results);
        return $results;
    }

    public function pointOnVertex($point, $vertices) {
        foreach ($vertices as $vertex) {
            if ($point == $vertex) {
                return true;
            }
        }
    }

    public function pointStringToCoordinates($pointString) {
        $coordinates = explode(" ", $pointString);
        return array("x" => $coordinates[0], "y" => $coordinates[1]);
    }

    public function checkMerchantPolygons($latitude,$longitude, $merchant_id,$provider = "Basilikum") {
        $polygons = CoveragePolygon::where('merchant_id', $merchant_id)->where('provider', $provider)->get();
        $point = array("x" => $latitude, "y" => $longitude);
        foreach ($polygons as $item) {
            $coveragePoints = json_decode($item->coverage, true);
            $vertices = array();
            foreach ($coveragePoints as $vertex) {
                $vertices[] = array("x" => $vertex['lat'], "y" => $vertex['lng']);
            }
            $result = $this->pointInPolygon($point, $vertices, true);
            
            if($result != "outside"){
                return array("status" => "success", "message" => "Address in coverage","polygon" => $item);
            }
        }
        return array("status" => "error", "message" => "Address not in coverage" );
    }

}
