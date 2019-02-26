<?php

namespace App\Services;

use Excel;

class Proofhub {

    public function sendPost(array $data, $query) {
        //url-ify the data for the POST
        $fields_string = "";
        foreach ($data as $key => $value) {
            $fields_string .= $key . '=' . $value . '&';
        }
        rtrim($fields_string, '&');
        $curl = curl_init($query);
        //dd($data);
        $headers = array(
            'Accept: application/json',
            'Authorization: Basic ' . env('RAPIGO_KEY')
        );
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode(str_replace("\\", "", $response), true);
        return $response;
    }

    public function sendGet($query) {
        $curl = curl_init($query);
        $headers = array(
            'Content-Type: application/json; charset=utf-8',
            'X-API-KEY: 90d41d8553fb0c53f627750214995f31bc1c7287',
            'Accept: application/json',
            'User-Agent: HoovStats (hoovert@backbone.digital)'
        );
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers
        );
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode(str_replace("\\", "", $response), true);
        return $response;
    }

    public function getProjectTimeSheets($project) {
        $query = "https://backbone.proofhub.com/api/v3/projects/" . $project['code'] . "/timesheets";
        //dd($query);
        $sheets = $this->sendGet($query);
        foreach ($sheets as $sheet) {
            $project = $this->getTimeSheetTime($project, $sheet['id']);
        }
        return $project;
    }

    public function calculateTotalsProject($project) {
        $people = $project["rows"];
        $totalCost = 0;
        $totalHours = 0;
        $finalPeople = [];
        foreach ($people as $person) {
            if ($person["total_cost"] > 0 || $person["billable"] > 0 || $person["non_billable"] > 0) {
                array_push($finalPeople, $person);
                $totalCost += $person["total_cost"];
                $totalHours += ($person["billable"] + $person["non_billable"]);
            }
        }
        $project["rows"] = $finalPeople;
        $project["Consumed"] = $totalCost;
        $project["Hours"] = $totalHours;
        return $project;
    }

    public function getTimeSheetTime($project, $sheet) {
        $query = "https://backbone.proofhub.com/api/v3/projects/" . $project['code'] . "/timesheets/" . $sheet . "/time";
        //dd($query);
        $timeEntries = $this->sendGet($query);
        $results = [];
        foreach ($project["rows"] as $person) {
            foreach ($timeEntries as $timeEntry) {
                if ($timeEntry["creator"]["id"] == $person["id"]) {
                    if ($timeEntry["status"] == "non billable") {
                        $person["non_billable"] += ($timeEntry["logged_hours"] + ($timeEntry["logged_mins"] / 60));
                    } else {
                        $person["billable"] += ($timeEntry["logged_hours"] + ($timeEntry["logged_mins"] / 60));
                        $person["total_cost"] = $person["billable"] * $person["cost"];
                    }
                }
            }
            array_push($results, $person);
        }
        $project["rows"] = $results;
        return $project;
    }

    public function getLabels() {
        $query = "https://backbone.proofhub.com/api/v3/labels";
        //dd($query);
        $labels = $this->sendGet($query);
        dd($labels);
    }

    public function writeFile($data) {
        Excel::create('TotalHealthChecks', function($excel) use($data) {

            $excel->setTitle('TotalHealthChecks');
            // Chain the setters
            $excel->setCreator('Hoovert Arredondo')
                    ->setCompany('Backbone Technology');
            // Call them separately
            $excel->setDescription('This report is clasified');
            foreach ($data as $page) {
                $excel->sheet(substr($page["name"], 0, 30), function($sheet) use($page) {
                    $sheet->fromArray($page["rows"], null, 'A1', true);
                });
            }
        })->store('xlsx');
    }

    public function getSummary() {
        $people = $this->getPeople();
        $copy = $people;
        $projects = [
            [
                "name" => "TaxPayer Redesign and Dev",
                "budget" => "50000",
                "code" => "1420446568",
                "rows" => $copy,
            ],
            [
                "name" => "NOVEDADES GUILLERS",
                "budget" => "50000",
                "code" => "1871891261",
                "rows" => $copy,
            ]
        ];
        $results = [];
        $summary = [];
        $summary["rows"] = [];
        $totalHours = 0;
        $totalCost = 0;
        foreach ($projects as $project) {
            $project = $this->getProjectTimeSheets($project, $people);
            $project = $this->calculateTotalsProject($project);
            $projectData = [
                "Name" => $project["name"],
                "Budget" => $project["budget"],
                "Hours" => $project["Hours"],
                "Consumed" => $project["Consumed"],
            ];
            $totalHours += $project["Hours"];
            $totalCost += $project["Consumed"];
            array_push($results, $project);
            array_push($summary["rows"], $projectData);
        }
        $summary["Hours"] = $totalHours;
        $summary["name"] = "Summary";
        $summary["Consumed"] = $totalCost;
        array_unshift($results, $summary);
        $this->writeFile($results);
        dd($results);
    }

    public function getPeople() {
        $query = "https://backbone.proofhub.com/api/v3/groups/1010576416";
        //dd($query);
        $results = $this->sendGet($query);
        $people = $results['assigned'];
        $orderedPeople = [];
        foreach ($people as $person) {
            $query = "https://backbone.proofhub.com/api/v3/people/" . $person;
            //dd($query);
            $resultsPerson = $this->sendGet($query);
            $saveperson = [
                "name" => $resultsPerson['first_name'] . " " . $resultsPerson['last_name'],
                "email" => $resultsPerson['email'],
                "id" => $resultsPerson['id'],
                "billable" => 0,
                "non_billable" => 0,
                "total_cost" => 0
            ];
            if ($resultsPerson['id'] == "1871117844") {
                //andres
                $saveperson['cost'] = 35000;
            } else if ($resultsPerson['id'] == "4232277661") {
                //Deyvid
                $saveperson['cost'] = 10000;
            } else if ($resultsPerson['id'] == "3749231432") {
                //Fabian Horta
                $saveperson['cost'] = 50000;
            } else if ($resultsPerson['id'] == "1856477202") {
                //Fabian Vargas
                $saveperson['cost'] = 20000;
            } else if ($resultsPerson['id'] == "2133970978") {
                //Gobert Perdomo
                $saveperson['cost'] = 12000;
            } else if ($resultsPerson['id'] == "1923370963") {
                //Juan Arredondo
                $saveperson['cost'] = 12000;
            } else if ($resultsPerson['id'] == "2936451878") {
                //Manuel Correa
                $saveperson['cost'] = 20000;
            } else if ($resultsPerson['id'] == "1923533788") {
                //Victor Gil
                $saveperson['cost'] = 12000;
            } else if ($resultsPerson['id'] == "1923533788") {
                //Hoovert
                $saveperson['cost'] = 12000;
            }
            array_push($orderedPeople, $saveperson);
        }
        return $orderedPeople;
    }

}