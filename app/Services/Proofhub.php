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
            'X-API-KEY: d1bc2bf2e25adeaa6bee25e746ac91edc9b0c521',
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
        $totalTimeEntries = [];
        foreach ($sheets as $sheet) {
            $results = $this->getTimeSheetTime($project, $sheet['id']);
            $totalTimeEntries = array_merge($totalTimeEntries, $results);
        }
        return $totalTimeEntries;
    }

    public function getProjectTaskLists($project) {
        $query = "https://backbone.proofhub.com/api/v3/projects/" . $project['code'] . "/todolists";
        //dd($query);
        $lists = $this->sendGet($query);
        $totalTasks = [];
        foreach ($lists as $list) {
            $results = $this->getTasksList($project, $list['id']);
            if (is_array($results)) {
                $totalTasks = array_merge($totalTasks, $results);
            }
        }
        return $totalTasks;
    }

    public function getTasksList($project, $list) {
        $query = "https://backbone.proofhub.com/api/v3/projects/" . $project['code'] . "/todolists/" . $list . "/tasks";
        //dd($query);
        $tasks = $this->sendGet($query);
        return $tasks;
    }

    public function calculateTotalsProject($projectPeople) {
        $totalCost = 0;
        $totalHours = 0;
        $billable = 0;
        $billableCost = 0;
        $nonbillable = 0;
        $nonbillableCost = 0;
        $finalPeople = [];
        $project = [];
        foreach ($projectPeople as $person) {
            if ($person["total_cost"] > 0 || $person["hours"] > 0) {
                array_push($finalPeople, $person);
                $totalCost += $person["total_cost"];
                $totalHours += $person["hours"];
                $billableCost += $person["billable_cost"];
                $billable += $person["billable"];
                $nonbillableCost += $person["non_billable_cost"];
                $nonbillable += $person["non_billable"];
            }
        }
        $project["rows"] = $finalPeople;
        $project["Consumed"] = $totalCost;
        $project["billable"] = $billable;
        $project["billable_cost"] = $billableCost;
        $project["non_billable"] = $nonbillable;
        $project["non_billable_cost"] = $totalCost;
        $project["Hours"] = $totalHours;
        return $project;
    }

    public function getTimeSheetTime($project, $sheet) {
        $query = "https://backbone.proofhub.com/api/v3/projects/" . $project['code'] . "/timesheets/" . $sheet . "/time";

        //dd($query);
        $timeEntries = $this->sendGet($query);
        if ($sheet == "3998583695") {
            //dd($timeEntries);
        }
        return $timeEntries;
    }

    public function getLabels() {
        $query = "https://backbone.proofhub.com/api/v3/labels";
        //dd($query);
        $labels = $this->sendGet($query);
        $resultLabels = [];
        foreach ($labels as $label) {
            $label["invested_hours"] = 0;
            array_push($resultLabels, $label);
        }
        return $resultLabels;
    }

    public function getTimeEntryTask($timeEntry, $tasks) {
        foreach ($tasks as $task) {
            if ($task["id"] == $timeEntry["task"]["task_id"]) {
                return $task;
            }
        }
    }

    private function getTimeEntryPerson($timeEntry, &$people) {
        foreach ($people as &$person) {
            if ($person["id"] == $timeEntry["creator"]["id"]) {
                return $person;
            }
        }
    }

    public function writeFile($data) {

        Excel::create('TotalHealthChecks_' . time(), function($excel) use($data) {

            $excel->setTitle('TotalHealthChecks_' . time());
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
        $labels = $this->getLabels();
        $people = $this->getPeople($labels);
        $date1 = "2019-02-20";
        $date2 = "2019-04-01";
        $dateTimestamp1 = strtotime($date1);
        $dateTimestamp2 = strtotime($date2);
        $copy = $people;
        $projects = [
            [
                "name" => "TaxPayer Redesign and Dev",
                "budget" => "50000",
                "country" => "COL",
                "price" => "Retail",
                "code" => "1420446568",
                "rows" => $copy,
            ],
            /*[
                "name" => "Archies",
                "budget" => "0",
                "country" => "COL",
                "price" => "Retail",
                "code" => "1714087114",
                "rows" => $copy,
            ],
            [
                "name" => "Primus",
                "budget" => "0",
                "country" => "COL",
                "price" => "Retail",
                "code" => "2400881156",
                "rows" => $copy,
            ],
            [
                "name" => "Daportare",
                "budget" => "0",
                "country" => "COL",
                "price" => "Retail",
                "code" => "2489878353",
                "rows" => $copy,
            ],
            [
                "name" => "Next Conn-Infrastructure",
                "budget" => "0",
                "country" => "COL",
                "price" => "Retail",
                "code" => "2581819961",
                "rows" => $copy,
            ],
            [
                "name" => "Xtech",
                "budget" => "0",
                "country" => "COL",
                "price" => "Retail",
                "code" => "2349279336",
                "rows" => $copy,
            ],
            [
                "name" => "Niños en movimiento",
                "budget" => "1200000",
                "country" => "COL",
                "price" => "Retail",
                "code" => "2727778621",
                "rows" => $copy,
            ],
            [
                "name" => "NOVEDADES GUILLERS",
                "budget" => "50000",
                "country" => "COL",
                "price" => "Retail",
                "code" => "1871891261",
                "rows" => $copy,
            ],
            [
                "name" => "Magic Flavors",
                "budget" => "30000000",
                "country" => "COL",
                "price" => "Retail",
                "code" => "2541330918",
                "rows" => $copy,
            ],
            [
                "name" => "El techo",
                "budget" => "50000",
                "country" => "COL",
                "price" => "Retail",
                "code" => "1889991926",
                "rows" => $copy,
            ],
            [
                "name" => "Cano",
                "budget" => "5000",
                "country" => "COL",
                "price" => "Retail",
                "code" => "2234243776",
                "rows" => $copy,
            ],
            [
                "name" => "CBC",
                "budget" => "2400000",
                "country" => "COL",
                "price" => "Retail",
                "code" => "1714114251",
                "rows" => $copy,
            ],
            [
                "name" => "Ezpot",
                "budget" => "5000000",
                "country" => "COL",
                "price" => "Retail",
                "code" => "1498154595",
                "rows" => $copy,
            ],
            [
                "name" => "Juan Valdez",
                "budget" => "5500000",
                "country" => "COL",
                "price" => "Retail",
                "code" => "1753802741",
                "rows" => $copy,
            ],
            [
                "name" => "Celsia",
                "budget" => "2500000",
                "country" => "COL",
                "price" => "Retail",
                "code" => "2349062236",
                "rows" => $copy,
            ],
            [
                "name" => "Papa Johns",
                "budget" => "2400000",
                "country" => "COL",
                "price" => "Retail",
                "code" => "1753924859",
                "rows" => $copy,
            ],
            [
                "name" => "Super de alimentos",
                "budget" => "0",
                "country" => "COL",
                "price" => "Retail",
                "code" => "1422726112",
                "rows" => $copy,
            ],
            [
                "name" => "Dilucca",
                "budget" => "0",
                "country" => "COL",
                "price" => "Retail",
                "code" => "1421925558",
                "rows" => $copy,
            ],
            [
                "name" => "BYC en casa",
                "budget" => "500000",
                "country" => "COL",
                "price" => "Retail",
                "code" => "1739080686",
                "rows" => $copy,
            ]*/
        ];
        $results = [];
        $summary = [];
        $summary["rows"] = [];
        $totalHours = 0;
        $totalCost = 0;
                foreach ($projects as $project) {

            $projectPeople = $people;
            $tasks = $this->getProjectTaskLists($project);
            $totalPendingTasks = 0;
            $totalMissingHours = 0;
            foreach ($tasks as $task) {
                if (!$task["completed"]) {
                    $totalPendingTasks++;
                    $estimate = $task["estimated_hours"] + ($task["estimated_mins"] / 60);
                    $logged = $task["logged_hours"] + ($task["logged_mins"] / 60);
                    if ($estimate > $logged) {
                        $totalMissingHours += $estimate - $logged;
                    }
                }
            }
            $timeEntries = $this->getProjectTimeSheets($project);
            $resultsPeople = [];
            foreach ($projectPeople as $person) {
                if (array_key_exists("cost", $person)) {
                    
                } else {
                    dd($person);
                }
                foreach ($timeEntries as $timeEntry) {
                    $timeEntryTimestamp = strtotime($timeEntry["date"]);
                    if ($timeEntryTimestamp > $dateTimestamp1 && $timeEntryTimestamp < $dateTimestamp2) {
                        if ($timeEntry["creator"]["id"] == $person["id"]) {
                            $task = $this->getTimeEntryTask($timeEntry, $tasks);
                            $taskLabels = $task["labels"];
                            $isBillable = true;
                            if ($taskLabels) {
                                foreach ($taskLabels as $taskLabel) {
                                    if ($taskLabel == "2974484984") {
                                        $isBillable = false;
                                    }
                                }
                            }
                            if ($isBillable) {
                                $person["billable"] += ($timeEntry["logged_hours"] + ($timeEntry["logged_mins"] / 60));
                                if($project["country"]=="COL"){
                                    if($project["price"]=="Wholesale"){
                                        $person["billable_cost"] = $person["billable"] * $person["cost"];
                                    } else {
                                        $person["billable_cost"] = $person["billable"] * $person["retail"];
                                    }
                                } else {
                                    if($project["price"]=="Wholesale"){
                                        $person["billable_cost"] = $person["billable"] * $person["cost_us"];
                                    } else {
                                        $person["billable_cost"] = $person["billable"] * $person["retail_us"];
                                    }
                                }
                                
                            } else {
                                $person["non_billable"] += ($timeEntry["logged_hours"] + ($timeEntry["logged_mins"] / 60));
                                $person["non_billable_cost"] = $person["non_billable"] * $person["cost"];
                            }
                            $person["hours"] += ($timeEntry["logged_hours"] + ($timeEntry["logged_mins"] / 60));
                            $person["total_cost"] = $person["hours"] * $person["cost"];
                        }
                    }
                }
                if ($person["hours"] > 0) {
                    array_push($resultsPeople, $person);
                }
            }
            $project['people'] = $resultsPeople;
            $projectLabels = $labels;
            $resultsLabels = [];
            foreach ($projectLabels as $label) {
                $hours = 0;
                foreach ($timeEntries as $timeEntry) {
                    $timeEntryTimestamp = strtotime($timeEntry["date"]);
                    if ($timeEntryTimestamp > $dateTimestamp1 && $timeEntryTimestamp < $dateTimestamp2) {
                        $task = $this->getTimeEntryTask($timeEntry, $tasks);
                        $taskLabels = $task["labels"];
                        if ($taskLabels) {
                            foreach ($taskLabels as $taskLabel) {
                                if ($label["id"] == $taskLabel) {
                                    $hours += ($timeEntry["logged_hours"] + ($timeEntry["logged_mins"] / 60));
                                }
                            }
                        }
                    }
                }
                if ($hours > 0) {
                    $insert = $label;
                    unset($insert["id"]);
                    unset($insert["color"]);
                    $insert["Hours"] = $hours;
                    array_push($resultsLabels, $insert);
                }
            }
            $project['labels'] = $resultsLabels;
            $project["rows"] = array_merge($resultsPeople, $resultsLabels);
            $projectResults = $this->calculateTotalsProject($resultsPeople);
            $projectData = [
                "Name" => $project["name"],
                "Budget" => $project["budget"],
                "Hours" => $projectResults["Hours"],
                "Consumed" => $projectResults["Consumed"],
                "billable" => $projectResults["billable"],
                "billable_cost" => $projectResults["billable_cost"],
                "non_billable_cost" => $projectResults["non_billable_cost"],
                "non_billable" => $projectResults["non_billable"],
                "Total Pending tasks" => $totalPendingTasks,
                "Estimated remaining hours" => $totalMissingHours,
            ];
            $totalHours += $projectResults["Hours"];
            $totalCost += $projectResults["Consumed"];
            array_push($results, $project);
            array_push($summary["rows"], $projectData);
        }
        $summary["Hours"] = $totalHours;
        $summary["name"] = "Summary";
        $summary["Consumed"] = $totalCost;
        //$peopleResults = $this->calculateTotalsPeople($results,$people);
        array_unshift($results, $summary);
        $this->writeFile($results);
    }
    
    private function calculateTotalsPeople($results,$people){
        foreach ($results as $project) {
            
        }
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
                "hours" => 0,
                "billable" => 0,
                "non_billable" => 0,
                "total_cost" => 0,
                "billable_cost" => 0,
                "non_billable_cost" => 0
            ];
            if ($resultsPerson['id'] == "1871117844") {
                //andres
                $saveperson['cost'] = 57691;
                $saveperson['retail'] = 68181;
                $saveperson['cost_us'] = 19.23;
                $saveperson['retail_us'] = 22.73;
                
            } else if ($resultsPerson['id'] == "4232277661") {
                //Deyvid
                $saveperson['cost'] = 26580;
                $saveperson['retail'] = 31413;
                $saveperson['cost_us'] = 8.86;
                $saveperson['retail_us'] = 10.47;
            } else if ($resultsPerson['id'] == "3749231432") {
                //Fabian Horta
                $saveperson['cost'] = 49916;
                $saveperson['retail'] = 58992;
                $saveperson['cost_us'] = 16.64;
                $saveperson['retail_us'] = 19.66;
            } else if ($resultsPerson['id'] == "1856477202") {
                //Fabian Vargas
                $saveperson['cost'] = 41412;
                $saveperson['retail'] = 48942;
                $saveperson['cost_us'] = 13.80;
                $saveperson['retail_us'] = 16.31;
            } else if ($resultsPerson['id'] == "2133970978") {
                //Gobert Perdomo
                $saveperson['cost'] = 30887;
                $saveperson['retail'] = 36502;
                $saveperson['cost_us'] = 10.30;
                $saveperson['retail_us'] = 12.17;
            } else if ($resultsPerson['id'] == "1923370963") {
                //Juan Arredondo
                $saveperson['cost'] = 29411;
                $saveperson['retail'] = 34758;
                $saveperson['cost_us'] = 9.8;
                $saveperson['retail_us'] = 11.6;
            } else if ($resultsPerson['id'] == "2936451878") {
                //Manuel Correa
                $saveperson['cost'] = 41940;
                $saveperson['retail'] = 49565;
                $saveperson['cost_us'] = 13.98;
                $saveperson['retail_us'] = 16.52;
            } else if ($resultsPerson['id'] == "1923533788") {
                //Victor Gil
                $saveperson['cost'] = 30756;
                $saveperson['retail'] = 36348;
                $saveperson['cost_us'] = 10.25;
                $saveperson['retail_us'] = 12.12;
            } else if ($resultsPerson['id'] == "1849041546") {
                //Hoovert
                $saveperson['cost'] = 111154;
                $saveperson['retail'] = 131363;
                $saveperson['cost_us'] = 37.05;
                $saveperson['retail_us'] = 43.79;
            } else if ($resultsPerson['id'] == "2316429479") {
                //Julian
                $saveperson['cost'] = 26580;
                $saveperson['retail'] = 314413;
                $saveperson['cost_us'] = 8.86;
                $saveperson['retail_us'] = 10.47;
            } else if ($resultsPerson['id'] == "1856450064") {
                //Jucaro
                $saveperson['cost'] = 51451;
                $saveperson['retail'] = 60806;
                $saveperson['cost_us'] = 17.15;
                $saveperson['retail_us'] = 20.27;
            } else if ($resultsPerson['id'] == "4173918621") {
                //Sabine
                $saveperson['cost'] = 36600;
                $saveperson['retail'] = 43254;
                $saveperson['cost_us'] = 12.20;
                $saveperson['retail_us'] = 14.42;
            } else if ($resultsPerson['id'] == "4472620297") {
                //Neiderson
                $saveperson['cost'] = 26580;
                $saveperson['retail'] = 31413;
                $saveperson['cost_us'] = 8.86;
                $saveperson['retail_us'] = 10.47;
            } else if ($resultsPerson['id'] == "3464709062") {
                //Karina Rodriguez
                $saveperson['cost'] = 41940;
                $saveperson['retail'] = 49565;
                $saveperson['cost_us'] = 13.98;
                $saveperson['retail_us'] = 16.52;
            } else if ($resultsPerson['id'] == "1856110846") {
                //David Mendez
                $saveperson['cost'] = 81418;
                $saveperson['retail'] = 96221;
                $saveperson['cost_us'] = 27.14;
                $saveperson['retail_us'] = 32.07;
            } else if ($resultsPerson['id'] == "2304516147") {
                //Alejandra Archila
                $saveperson['cost'] = 30726;
                $saveperson['retail'] = 36313;
                $saveperson['cost_us'] = 10.24;
                $saveperson['retail_us'] = 12.10;
            } else if ($resultsPerson['id'] == "3216537278") {
                //Laura Aires
                $saveperson['cost'] = 36600;
                $saveperson['retail'] = 43254;
                $saveperson['cost_us'] = 12.20;
                $saveperson['retail_us'] = 14.42;
            } else if ($resultsPerson['id'] == "4194692322") {
                //Paula Lozano
                $saveperson['cost'] = 43258;
                $saveperson['retail'] = 51123;
                $saveperson['cost_us'] = 14.42;
                $saveperson['retail_us'] = 17.04;
            } else if ($resultsPerson['id'] == "4151530242") {
                //Juliana Acosta
                $saveperson['cost'] = 43258;
                $saveperson['retail'] = 51123;
                $saveperson['cost_us'] = 14.42;
                $saveperson['retail_us'] = 17.04;
            } else if ($resultsPerson['id'] == "2791958639") {
                //Oscar Gil
                $saveperson['cost'] = 52770;
                $saveperson['retail'] = 62364;
                $saveperson['cost_us'] = 17.59;
                $saveperson['retail_us'] = 20.79;
            } else if ($resultsPerson['id'] == "2213225838") {
                //Edinson Loaisa
                $saveperson['cost'] = 33580;
                $saveperson['retail'] = 39685;
                $saveperson['cost_us'] = 11.19;
                $saveperson['retail_us'] = 13.23;
            } else {
                $saveperson['cost'] = 23;
            }
            array_push($orderedPeople, $saveperson);
        }
        return $orderedPeople;
    }

}
