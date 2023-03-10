<?php

namespace App\Services;

use App\Services\GoogleSheets;
use App\Mail\StoreReports;
use Illuminate\Support\Facades\Mail;
use App\Jobs\ProofhubTotalsJob;
use App\Exports\ArrayMultipleSheetExport;
use App\Jobs\ProofhubSummaryJob;
use Excel;

class Proofhub {

    const TYPE_COST = 'retail';
    const START_DATE = '2020-03-01';
    const END_DATE = '2025-03-01';
    const PRODUCCION = 'Produccion';
    const CUENTAS = 'Cuentas';
    const CANADA = 'Canada';
    const INTERNO = 'Interno';
    const IS_ADMIN = false;
    const DIAS_HABILES = 19;
    const MIN_HORAS_DIARIAS = 7;
    const COSTO_HORA_PROMEDIO = 54887;

    /**
     * The EditAlert implementation.
     *
     */
    protected $googleSheets;

    /**
     * Create a new class instance.
     *
     * @param  EventPusher  $pusher
     * @return void
     */
    public function __construct(GoogleSheets $googleSheets) {
        $this->googleSheets = $googleSheets;
    }

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
        if (array_key_exists('status', $response)) {
            sleep(11);
            return $this->sendGet($query);
        } else {
            return $response;
        }
    }

    public function getProjectTimeSheets($project) {
        $query = "https://backbone.proofhub.com/api/v3/projects/" . $project['code'] . "/timesheets";
        //dd($query);
        $sheets = $this->sendGet($query);
        $totalTimeEntries = [];
        foreach ($sheets as $sheet) {
            if (is_array($sheet)) {
                $results = $this->getTimeSheetTime($project, $sheet['id']);
                if (is_array($results)) {
                    $totalTimeEntries = array_merge($totalTimeEntries, $results);
                }
            }
        }
        return $totalTimeEntries;
    }

    private static function cmp($a, $b) {
        $dateTimestamp1 = strtotime($a['End']);
        $dateTimestamp2 = strtotime($b['End']);
        if ($dateTimestamp1 == $dateTimestamp2) {
            return 0;
        }
        return ($dateTimestamp1 < $dateTimestamp2) ? -1 : 1;
    }

    public function getProjectTaskLists($project) {
        $query = "https://backbone.proofhub.com/api/v3/projects/" . $project['code'] . "/todolists";
        //dd($query);
        $lists = $this->sendGet($query);
        $totalTasks = [];
        foreach ($lists as $list) {
            if (is_array($list)) {
                $results = $this->getTasksList($project, $list['id']);
                if (is_array($results)) {
                    $totalTasks = array_merge($totalTasks, $results);
                }
            }
        }
        return $totalTasks;
    }

    public function getProjectEvents($project, $ignoreDate) {
        if ($ignoreDate) {
            $query = "https://backbone.proofhub.com/api/v3/projects/" . $project['code'] . "/events?view=milestones";
        } else {
            $query = "https://backbone.proofhub.com/api/v3/projects/" . $project['code'] . "/events?view=milestones&startDate=" . self::START_DATE . "&endDate=" . self::END_DATE;
        }
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
        $project["Hours"] = number_format((float) $totalHours, 2, ',', '');
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

    private function getPerson($person_id, &$people) {
        foreach ($people as &$person) {
            if ($person["id"] == $person_id) {
                return $person;
            }
        }
    }

    public function writeFile($data, $title) {
        foreach ($data as $page) {
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
        }
        $file = Excel::store(new ArrayMultipleSheetExport($data), "exports/" . $title . ".xls", "local");
        $path = 'exports/' . $title . ".xls";
        Mail::to("hoovert@backbone.digital")->send(new StoreReports($path));
    }

    public function getProjects($copy, $type) {
        $projects = [
//            [
//                "name" => "Wellness",
//                "budget" => "15000000",
//                "country" => "COL",
//                "type" => self::PRODUCCION,
//                "price" => "Retail",
//                "code" => "2727439403",
//                "rows" => $copy,
//            ],
//              [
//              "name" => "La nuit",
//              "budget" => "15000000",
//              "country" => "COL",
//              "type" => self::PRODUCCION,
//              "price" => "Retail",
//              "code" => "2836708259",
//              "rows" => $copy,
//              ], 
            [
                "name" => "La nuit ongoing",
                "budget" => "3000000",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "2925895418",
                "rows" => $copy,
            ],
//            [
//                "name" => "Klip(Accvent)",
//                "budget" => "15000000",
//                "country" => "COL",
//                "type" => self::PRODUCCION,
//                "price" => "Retail",
//                "code" => "2801511014",
//                "rows" => $copy,
//            ],
            [
                "name" => "igastoresbc",
                "budget" => "0",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "2555537362",
                "rows" => $copy,
            ],
            [
                "name" => "Eighth Avenue",
                "budget" => "0",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "1969531729",
                "rows" => $copy,
            ],
            [
                "name" => "Cerromatoso",
                "budget" => "50000",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "2052138062",
                "rows" => $copy,
            ],
            [
                "name" => "Afydi",
                "budget" => "50000",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "1767982047",
                "rows" => $copy,
            ],
            [
                "name" => "Misi",
                "budget" => "50000",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "1793993272",
                "rows" => $copy,
            ],
            [
                "name" => "CUCU",
                "budget" => "50000",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "1466430941",
                "rows" => $copy,
            ],
            [
                "name" => "Icapital",
                "budget" => "0",
                "country" => "COL",
                "type" => self::CANADA,
                "price" => "Retail",
                "code" => "1479511181",
                "rows" => $copy,
            ],
            [
                "name" => "Henkel",
                "budget" => "0",
                "country" => "COL",
                "type" => self::CANADA,
                "price" => "Retail",
                "code" => "3088122966",
                "rows" => $copy,
            ],
            //            [
            //                "name" => "FreshSt",
            //                "budget" => "0",
            //                "country" => "COL",
            //                "type" => self::CANADA,
            //                "price" => "Retail",
            //                "code" => "1490963176",
            //                "rows" => $copy,
            //            ], [
            //                "name" => "Volo",
            //                "budget" => "0",
            //                "country" => "COL",
            //                "type" => self::CANADA,
            //                "price" => "Retail",
            //                "code" => "1849937081",
            //                "rows" => $copy,
            //            ],
            [
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
            ],
            [
                "name" => "Ongoing Development",
                "budget" => "0",
                "country" => "COL",
                "type" => self::INTERNO,
                "price" => "Retail",
                "code" => "1479076983",
                "rows" => $copy,
            ],
            //            [
            //                "name" => "Auvenir",
            //                "budget" => "0",
            //                "country" => "COL",
            //                "type" => self::CANADA,
            //                "price" => "Retail",
            //                "code" => "2502782201",
            //                "rows" => $copy,
            //            ],
            [
                "name" => "Archies",
                "budget" => "0",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "1714087114",
                "rows" => $copy,
            ],
            //            [
            //                "name" => "Primus",
            //                "budget" => "0",
            //                "country" => "COL",
            //                "type" => self::PRODUCCION,
            //                "price" => "Retail",
            //                "code" => "2400881156",
            //                "rows" => $copy,
            //            ],
            [
                "name" => "Daportare",
                "budget" => "0",
                "country" => "COL",
                "type" => self::CUENTAS,
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
            //            [
            //                "name" => "Xtech",
            //                "budget" => "0",
            //                "country" => "COL",
            //                "type" => self::PRODUCCION,
            //                "price" => "Retail",
            //                "code" => "2349279336",
            //                "rows" => $copy,
            //            ],
            //            [
            //                "name" => "NOVEDADES GUILLERS",
            //                "budget" => "50000",
            //                "country" => "COL",
            //                "type" => self::PRODUCCION,
            //                "price" => "Retail",
            //                "code" => "1871891261",
            //                "rows" => $copy,
            //            ],
//              [
//              "name" => "Magic Flavors",
//              "budget" => "30000000",
//              "country" => "COL",
//              "type" => self::PRODUCCION,
//              "price" => "Retail",
//              "code" => "2541330918",
//              "rows" => $copy,
//              ],
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
            ],
            //            [
            //                "name" => "Internal Management",
            //                "budget" => "1200000",
            //                "country" => "COL",
            //                "type" => self::INTERNO,
            //                "price" => "Retail",
            //                "code" => "1753870584",
            //                "rows" => $copy,
            //            ], [
            //                "name" => "Internal Management 2019",
            //                "budget" => "1200000",
            //                "country" => "COL",
            //                "type" => self::INTERNO,
            //                "price" => "Retail",
            //                "code" => "2711279064",
            //                "rows" => $copy,
            //            ],
            [
                "name" => "El techo",
                "budget" => "50000",
                "country" => "COL",
                "type" => self::PRODUCCION,
                "price" => "Retail",
                "code" => "1889991926",
                "rows" => $copy,
            ],
            [
                "name" => "Ni??os en movimiento",
                "budget" => "1200000",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "2727778621",
                "rows" => $copy,
            ],
            //            [
            //                "name" => "Chambar",
            //                "budget" => "0",
            //                "country" => "COL",
            //                "type" => self::CANADA,
            //                "price" => "Retail",
            //                "code" => "1481790725",
            //                "rows" => $copy,
            //            ],
            //            [
            //                "name" => "DSRF",
            //                "budget" => "0",
            //                "country" => "COL",
            //                "type" => self::PRODUCCION,
            //                "price" => "Retail",
            //                "code" => "1409523753",
            //                "rows" => $copy,
            //            ],
            //            [
            //                "name" => "Sales Proposals",
            //                "budget" => "0",
            //                "country" => "COL",
            //                "type" => self::INTERNO,
            //                "price" => "Retail",
            //                "code" => "2727195166",
            //                "rows" => $copy,
            //            ],
            //            [
            //                "name" => "Vilaseca",
            //                "budget" => "0",
            //                "country" => "COL",
            //                "type" => self::PRODUCCION,
            //                "price" => "Retail",
            //                "code" => "1779963222",
            //                "rows" => $copy,
            //            ],
            //            [
            //                "name" => "Dial",
            //                "budget" => "0",
            //                "country" => "COL",
            //                "type" => self::CUENTAS,
            //                "price" => "Retail",
            //                "code" => "1637613840",
            //                "rows" => $copy,
            //            ],
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
            //            [
            //                "name" => "Balance ecuador",
            //                "budget" => "0",
            //                "country" => "COL",
            //                "type" => self::CUENTAS,
            //                "price" => "Retail",
            //                "code" => "2547043347",
            //                "rows" => $copy,
            //            ],
            [
                "name" => "CONSTRUCTORA BOLIVAR",
                "budget" => "0",
                "country" => "COL",
                "type" => self::PRODUCCION,
                "price" => "Retail",
                "code" => "2940400374",
                "rows" => $copy,
            ],
            [
                "name" => "Casa chiqui",
                "budget" => "0",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "3063142963",
                "rows" => $copy,
            ],
//              [
//              "name" => "Rivercol",
//              "budget" => "0",
//              "country" => "COL",
//              "type" => self::PRODUCCION,
//              "price" => "Retail",
//              "code" => "2944945893",
//              "rows" => $copy,
//              ],
            [
                "name" => "Casa Reigner",
                "budget" => "0",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "3347556783",
                "rows" => $copy,
            ],
            [
                "name" => "Dunna",
                "budget" => "0",
                "country" => "COL",
                "type" => self::PRODUCCION,
                "price" => "Retail",
                "code" => "3383378189",
                "rows" => $copy,
            ],
            //                  [
            //                  "name" => "Hipergas",
            //                  "budget" => "0",
            //                  "country" => "COL",
            //                  "type" => self::PRODUCCION,
            //                  "price" => "Retail",
            //                  "code" => "3313974215",
            //                  "rows" => $copy,
            //                  ],https://backbone.proofhub.com/bapp/#overview/3669257430
            //            [
            //                "name" => "Backbone Print",
            //                "budget" => "0",
            //                "country" => "COL",
            //                "type" => self::PRODUCCION,
            //                "price" => "Retail",
            //                "code" => "2803356359",
            //                "rows" => $copy,
            //            ],
            [
                "name" => "CRUX",
                "budget" => "0",
                "country" => "COL",
                "type" => self::PRODUCCION,
                "price" => "Retail",
                "code" => "3669257430",
                "rows" => $copy,
            ],
            [
                "name" => "Kannabis Latam",
                "budget" => "0",
                "country" => "COL",
                "type" => self::PRODUCCION,
                "price" => "Retail",
                "code" => "3402238702",
                "rows" => $copy,
            ],
            [
                "name" => "Kerr Village",
                "budget" => "0",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "1671237114",
                "rows" => $copy,
            ],
            [
                "name" => "SCHWARZKOPF ARGENTINA",
                "budget" => "0",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "3541209474",
                "rows" => $copy,
            ],
            [
                "name" => "Dirty Apron",
                "budget" => "0",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "1497978202",
                "rows" => $copy,
            ],
            [
                "name" => "Carcaicer",
                "budget" => "0",
                "country" => "COL",
                "type" => self::PRODUCCION,
                "price" => "Retail",
                "code" => "3696327015",
                "rows" => $copy,
            ],
            [
                "name" => "Fonnegra Agency",
                "budget" => "0",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "3568862513",
                "rows" => $copy,
            ],
            [
                "name" => "Backbone Website",
                "budget" => "0",
                "country" => "COL",
                "type" => self::PRODUCCION,
                "price" => "Retail",
                "code" => "1523338129",
                "rows" => $copy,
            ],
            [
                "name" => "Volo",
                "budget" => "0",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "1849937081",
                "rows" => $copy,
            ],
            [
                "name" => "Mutante",
                "budget" => "0",
                "country" => "COL",
                "type" => self::PRODUCCION,
                "price" => "Retail",
                "code" => "3715404628",
                "rows" => $copy,
            ],
            [
                "name" => "Henkel Asesores",
                "budget" => "0",
                "country" => "COL",
                "type" => self::PRODUCCION,
                "price" => "Retail",
                "code" => "3669678060",
                "rows" => $copy,
            ],
            [
                "name" => "SCHWARZKOPF COLOMBIA",
                "budget" => "0",
                "country" => "COL",
                "type" => self::PRODUCCION,
                "price" => "Retail",
                "code" => "3698660834",
                "rows" => $copy,
            ],
            [
                "name" => "GTC Ingenieria",
                "budget" => "0",
                "country" => "COL",
                "type" => self::PRODUCCION,
                "price" => "Retail",
                "code" => "3932599037",
                "rows" => $copy,
            ],
            [
                "name" => "Archies Migracion",
                "budget" => "0",
                "country" => "COL",
                "type" => self::PRODUCCION,
                "price" => "Retail",
                "code" => "3980374480",
                "rows" => $copy,
            ],
            [
                "name" => "Olano Ocampo",
                "budget" => "0",
                "country" => "COL",
                "type" => self::PRODUCCION,
                "price" => "Retail",
                "code" => "3755486610",
                "rows" => $copy,
            ],
            [
                "name" => "NEXT LIVING",
                "budget" => "0",
                "country" => "COL",
                "type" => self::PRODUCCION,
                "price" => "Retail",
                "code" => "4029018321",
                "rows" => $copy,
            ],
            [
                "name" => "Henkel - Igora Vital",
                "budget" => "0",
                "country" => "COL",
                "type" => self::PRODUCCION,
                "price" => "Retail",
                "code" => "1574410769",
                "rows" => $copy,
            ],
            [
                "name" => "Scrum Celsia",
                "budget" => "0",
                "country" => "COL",
                "type" => self::PRODUCCION,
                "price" => "Retail",
                "code" => "3962925114",
                "rows" => $copy,
            ],
            [
                "name" => "BALANCE - Fotos 360",
                "budget" => "0",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "3987810136",
                "rows" => $copy,
            ],
            [
                "name" => "SCHWARZKOPF COLOMBIA COLORACI??N",
                "budget" => "0",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "3926723784",
                "rows" => $copy,
            ],
            [
                "name" => "XPR II CI",
                "budget" => "0",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "3926723784",
                "rows" => $copy,
            ],
            [
                "name" => "Fotos P??gina Web NOV 2019",
                "budget" => "0",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "3709760042",
                "rows" => $copy,
            ],
            [
                "name" => "Support Casa chiqui",
                "budget" => "0",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "4033346741",
                "rows" => $copy,
            ],
            [
                "name" => "Balance",
                "budget" => "0",
                "country" => "COL",
                "type" => self::CUENTAS,
                "price" => "Retail",
                "code" => "1754861100",
                "rows" => $copy,
            ],
        ];
        if ($type) {
            $finalResult = [];
            foreach ($projects as $item) {
                if ($item["type"] == $type) {
                    array_push($finalResult, $item);
                }
            }
            return $finalResult;
        } else {
            return $projects;
        }
    }

    public function getSummary($labels, $people, $type, $full, $ignoreDate, $name) {
        $date1 = self::START_DATE;
        $date2 = self::END_DATE;
        $copy = $people;
        $projects = $this->getProjects($copy, $type);
        $dateTimestamp1 = strtotime($date1) - 1000;
        $dateTimestamp2 = strtotime($date2);
        $results = [];
        $globalPendingMilestones = [];
        $summary = [];
        $summary["rows"] = [];
        $summaryTitles = [
            "Nombre",
            "Horas",
            "Presupuesto consumido",
            "Presupuesto mensual",
            "Tareas Abiertas",
            "Tiempo p terminacion",
            "Presupuesto estimado faltante",
            "Milestones Abiertos",
        ];
        $summary["rows"] = [$summaryTitles];
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
                if (is_array($task)) {
                    if (array_key_exists("completed", $task)) {
                        if (!$task["completed"]) {
                            $totalPendingTasks++;
                            $estimate = $task["estimated_hours"] + ($task["estimated_mins"] / 60);
                            $logged = $task["logged_hours"] + ($task["logged_mins"] / 60);
                            if ($estimate > $logged) {
                                $totalMissingHours += $estimate - $logged;
                            }
                        }
                    } else {
                        dd("826");
                    }
                } else {
                    dd($tasks);
                }
            }
            $estimatedMissingBudget = $totalMissingHours * self::COSTO_HORA_PROMEDIO;
            $totalPendingMilestones = 0;
            $projectMilestones = [];
            $totalCompletedMilestones = 0;
            $totalMissingBudget = 0;
            $totalPaidBudget = 0;
            $events = $this->getProjectEvents($project, $ignoreDate);
            foreach ($events as $event) {
                if (is_array($event)) {
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
                            $projectMilestone2 = [
                                "Nombre" => $project["name"],
                                "Horas" => $event["description"],
                                "Presupuesto consumido" => $event["start"],
                                "End" => $event["end"],
                                "Tareas Abiertas" => "",
                                "Tiempo p terminacion" => "",
                                "Presupuesto estimado faltante" => "",
                                "Milestones Abiertos" => "",
                            ];
                            if ($type == self::CUENTAS) {
                                $projectMilestone2["Presupuesto mensual"] = $event["title"];
                            }
                            array_push($globalPendingMilestones, $projectMilestone2);
                            $totalPendingMilestones++;
                            if ($event["description"]) {
                                $totalMissingBudget += intval($event["description"]);
                            }
//                        foreach ($event["assigned"] as $assinee) {
//                            $person = $this->getPerson($assinee, $people);
//                            if ($person) {
//                                if (is_array($person["milestones"])) {
//                                    array_push($person["milestones"], $projectMilestone);
//                                } else {
//                                    dd("884");
//                                }
//                            }
//                        }
                        }
                    }
                }
            }
            usort($globalPendingMilestones, array($this, 'cmp'));
            usort($projectMilestones, array($this, 'cmp'));

            foreach ($timeEntries as $timeEntry) {
                $foundCreator = false;
                if (array_key_exists("date", $timeEntry)) {
                    $timeEntryTimestamp = strtotime($timeEntry["date"]);
                } else {
                    dd("897");
                }

                if (($timeEntryTimestamp > $dateTimestamp1 && $timeEntryTimestamp < $dateTimestamp2) || $ignoreDate) {
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
                        $value["hours"] = number_format((float) $value["hours"], 2, ',', '');
                        if (self::IS_ADMIN) {
                            $value["cost"] = number_format((float) $value["cost"], 2, ',', '');
                        }
                        array_push($projectPersonLabels, $value);
                    }
                }
            }
            $projectPeople = array_map("unserialize", array_unique(array_map("serialize", $projectPeople)));
            foreach ($projectLabels as $key => $projectLabel) {
                if ($projectLabel["hours"] == 0) {
                    unset($projectLabels[$key]);
                } else {
                    unset($projectLabels[$key]["color"]);
                    unset($projectLabels[$key]["id"]);

                    $projectLabels[$key]["hours"] = number_format((float) $projectLabels[$key]["hours"], 2, ',', '');
                    $projectLabels[$key]["cost"] = number_format((float) $projectLabels[$key]["cost"], 2, ',', '');
                    //dd($projectLabels[$key]);
                }
            }
            $project['people'] = $projectPeople;
            $title = array([
                    "name" => "name",
                    "hours" => "hours",
                    "cost" => "cost"
            ]);
            $title1 = array([
                    "name" => "label name",
                    "hours" => "hours",
                    "cost" => "cost"
            ]);
            $title2 = array([
                    "name" => "label name",
                    "hours" => "Person",
                    "person_name" => "hours",
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
            foreach ($projectPeople as $key => $projectPersonInt) {
                unset($projectPeople[$key]["cost"]);
                unset($projectPeople[$key]["retail"]);
                unset($projectPeople[$key]["id"]);
                unset($projectPeople[$key]["esperado"]);
                unset($projectPeople[$key]["faltan"]);
                $projectPeople[$key]["hours"] = number_format((float) $projectPeople[$key]["hours"], 2, ',', '');
                $projectPeople[$key]["total_cost"] = number_format((float) $projectPeople[$key]["total_cost"], 2, ',', '');
            }
            if (count($projectPersonLabels) > 0) {
                $project["rows"] = array_merge($title, $projectPeople, $title1, $project['labels'], $title2, $projectPersonLabels, $title3, $projectMilestones);
            } else {
                $project["rows"] = $projectPeople;
            }

            $projectResults = $this->calculateTotalsProject($project['people']);
            $projectData = [
                "Nombre" => $project["name"],
                "Horas" => $projectResults["Hours"],
                "Presupuesto consumido" => $projectResults["Consumed"],
                "Presupuesto mensual" => $project["budget"],
                //"Facturado" => "",
                /* "billable" => $projectResults["billable"],
                  "billable_cost" => $projectResults["billable_cost"],
                  "non_billable_cost" => $projectResults["non_billable_cost"],
                  "non_billable" => $projectResults["non_billable"], */
                "Tareas Abiertas" => $totalPendingTasks,
                "Tiempo p terminacion" => $totalMissingHours,
                "Presupuesto estimado faltante" => $estimatedMissingBudget,
                "Milestones Abiertos" => $totalPendingMilestones,
                //"Presupuesto por recoger" => $totalMissingBudget,
                "Milestones Cerrados" => $totalCompletedMilestones,
                    //"Presupuesto Recogido" => $totalPaidBudget,
            ];

            $projectResults["Hours"] = str_replace(",", ".", $projectResults["Hours"]);
            if (is_numeric($projectResults["Hours"])) {
                $totalHours += $projectResults["Hours"];
                $totalCost += $projectResults["Consumed"];
            } else {
                dd($projectResults);
            }

            if (count($project["rows"]) > 0) {
                array_push($results, $project);
                array_push($summary["rows"], $projectData);
            }
        }
        if (count($globalPendingMilestones) > 0) {
            $title = array([
                    "Nombre" => "Project",
                    "Presupuesto mensual" => "Milestone",
                    "Horas" => "Pago",
                    "Presupuesto consumido" => "Start",
                    "Facturado" => "End",
                    "Tareas Abiertas" => "",
                    "Tiempo p terminacion" => "",
                    "Presupuesto estimado faltante" => "",
                    "Milestones Abiertos" => "",
                    "Presupuesto por recoger" => "",
                    "Milestones Cerrados" => "",
                    "Presupuesto Recogido" => "",
            ]);
            $summary["rows"] = array_merge($summary["rows"], $title, $globalPendingMilestones);
        }
        //dd($summary["rows"]);
        $summary["Hours"] = $totalHours;
        $summary["name"] = "Summary";
        $summary["Consumed"] = $totalCost;
        //dd($results);
        $daProjects = $results;
        if ($full) {
            $this->calculateTotalsPeople($daProjects, $people);
            //$this->calculateTotalsLabels($daProjects, $labels);
//            dispatch(new ProofhubTotalsJob($daProjects, $people,"people")); 
//            dispatch(new ProofhubTotalsJob($daProjects, $labels,"labels")); 
        }

        array_unshift($results, $summary);
        $this->writeFile($results, $name);
    }

    public function getReport() {
        $labels = $this->getLabels();
        $people = $this->getPeople($labels);
        $copy = $people;
        //$projects = $this->getProjects($copy, null);
        $full = false;
        //return true;
        $name = 'Total_cuentas_mes_' . time();
        $ignoreDate = false;
        $this->getSummary($labels, $people, self::CUENTAS, $full, $ignoreDate, $name);
        //dispatch(new ProofhubSummaryJob($labels, $people, self::CUENTAS, $full, $ignoreDate, $name));
        $full = true;
        $name = 'Total_mes_' . time();
        $ignoreDate = false;
        $this->getSummary($labels, $people, null, $full, $ignoreDate, $name);
        //dispatch(new ProofhubSummaryJob($labels, $people, null, $full, $ignoreDate, $name));
        //$projects = $this->getProjects($copy, self::PRODUCCION);
        $full = false;
        $name = 'Total_produccion_' . time();
        $ignoreDate = true;
        $this->getSummary($labels, $people, self::PRODUCCION, $full, $ignoreDate, $name);
        //dispatch(new ProofhubSummaryJob($labels, $people, self::PRODUCCION, $full, $ignoreDate, $name));
        //$projects = $this->getProjects($copy, self::CUENTAS);
        return true;
        $projects = $this->getProjects($copy, self::CANADA);
        $full = false;
        $name = 'Total_canada_mes_' . time();
        $ignoreDate = false;
        $this->getSummary($labels, $people, self::CANADA, $full, $ignoreDate, $name);
        $projects = $this->getProjects($copy, self::INTERNO);
        $full = false;
        $name = 'Total_internos_' . time();
        $ignoreDate = false;
        $this->getSummary($labels, $people, self::INTERNO, $full, $ignoreDate, $name);
    }

    public function calculateTotalsPeople($results, $people) {
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
        $titleH = array([
                "name" => "Name",
                "hours" => "hours",
                "cost" => "cost"
        ]);
        $finalResults["rows"] = [$titleH];
        $totalResults = [];
        $title = array([
                "name" => "label name",
                "hours" => "hours",
                "cost" => "cost"
        ]);
        foreach ($people as &$finalPerson) {
            if ($finalPerson["hours"] > 0) {
                $resume = [];
                $resume["name"] = $finalPerson["name"];
                $resume["hours"] = number_format((float) $finalPerson["hours"], 2, ',', '');
                $resume["esperado"] = (self::DIAS_HABILES * self::MIN_HORAS_DIARIAS * $finalPerson["esperado"]) / 100;
                array_push($finalResults["rows"], $resume);
                $resultsPerson = [];
                $resultsPerson["name"] = $finalPerson["name"];
                foreach ($finalPerson['projects'] as $key => $value1) {
                    unset($finalPerson['projects'][$key]["cost"]);
                    unset($finalPerson['projects'][$key]["retail"]);
                    unset($finalPerson['projects'][$key]["esperado"]);
                    unset($finalPerson['projects'][$key]["faltan"]);
                    $finalPerson['projects'][$key]["total_cost"] = number_format((float) $finalPerson['projects'][$key]["total_cost"], 2, ',', '');
                    $finalPerson['projects'][$key]["hours"] = number_format((float) $finalPerson['projects'][$key]["hours"], 2, ',', '');
                    if (!self::IS_ADMIN) {
                        unset($finalPerson['projects'][$key]["total_cost"]);
                    }
                }

                foreach ($finalPerson['labels'] as $key => $value1) {
                    unset($finalPerson['labels'][$key]["id"]);
                    unset($finalPerson['labels'][$key]["person_id"]);
                    unset($finalPerson['labels'][$key]["person_name"]);
                    if (self::IS_ADMIN) {
                        $finalPerson['labels'][$key]["cost"] = number_format((float) $finalPerson['labels'][$key]["cost"], 2, ',', '');
                    }

                    $finalPerson['labels'][$key]["hours"] = number_format((float) $finalPerson['labels'][$key]["hours"], 2, ',', '');
                }
                $title2 = array([
                        "name" => "Title",
                        "hours" => "Pago",
                        "cost" => "Start",
                        "person_name" => "End",
                ]);
                if (false) {//if(count($finalPerson['milestones'])>0){
                    $resultsPerson["rows"] = array_merge($titleH, $finalPerson['projects'], $title, $finalPerson['labels'], $title2, $finalPerson['milestones']);
                } else {
                    $resultsPerson["rows"] = array_merge($titleH, $finalPerson['projects'], $title, $finalPerson['labels']);
                }

                array_push($totalResults, $resultsPerson);
            }
        }
        //dd($totalResults);
        array_unshift($totalResults, $finalResults);
        $this->writeFile($totalResults, 'People_' . time());
    }

    public function calculateTotalsLabels($results, $labels) {
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
                "person_name" => "Person",
                "hours" => "hours",
                "cost" => "Cost"
        ]);
        foreach ($labels as &$finalLabel) {
            if ($finalLabel["hours"] > 0) {
                $resume = [];
                $resume["name"] = $finalLabel["name"];
                $resume["hours"] = number_format((float) $finalLabel["hours"], 2, ',', '');
                if (self::IS_ADMIN) {
                    $resume["cost"] = number_format((float) $finalLabel["cost"], 2, ',', '');
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
                    unset($finalLabel['people'][$key]["name"]);
                    if (self::IS_ADMIN) {
                        $finalLabel['people'][$key]["cost"] = number_format((float) $finalLabel['people'][$key]["cost"], 2, ',', '');
                    }

                    $finalLabel['people'][$key]["hours"] = number_format((float) $finalLabel['people'][$key]["hours"], 2, ',', '');
                }
                foreach ($finalLabel['projects'] as $key => $value2) {
                    if (self::IS_ADMIN) {
                        $finalLabel['projects'][$key]["cost"] = number_format((float) $finalLabel['projects'][$key]["cost"], 2, ',', '');
                    }
                    $finalLabel['projects'][$key]["hours"] = number_format((float) $finalLabel['projects'][$key]["hours"], 2, ',', '');
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
            if (array_key_exists("first_name", $resultsPerson)) {
                $resultsPerson['first_name'] . " " . $resultsPerson['last_name'];
            } else {
                dd("1320");
            }

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
                    //  "milestones" => [],
            ];
            if ($resultsPerson['id'] == "1871117844") {
                //andres
                $saveperson['cost'] = 65240;
                $saveperson['retail'] = 71171;
                $saveperson['cost_us'] = 19.23;
                $saveperson['retail_us'] = 22.73;
            } else if ($resultsPerson['id'] == "4232277661") {
                //Deyvid
                $saveperson['cost'] = 30957;
                $saveperson['retail'] = 33771;
                $saveperson['cost_us'] = 8.86;
                $saveperson['retail_us'] = 10.47;
            } else if ($resultsPerson['id'] == "1856477202") {
                //Fabian Vargas
                $saveperson['cost'] = 50186;
                $saveperson['retail'] = 54749;
                $saveperson['cost_us'] = 13.80;
                $saveperson['retail_us'] = 16.31;
            } else if ($resultsPerson['id'] == "2133970978") {
                //Gobert Perdomo
                $saveperson['cost'] = 36930;
                $saveperson['retail'] = 40287;
                $saveperson['cost_us'] = 10.30;
                $saveperson['retail_us'] = 12.17;
            } else if ($resultsPerson['id'] == "1923370963") {
                //Juan Arredondo
                $saveperson['cost'] = 38788;
                $saveperson['retail'] = 42315;
                $saveperson['cost_us'] = 9.8;
                $saveperson['retail_us'] = 11.6;
            } else if ($resultsPerson['id'] == "1923533788") {
                //Victor Gil
                $saveperson['cost'] = 36930;
                $saveperson['retail'] = 40287;
                $saveperson['cost_us'] = 10.25;
                $saveperson['retail_us'] = 12.12;
            } else if ($resultsPerson['id'] == "1849041546") {
                //Hoovert
                $saveperson['cost'] = 88459;
                $saveperson['retail'] = 96501;
                $saveperson['esperado'] = 50;
                $saveperson['cost_us'] = 37.05;
                $saveperson['retail_us'] = 43.79;
            } else if ($resultsPerson['id'] == "2316429479") {
                //Julian
                $saveperson['cost'] = 30957;
                $saveperson['retail'] = 33771;
                $saveperson['cost_us'] = 8.86;
                $saveperson['retail_us'] = 10.47;
            } else if ($resultsPerson['id'] == "4472620297") {
                //Neiderson
                $saveperson['cost'] = 30957;
                $saveperson['retail'] = 33771;
                $saveperson['cost_us'] = 8.86;
                $saveperson['retail_us'] = 10.47;
            } else if ($resultsPerson['id'] == "1856110846") {
                //David Mendez
                $saveperson['cost'] = 87786;
                $saveperson['retail'] = 95767;
                $saveperson['esperado'] = 50;
                $saveperson['cost_us'] = 27.14;
                $saveperson['retail_us'] = 32.07;
            } else if ($resultsPerson['id'] == "3216537278") {
                //Laura Aires
                $saveperson['cost'] = 41447;
                $saveperson['retail'] = 45214;
                $saveperson['cost_us'] = 12.20;
                $saveperson['retail_us'] = 14.42;
            } else if ($resultsPerson['id'] == "4194692322") {
                //Paula Lozano
                $saveperson['cost'] = 51482;
                $saveperson['retail'] = 56162;
                $saveperson['cost_us'] = 14.42;
                $saveperson['esperado'] = 50;
                $saveperson['retail_us'] = 17.04;
            } else if ($resultsPerson['id'] == "5105315875") {
                //Fabian Herrera
                $saveperson['cost'] = 40994;
                $saveperson['retail'] = 44721;
                $saveperson['cost_us'] = 11.19;
                $saveperson['retail_us'] = 13.23;
            } else if ($resultsPerson['id'] == "5262862217") {
                //Nicolas Barrios
                $saveperson['cost'] = 43842;
                $saveperson['retail'] = 47828;
                $saveperson['cost_us'] = 11.19;
                $saveperson['retail_us'] = 13.23;
            } else if ($resultsPerson['id'] == "5105315875") {
                //Nestor Mosquera
                $saveperson['cost'] = 30957;
                $saveperson['retail'] = 33771;
                $saveperson['cost_us'] = 8.86;
                $saveperson['retail_us'] = 10.47;
            } else if ($resultsPerson['id'] == "5450721066") {
                //Hector Segura
                $saveperson['cost'] = 29293;
                $saveperson['retail'] = 31956;
                $saveperson['cost_us'] = 8.86;
                $saveperson['retail_us'] = 10.47;
            } else if ($resultsPerson['id'] == "5402212913") {
                //Mayra Salcedo
                $saveperson['cost'] = 44380;
                $saveperson['retail'] = 48414;
                $saveperson['cost_us'] = 8.86;
                $saveperson['retail_us'] = 10.47;
            } else if ($resultsPerson['id'] == "6460464077") {
                //Leonardo 
                $saveperson['cost'] = 68500;
                $saveperson['retail'] = 74750;
                $saveperson['cost_us'] = 8.86;
                $saveperson['retail_us'] = 10.47;
            } else if ($resultsPerson['id'] == "2185287855") {
                //Angelica 
                $saveperson['cost'] = 45500;
                $saveperson['retail'] = 74750;
                $saveperson['cost_us'] = 8.86;
                $saveperson['retail_us'] = 10.47;
            } else {
                $saveperson['cost'] = 50000;
                $saveperson['retail'] = 60000;
            }
            array_push($orderedPeople, $saveperson);
        }
        return $orderedPeople;
    }
}
