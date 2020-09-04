<?php

use Illuminate\Database\Seeder;
use App\Models\CoveragePolygon;
use Grimzy\LaravelMysqlSpatial\Types\MultiPolygon;

class MigrateCoverageArea extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $cpolygons = CoveragePolygon::all();
        foreach ($cpolygons as $cpolygon) {
            $coordPoints = json_decode($cpolygon->coverage, true);
            $totalPoints = [];
            foreach ($coordPoints as $coordPoint) {
                $pointArray = [ $coordPoint['lng'],$coordPoint['lat']];
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
