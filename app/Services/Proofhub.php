<?php

namespace App\Services;

use Excel;

class Proofhub {

    const TYPE_COST = 'retail';
    const START_DATE = '2019-03-01';
    const END_DATE = '2019-04-01';
    const PRODUCCION = 'Produccion';
    const CUENTAS = 'Cuentas';
    const CANADA = 'Canada';
    const INTERNO = 'Interno';
    const IS_ADMIN = true;
    const DIAS_HABILES = 19;
    const MIN_HORAS_DIARIAS = 7;
    const COSTO_HORA_PROMEDIO = 54887;

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
        $responseString = $response;
//        $response = str_replace("&quot;", "'", $response);
//        $response = str_replace("\\", "", $response);
//        $response = str_replace('\"', "'", $response);
        curl_close($curl);
        $response = json_decode($response, true);
        if (!is_array($response)) {
            dd($responseString);
        }
        return $response;
    }

    public function getProjectTimeSheets($project) {
        $query = "https://backbone.proofhub.com/api/v3/projects/" . $project['code'] . "/timesheets";
        //dd($query);
        $sheets = $this->sendGet($query);
        $totalTimeEntries = [];
        foreach ($sheets as $sheet) {
            $results = $this->getTimeSheetTime($project, $sheet['id']);
            if (is_array($results)) {
                $totalTimeEntries = array_merge($totalTimeEntries, $results);
            }
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

    public function getProjectEvents($project) {
        $query = "https://backbone.proofhub.com/api/v3/projects/" . $project['code'] . "/events?view=milestones";
        $events = $this->sendGet($query);
        if (is_array($events)) {
            return $events;
        }
        return [];
    }

    public function getTasksList($project, $list) {
        $query = "https://backbone.proofhub.com/api/v3/projects/" . $project['code'] . "/todolists/" . $list . "/tasks";
        //dd($query);
        $tasks = $this->sendGet($query);
        if (!is_array($tasks)) {
            dd($query);
        }
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
            if ($person["hours"] > 0) {
                array_push($finalPeople, $person);
                if (!array_key_exists(self::TYPE_COST, $person)) {
                    if ($person["id"] == 4155953643) {
                        $person[self::TYPE_COST] = 30000;
                    } else {
                        $person[self::TYPE_COST] = 30000;
                        //dd($person);
                    }
                }
                $totalCost += $person[self::TYPE_COST] * $person["hours"];
                $totalHours += $person["hours"];
                /* $billableCost += $person["billable_cost"];
                  $billable += $person["billable"];
                  $nonbillableCost += $person["non_billable_cost"];
                  $nonbillable += $person["non_billable"]; */
            }
        }
        $project["rows"] = $finalPeople;
        $project["Consumed"] = $totalCost;
        /* $project["billable"] = $billable;
          $project["billable_cost"] = $billableCost;
          $project["non_billable"] = $nonbillable;
          $project["non_billable_cost"] = $totalCost; */
        $project["Hours"] = $totalHours;
        return $project;
    }

    public function getTimeSheetTime($project, $sheet) {
        $query = "https://backbone.proofhub.com/api/v3/projects/" . $project['code'] . "/timesheets/" . $sheet . "/time";

        //dd($query);
        $timeEntries = $this->sendGet($query);
        if (!is_array($timeEntries)) {
            dd($query);
        }
        return $timeEntries;
    }

    public function getLabels() {
        $query = "https://backbone.proofhub.com/api/v3/labels";
        //dd($query);
        $labels = $this->sendGet($query);
        $resultLabels = [];
        foreach ($labels as $label) {
            $label["hours"] = 0;
            $label["cost"] = 0;
            $label["people"] = [];
            $label["projects"] = [];
            unset($label["color"]);
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

    public function getLabel($label, $labels) {
        foreach ($labels as $item) {
            if ($label == $item["id"]) {
                return $item;
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

    public function writeFile($data, $title) {

        Excel::create($title, function($excel) use($data, $title) {

            $excel->setTitle($title);
            // Chain the setters
            $excel->setCreator('Hoovert Arredondo')
                    ->setCompany('Backbone Technology');
            // Call them separately
            $excel->setDescription('This report is clasified');
            foreach ($data as $page) {
                echo $title . PHP_EOL;
                echo $page["name"] . PHP_EOL;
                foreach ($page["rows"] as $key => $value) {
                    if ($page["rows"][$key]) {
                        if (array_key_exists("labels", $page["rows"][$key])) {
                            unset($page["rows"][$key]["labels"]);
                        }
                        if (array_key_exists("projects", $page["rows"][$key])) {
                            unset($page["rows"][$key]["projects"]);
                        }
                        if (array_key_exists("people", $page["rows"][$key])) {
                            unset($page["rows"][$key]["people"]);
                        }
                    }
                }
                $excel->sheet(substr($page["name"], 0, 30), function($sheet) use($page) {
                    $sheet->fromArray($page["rows"], null, 'A1', true);
                });
            }
        })->store('xlsx');
    }

    public function getProjects($copy,$type) {
        $projects = [
            [
                "name" => "TaxPayer Redesign and Dev",
                "budget" => "0",
                "country" => "COL",
                "type" => self::PRODUCCION,
                "price" => "Retail",
                "code" => "1420446568",
                "rows" => $copy,
            ], [
                "name" => "Wellness",
                "budget" => "15000000",
                "country" => "COL",
                "type" => self::PRODUCCION,
                "price" => "Retail",
                "code" => "2727439403",
                "rows" => $copy,
            ], [
                "name" => "Klip(Accvent)",
                "budget" => "15000000",
                "country" => "COL",
                "type" => self::PRODUCCION,
                "price" => "Retail",
                "code" => "2801511014",
                "rows" => $copy,
            ], [
                "name" => "igastoresbc",
                "budget" => "0",
                "country" => "COL",
                "type" => self::CANADA,
                "price" => "Retail",
                "code" => "2555537362",
                "rows" => $copy,
            ], [
                "name" => "Cerromatoso",
                "budget" => "50000",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "2052138062",
                "rows" => $copy,
            ], [
                "name" => "sheldon consulting",
                "budget" => "50000",
                "country" => "COL",
                "type" => self::CANADA,
                "price" => "Retail",
                "code" => "1752852931",
                "rows" => $copy,
            ], [
                "name" => "Afydi",
                "budget" => "50000",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "1767982047",
                "rows" => $copy,
            ], [
                "name" => "Misi",
                "budget" => "50000",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "1793993272",
                "rows" => $copy,
            ], [
                "name" => "Icapital",
                "budget" => "0",
                "country" => "COL",
                "type" => self::CANADA,
                "price" => "Retail",
                "code" => "1479511181",
                "rows" => $copy,
            ], [
                "name" => "Teck",
                "budget" => "0",
                "country" => "COL",
                "type" => self::CANADA,
                "price" => "Retail",
                "code" => "1483921014",
                "rows" => $copy,
            ], [
                "name" => "Prasino",
                "budget" => "0",
                "country" => "COL",
                "type" => self::PRODUCCION,
                "price" => "Retail",
                "code" => "2382102056",
                "rows" => $copy,
            ], [
                "name" => "Blulogistics",
                "budget" => "0",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "2267161477",
                "rows" => $copy,
            ], [
                "name" => "Volo",
                "budget" => "0",
                "country" => "COL",
                "type" => self::CANADA,
                "price" => "Retail",
                "code" => "1849937081",
                "rows" => $copy,
            ], [
                "name" => "Universidad Rosario",
                "budget" => "0",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "1846653453",
                "rows" => $copy,
            ], [
                "name" => "Kava",
                "budget" => "0",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "1833844586",
                "rows" => $copy,
            ], [
                "name" => "Seguros Mundial",
                "budget" => "0",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "1768117734",
                "rows" => $copy,
            ], [
                "name" => "CVU",
                "budget" => "0",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "1753816309",
                "rows" => $copy,
            ],
            [
                "name" => "Kannabis",
                "budget" => "0",
                "country" => "COL",
                "price" => "Retail",
                "type" => self::PRODUCCION,
                "code" => "2312725220",
                "rows" => $copy,
            ], [
                "name" => "BVC",
                "budget" => "0",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "1891932252",
                "rows" => $copy,
            ], [
                "name" => "Auvenir",
                "budget" => "0",
                "country" => "COL",
                "type" => self::CANADA,
                "price" => "Retail",
                "code" => "2502782201",
                "rows" => $copy,
            ], [
                "name" => "Archies",
                "budget" => "0",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "1714087114",
                "rows" => $copy,
            ],
            [
                "name" => "Primus",
                "budget" => "0",
                "country" => "COL",
                "type" => self::PRODUCCION,
                "price" => "Retail",
                "code" => "2400881156",
                "rows" => $copy,
            ],
            [
                "name" => "Daportare",
                "budget" => "0",
                "country" => "COL",
                "type" => self::PRODUCCION,
                "price" => "Retail",
                "code" => "2489878353",
                "rows" => $copy,
            ],
            [
                "name" => "Next Conn-Infrastructure",
                "budget" => "0",
                "country" => "COL",
                "type" => self::PRODUCCION,
                "price" => "Retail",
                "code" => "2581819961",
                "rows" => $copy,
            ],
            [
                "name" => "Xtech",
                "budget" => "0",
                "country" => "COL",
                "type" => self::PRODUCCION,
                "price" => "Retail",
                "code" => "2349279336",
                "rows" => $copy,
            ],
            [
                "name" => "NOVEDADES GUILLERS",
                "budget" => "50000",
                "country" => "COL",
                "type" => self::PRODUCCION,
                "price" => "Retail",
                "code" => "1871891261",
                "rows" => $copy,
            ],
            [
                "name" => "Magic Flavors",
                "budget" => "30000000",
                "country" => "COL",
                "type" => self::PRODUCCION,
                "price" => "Retail",
                "code" => "2541330918",
                "rows" => $copy,
            ],
            [
                "name" => "Cano",
                "budget" => "5000",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "2234243776",
                "rows" => $copy,
            ],
            [
                "name" => "CBC",
                "budget" => "2400000",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "1714114251",
                "rows" => $copy,
            ],
            [
                "name" => "Ezpot",
                "budget" => "5000000",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "1498154595",
                "rows" => $copy,
            ],
            [
                "name" => "Juan Valdez",
                "budget" => "5500000",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "1753802741",
                "rows" => $copy,
            ],
            [
                "name" => "Support Celsia",
                "budget" => "2500000",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "2349062236",
                "rows" => $copy,
            ],
            [
                "name" => "Celsia epsa",
                "budget" => "2500000",
                "country" => "COL",
                "type" => self::PRODUCCION,
                "price" => "Retail",
                "code" => "1934931508",
                "rows" => $copy,
            ],
            [
                "name" => "Papa Johns",
                "budget" => "2400000",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "1753924859",
                "rows" => $copy,
            ],
            [
                "name" => "Super de alimentos",
                "budget" => "0",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "1422726112",
                "rows" => $copy,
            ],
            [
                "name" => "Dilucca",
                "budget" => "0",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "1421925558",
                "rows" => $copy,
            ],
            [
                "name" => "BYC en casa",
                "budget" => "500000",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "1739080686",
                "rows" => $copy,
            ],
            [
                "name" => "Fonnegra",
                "budget" => "1200000",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "1767995616",
                "rows" => $copy,
            ], [
                "name" => "Internal Management",
                "budget" => "1200000",
                "country" => "COL",
                "type" => self::INTERNO,
                "price" => "Retail",
                "code" => "1753870584",
                "rows" => $copy,
            ], [
                "name" => "Internal Management 2019",
                "budget" => "1200000",
                "country" => "COL",
                "type" => self::INTERNO,
                "price" => "Retail",
                "code" => "2711279064",
                "rows" => $copy,
            ], [
                "name" => "El techo",
                "budget" => "50000",
                "country" => "COL",
                "type" => self::PRODUCCION,
                "price" => "Retail",
                "code" => "1889991926",
                "rows" => $copy,
            ],
            [
                "name" => "Niños en movimiento",
                "budget" => "1200000",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "2727778621",
                "rows" => $copy,
            ],
            [
                "name" => "Chambar",
                "budget" => "0",
                "country" => "COL",
                "type" => self::CANADA,
                "price" => "Retail",
                "code" => "1481790725",
                "rows" => $copy,
            ],
            [
                "name" => "DSRF",
                "budget" => "0",
                "country" => "COL",
                "type" => self::PRODUCCION,
                "price" => "Retail",
                "code" => "1409523753",
                "rows" => $copy,
            ],
            [
                "name" => "Sales Proposals",
                "budget" => "0",
                "country" => "COL",
                "type" => self::INTERNO,
                "price" => "Retail",
                "code" => "2727195166",
                "rows" => $copy,
            ],
            [
                "name" => "Vilaseca",
                "budget" => "0",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "1779963222",
                "rows" => $copy,
            ],
            [
                "name" => "Dial",
                "budget" => "0",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "1637613840",
                "rows" => $copy,
            ],
            [
                "name" => "Agency",
                "budget" => "0",
                "country" => "COL",
                "type" => self::INTERNO,
                "price" => "Retail",
                "code" => "1694100398",
                "rows" => $copy,
            ],
            [
                "name" => "Support Daportare",
                "budget" => "0",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "2065259008",
                "rows" => $copy,
            ],
            [
                "name" => "Marketing Xpr",
                "budget" => "0",
                "country" => "COL",
                "type" => self::INTERNO,
                "price" => "Retail",
                "code" => "2646379904",
                "rows" => $copy,
            ],
            [
                "name" => "Balance ecuador",
                "budget" => "0",
                "country" => "COL",
                "type" => self::PRODUCCION,
                "price" => "Retail",
                "code" => "2547043347",
                "rows" => $copy,
            ]
        ];
        if($type){
            $finalResult = [];
            foreach ($projects as $item) {
                if($item["type"]==$type){
                    array_push($finalResult, $item);
                }
            }
            return $finalResult;
            
        } else {
            return $projects;
        }
        
    }

    public function getSummary($labels,$people,$projects,$full,$ignoreDate,$name) {
        $date1 = self::START_DATE;
        $date2 = self::END_DATE;
        $dateTimestamp1 = strtotime($date1);
        $dateTimestamp2 = strtotime($date2);
        $results = [];
        $summary = [];
        $summary["rows"] = [];
        $totalHours = 0;
        $totalCost = 0;
        foreach ($projects as $project) {
            $timeEntries = $this->getProjectTimeSheets($project);
            $projectPeople = $people;
            $projectLabels = $labels;
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
            $estimatedMissingBudget = $totalMissingHours*self::COSTO_HORA_PROMEDIO;
            $totalPendingMilestones = 0;
            $projectMilestones = [];
            $totalCompletedMilestones = 0;
            $totalMissingBudget = 0;
            $totalPaidBudget = 0;
            $events = $this->getProjectEvents($project);
            foreach ($events as $event) {
                if ($event["milestone"]) {
                    if ($event["completed"]) {
                        $totalCompletedMilestones++;
                        if ($event["description"]) {
                            $totalPaidBudget += intval($event["description"]);
                        }
                    } else {
                        $projectMilestone = [
                            "Title" => $event["title"],
                            "Pago" => $event["description"],
                            "Start" => $event["start"],
                            "End" => $event["end"]
                        ];
                        array_push($projectMilestones, $projectMilestone);
                        $totalPendingMilestones++;
                        if ($event["description"]) {
                            $totalMissingBudget += intval($event["description"]);
                        }
                    }
                }
            }

            foreach ($timeEntries as $timeEntry) {
                $foundCreator = false;
                $timeEntryTimestamp = strtotime($timeEntry["date"]);
                if (($timeEntryTimestamp > $dateTimestamp1 && $timeEntryTimestamp < $dateTimestamp2)||$ignoreDate) {
                    $task = $this->getTimeEntryTask($timeEntry, $tasks);
                    foreach ($projectPeople as &$person) {
                        if ($timeEntry["creator"]["id"] == $person["id"]) {
                            $taskLabels = $task["labels"];
                            $isBillable = true;
                            $foundCreator = true;
                            if ($taskLabels) {
                                foreach ($taskLabels as $taskLabel) {
                                    $foundPerson = false;
                                    foreach ($person["labels"] as &$personLabel) {
                                        if ($personLabel["id"] == $taskLabel) {
                                            $foundPerson = true;
                                            $personLabel["hours"] += ($timeEntry["logged_hours"] + ($timeEntry["logged_mins"] / 60));
                                            if (self::IS_ADMIN) {
                                                $personLabel["cost"] += $person[self::TYPE_COST] * ($timeEntry["logged_hours"] + ($timeEntry["logged_mins"] / 60));
                                            }
                                        }
                                    }
                                    if (!$foundPerson) {
                                        $completeLabel = $this->getLabel($taskLabel, $labels);
                                        $newPersonLabel = [];
                                        $newPersonLabel["name"] = $completeLabel["name"];
                                        $newPersonLabel["id"] = $completeLabel["id"];
                                        $newPersonLabel["person_name"] = $person["name"];
                                        $newPersonLabel["person_id"] = $person["id"];
                                        $newPersonLabel["hours"] = ($timeEntry["logged_hours"] + ($timeEntry["logged_mins"] / 60));
                                        if (self::IS_ADMIN) {
                                            $newPersonLabel["cost"] = $person[self::TYPE_COST] * ($timeEntry["logged_hours"] + ($timeEntry["logged_mins"] / 60));
                                        }

                                        array_push($person["labels"], $newPersonLabel);
                                    }

                                    if ($taskLabel == "2974484984") {
                                        $isBillable = false;
                                    }
                                }
                            }
                            $person["hours"] += ($timeEntry["logged_hours"] + ($timeEntry["logged_mins"] / 60));
                            //dd($person);
                            if (self::IS_ADMIN) {
                                $person["total_cost"] = $person["hours"] * $person[self::TYPE_COST];
                            }
                        }
                    }

                    foreach ($projectLabels as &$label) {
                        $hours = 0;
                        $taskLabels = $task["labels"];
                        if ($taskLabels) {
                            foreach ($taskLabels as $taskLabel) {
                                if ($label["id"] == $taskLabel) {
                                    $label["hours"] += ($timeEntry["logged_hours"] + ($timeEntry["logged_mins"] / 60));
                                }
                            }
                        }
                    }
                }
            }

            $projectPersonLabels = [];
            foreach ($projectPeople as $key => $person) {
                if ($person["hours"] == 0) {
                    unset($projectPeople[$key]);
                } else {
                    unset($projectPeople[$key]["cost_us"]);
                    unset($projectPeople[$key]["retail_us"]);
                    if (!self::IS_ADMIN) {
                        unset($projectPeople[$key]["cost"]);
                        unset($projectPeople[$key]["retail"]);
                    }
                    foreach ($person["labels"] as $item => $value) {

                        foreach ($projectLabels as &$projectLabel) {
                            if ($projectLabel["id"] == $value["id"]) {
                                if (self::IS_ADMIN) {
                                    $projectLabel["cost"] += $value["cost"];
                                }

                                $projectLabel["hours"] += $value["hours"];
                            }
                        }
                        unset($value["id"]);
                        unset($value["person_id"]);
                        array_push($projectPersonLabels, $value);
                    }
                }
            }
            $projectPeople = array_map("unserialize", array_unique(array_map("serialize", $projectPeople)));
            foreach ($projectLabels as $key => $projectLabel) {
                if ($projectLabel["hours"] == 0) {
                    unset($projectLabels[$key]);
                }
                unset($projectLabels[$key]["color"]);
                unset($projectLabels[$key]["id"]);
            }
            $project['people'] = $projectPeople;
            $title = array([
                    "name" => "label name",
                    "hours" => "hours",
                    "cost" => "cost"
            ]);
            $title2 = array([
                    "name" => "label name",
                    "hours" => "hours",
                    "person_name" => "Person",
                    "cost" => "Cost",
            ]);
            $title3 = array([
                    "name" => "Milestone",
                    "hours" => "Pago",
                    "person_name" => "Start",
                    "cost" => "End",
            ]);

            $project['personlabels'] = $projectPersonLabels;
            $project['labels'] = $projectLabels;
            if (count($projectPersonLabels) > 0) {
                $project["rows"] = array_merge($project['people'], $title, $project['labels'], $title2, $projectPersonLabels, $title3, $projectMilestones);
            } else {
                $project["rows"] = $project['people'];
            }

            $projectResults = $this->calculateTotalsProject($projectPeople);
            $projectData = [
                "Nombre" => $project["name"],
                "Presupuesto mensual" => $project["budget"],
                "Horas" => $projectResults["Hours"],
                "Presupuesto consumido" => $projectResults["Consumed"],
                "Facturado" => "",
                /* "billable" => $projectResults["billable"],
                  "billable_cost" => $projectResults["billable_cost"],
                  "non_billable_cost" => $projectResults["non_billable_cost"],
                  "non_billable" => $projectResults["non_billable"], */
                "Tareas Abiertas" => $totalPendingTasks,
                "Tiempo p terminacion" => $totalMissingHours,
                "Presupuesto estimado faltante" => $estimatedMissingBudget,
                "Milestones Abiertos" => $totalPendingMilestones,
                "Presupuesto por recoger" => $totalMissingBudget,
                "Milestones Cerrados" => $totalCompletedMilestones,
                "Presupuesto Recogido" => $totalPaidBudget,
            ];
            $totalHours += $projectResults["Hours"];
            $totalCost += $projectResults["Consumed"];
            if (count($project["rows"]) > 0) {
                array_push($results, $project);
                array_push($summary["rows"], $projectData);
            }
        }
        $summary["Hours"] = $totalHours;
        $summary["name"] = "Summary";
        $summary["Consumed"] = $totalCost;
        //dd($results);
        $daProjects = $results;
        if ($full) {
            $this->calculateTotalsPeople($daProjects, $people);
            $this->calculateTotalsLabels($daProjects, $labels);
        }

        array_unshift($results, $summary);
        $this->writeFile($results, $name);
    }
    
    public function getReport() {
        $labels = $this->getLabels();
        $people = $this->getPeople($labels);
        $copy = $people;
        $projects = $this->getProjects($copy,null);
        $full = true;
        $name = 'Total_mes_' . time();
        $ignoreDate = true;
        $this->getSummary($labels, $people, $projects, $full, $ignoreDate,$name);
        sleep(10);
        $projects = $this->getProjects($copy,self::CUENTAS);
        $full = false;
        $name = 'Total_cuentas_mes_' . time();
        $ignoreDate = false;
        $this->getSummary($labels, $people, $projects, $full, $ignoreDate,$name);
        sleep(10);
        $projects = $this->getProjects($copy,self::PRODUCCION);
        $full = false;
        $name = 'Total_produccion_mes_' . time();
        $ignoreDate = true;
        $this->getSummary($labels, $people, $projects, $full, $ignoreDate,$name);
    }

    private function calculateTotalsPeople($results, $people) {
        foreach ($results as $project) {
            if (count($project["rows"]) > 0) {
                $projectPeople = $project['people'];
                foreach ($projectPeople as $personProject) {
                    foreach ($people as &$person) {
                        if ($person["id"] == $personProject["id"]) {
                            $person["hours"] += $personProject["hours"];
                            $personProject["name"] = $project["name"];
                            unset($personProject["id"]);
                            unset($personProject["projects"]);

                            foreach ($personProject["labels"] as $personProjectLabel) {
                                $pplfound = false;
                                foreach ($person["labels"] as &$personLabel) {
                                    if ($personLabel["id"] == $personProjectLabel["id"]) {
                                        $pplfound = true;
                                        $personLabel["hours"] += $personProjectLabel["hours"];
                                        if (self::IS_ADMIN) {
                                            $personLabel["cost"] += $personProjectLabel["cost"];
                                            
                                        }
                                    }
                                }
                                if (!$pplfound) {
                                    array_push($person["labels"], $personProjectLabel);
                                }
                            }
                            unset($personProject["labels"]);
                            array_push($person["projects"], $personProject);
                            break;
                        }
                    }
                }
            }
        }
        $finalResults = [];
        $finalResults["name"] = "Resumen";
        $finalResults["rows"] = [];
        $totalResults = [];
        $title = array([
                "name" => "label name",
                "person_name" => "Person",
                "hours" => "hours",
                "cost" => "cost"
        ]);
        foreach ($people as &$finalPerson) {
            if ($finalPerson["hours"] > 0) {
                $resume = [];
                $resume["name"] = $finalPerson["name"];
                $resume["hours"] = $finalPerson["hours"];
                $resume["esperado"] = (self::DIAS_HABILES*self::MIN_HORAS_DIARIAS*$finalPerson["esperado"])/100;
                array_push($finalResults["rows"], $resume);
                $resultsPerson = [];
                $resultsPerson["name"] = $finalPerson["name"];
                foreach ($finalPerson['projects'] as $key => $value1) {
                    unset($finalPerson['projects'][$key]["cost"]);
                    unset($finalPerson['projects'][$key]["retail"]);
                    if (!self::IS_ADMIN) {
                        unset($finalPerson['projects'][$key]["total_cost"]);
                    }
                }

                foreach ($finalPerson['labels'] as $key => $value1) {
                    unset($finalPerson['labels'][$key]["id"]);
                    unset($finalPerson['labels'][$key]["person_id"]);
                }
                $resultsPerson["rows"] = array_merge($finalPerson['projects'], $title, $finalPerson['labels']);
                array_push($totalResults, $resultsPerson);
            }
        }
        //dd($totalResults);
        array_unshift($totalResults, $finalResults);
        $this->writeFile($totalResults, 'People_' . time());
    }

    private function calculateTotalsLabels($results, $labels) {
        foreach ($labels as &$label) {
            foreach ($results as $project) {
                $projectLabel = [];
                $projectLabel["name"] = $project['name'];
                $projectLabel["hours"] = 0;
                $projectLabel["cost"] = 0;
                if (count($project["rows"]) > 0) {
                    $projectPeople = $project['people'];
                    foreach ($projectPeople as $personProject) {
                        foreach ($personProject["labels"] as $personProjectLabel) {

                            if ($label["id"] == $personProjectLabel["id"]) {
                                $label["hours"] += $personProjectLabel["hours"];
                                if (self::IS_ADMIN) {
                                    $label["cost"] += $personProjectLabel["cost"];
                                    $projectLabel["cost"] += $personProjectLabel["cost"];
                                }
                                $projectLabel["hours"] += $personProjectLabel["hours"];
                                array_push($label["people"], $personProjectLabel);
                            }
                        }
                    }
                    array_push($label["projects"], $projectLabel);
                }
            }
        }
        $finalResults = [];
        $finalResults["name"] = "Resumen";
        $finalResults["rows"] = [];
        $totalResults = [];
        $title = array([
                "name" => "label name",
                "person_name" => "Person",
                "hours" => "hours",
                "cost" => "Cost"
        ]);
        foreach ($labels as &$finalLabel) {
            if ($finalLabel["hours"] > 0) {
                $resume = [];
                $resume["name"] = $finalLabel["name"];
                $resume["hours"] = $finalLabel["hours"];
                if (self::IS_ADMIN) {
                    $resume["cost"] = $finalLabel["cost"];
                }
                array_push($finalResults["rows"], $resume);
                $resultsPerson = [];
                $resultsPerson["name"] = $finalLabel["name"];
                /* foreach ($finalLabel['projects'] as $key => $value1) {
                  unset($finalLabel['projects'][$key]["cost"]);
                  unset($finalLabel['projects'][$key]["retail"]);
                  unset($finalLabel['projects'][$key]["total_cost"]);
                  } */
                foreach ($finalLabel['people'] as $key => $value1) {
                    unset($finalLabel['people'][$key]["id"]);
                    unset($finalLabel['people'][$key]["person_id"]);
                }
                $resultsPerson["rows"] = array_merge($finalLabel['projects'], $title, $finalLabel['people']);
                array_push($totalResults, $resultsPerson);
            }
        }
        //dd($totalResults);
        array_unshift($totalResults, $finalResults);
        $this->writeFile($totalResults, 'Labels_' . time());
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
                "id" => $resultsPerson['id'],
                "hours" => 0,
                "esperado" => 90,
                "faltan" => 0,
                //"billable" => 0,
                // "non_billable" => 0,
                "total_cost" => 0,
                // "billable_cost" => 0,
                // "non_billable_cost" => 0,
                "labels" => [],
                "projects" => [],
            ];
            if ($resultsPerson['id'] == "1871117844") {
                //andres
                $saveperson['cost'] = 57691;
                $saveperson['retail'] = 71927;
                $saveperson['cost_us'] = 19.23;
                $saveperson['retail_us'] = 22.73;
            } else if ($resultsPerson['id'] == "4232277661") {
                //Deyvid
                $saveperson['cost'] = 26580;
                $saveperson['retail'] = 33139;
                $saveperson['cost_us'] = 8.86;
                $saveperson['retail_us'] = 10.47;
            } else if ($resultsPerson['id'] == "3749231432") {
                //Fabian Horta
                $saveperson['cost'] = 49916;
                $saveperson['retail'] = 62233;
                $saveperson['cost_us'] = 16.64;
                $saveperson['retail_us'] = 19.66;
            } else if ($resultsPerson['id'] == "1856477202") {
                //Fabian Vargas
                $saveperson['cost'] = 41412;
                $saveperson['retail'] = 51631;
                $saveperson['cost_us'] = 13.80;
                $saveperson['retail_us'] = 16.31;
            } else if ($resultsPerson['id'] == "2133970978") {
                //Gobert Perdomo
                $saveperson['cost'] = 30887;
                $saveperson['retail'] = 38508;
                $saveperson['cost_us'] = 10.30;
                $saveperson['retail_us'] = 12.17;
            } else if ($resultsPerson['id'] == "1923370963") {
                //Juan Arredondo
                $saveperson['cost'] = 29411;
                $saveperson['retail'] = 36668;
                $saveperson['cost_us'] = 9.8;
                $saveperson['retail_us'] = 11.6;
            } else if ($resultsPerson['id'] == "2936451878") {
                //Manuel Correa
                $saveperson['cost'] = 41940;
                $saveperson['retail'] = 52288;
                $saveperson['cost_us'] = 13.98;
                $saveperson['retail_us'] = 16.52;
            } else if ($resultsPerson['id'] == "1923533788") {
                //Victor Gil
                $saveperson['cost'] = 30756;
                $saveperson['retail'] = 38345;
                $saveperson['cost_us'] = 10.25;
                $saveperson['retail_us'] = 12.12;
            } else if ($resultsPerson['id'] == "1849041546") {
                //Hoovert
                $saveperson['cost'] = 111154;
                $saveperson['retail'] = 138581;
                $saveperson['esperado'] = 50;
                $saveperson['cost_us'] = 37.05;
                $saveperson['retail_us'] = 43.79;
            } else if ($resultsPerson['id'] == "2316429479") {
                //Julian
                $saveperson['cost'] = 26580;
                $saveperson['retail'] = 33139;
                $saveperson['cost_us'] = 8.86;
                $saveperson['retail_us'] = 10.47;
            } else if ($resultsPerson['id'] == "1856450064") {
                //Jucaro
                $saveperson['cost'] = 51451;
                $saveperson['retail'] = 64147;
                $saveperson['cost_us'] = 17.15;
                $saveperson['retail_us'] = 20.27;
            } else if ($resultsPerson['id'] == "4173918621") {
                //Sabine
                $saveperson['cost'] = 36600;
                $saveperson['retail'] = 45631;
                $saveperson['cost_us'] = 12.20;
                $saveperson['retail_us'] = 14.42;
            } else if ($resultsPerson['id'] == "4472620297") {
                //Neiderson
                $saveperson['cost'] = 26580;
                $saveperson['retail'] = 33139;
                $saveperson['cost_us'] = 8.86;
                $saveperson['retail_us'] = 10.47;
            } else if ($resultsPerson['id'] == "3464709062") {
                //Karina Rodriguez
                $saveperson['cost'] = 41940;
                $saveperson['retail'] = 52288;
                $saveperson['cost_us'] = 13.98;
                $saveperson['retail_us'] = 16.52;
            } else if ($resultsPerson['id'] == "1856110846") {
                //David Mendez
                $saveperson['cost'] = 81418;
                $saveperson['retail'] = 101508;
                $saveperson['esperado'] = 50;
                $saveperson['cost_us'] = 27.14;
                $saveperson['retail_us'] = 32.07;
            } else if ($resultsPerson['id'] == "2304516147") {
                //Alejandra Archila
                $saveperson['cost'] = 30726;
                $saveperson['retail'] = 38308;
                $saveperson['cost_us'] = 10.24;
                $saveperson['retail_us'] = 12.10;
            } else if ($resultsPerson['id'] == "3216537278") {
                //Laura Aires
                $saveperson['cost'] = 36600;
                $saveperson['retail'] = 45631;
                $saveperson['cost_us'] = 12.20;
                $saveperson['retail_us'] = 14.42;
            } else if ($resultsPerson['id'] == "4194692322") {
                //Paula Lozano
                $saveperson['cost'] = 43258;
                $saveperson['retail'] = 53932;
                $saveperson['cost_us'] = 14.42;
                $saveperson['esperado'] = 50;
                $saveperson['retail_us'] = 17.04;
            } else if ($resultsPerson['id'] == "4151530242") {
                //Juliana Acosta
                $saveperson['cost'] = 43258;
                $saveperson['retail'] = 53932;
                $saveperson['esperado'] = 50;
                $saveperson['cost_us'] = 14.42;
                $saveperson['retail_us'] = 17.04;
            } else if ($resultsPerson['id'] == "2791958639") {
                //Oscar Gil
                $saveperson['cost'] = 52770;
                $saveperson['retail'] = 65791;
                $saveperson['esperado'] = 50;
                $saveperson['cost_us'] = 17.59;
                $saveperson['retail_us'] = 20.79;
            } else if ($resultsPerson['id'] == "2213225838") {
                //Edinson Loaisa
                $saveperson['cost'] = 33580;
                $saveperson['retail'] = 41866;
                $saveperson['cost_us'] = 11.19;
                $saveperson['retail_us'] = 13.23;
            } else if ($resultsPerson['id'] == "4155953643") {
                //Maria luisa
                $saveperson['cost'] = 33580;
                $saveperson['retail'] = 41866;
                $saveperson['cost_us'] = 11.19;
                $saveperson['retail_us'] = 13.23;
            } else {
                $saveperson['cost'] = 50000;
                $saveperson['retail'] = 60000;
            }
            array_push($orderedPeople, $saveperson);
        }
        return $orderedPeople;
    }

}
