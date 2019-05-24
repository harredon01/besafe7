<?php

namespace App\Services;

use Excel;

class GoogleSheets {

    /**
     * Create a new class instance.
     *
     * @param  EventPusher  $pusher
     * @return void
     */
    public function runSheet() {

        $this->createSpreadsheet($service);
    }

    /**
     * Returns an authorized API client.
     * @return Google_Client the authorized client object
     */
    function getClient() {
        $client = new \Google_Client();
        $client->setApplicationName('Google Sheets API PHP Quickstart');
        $client->setScopes(\Google_Service_Sheets::SPREADSHEETS);
        $client->setAuthConfig('/home/hoovert/credentials.json');
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        // Load previously authorized token from a file, if it exists.
        // The file token.json stores the user's access and refresh tokens, and is
        // created automatically when the authorization flow completes for the first
        // time.
        $tokenPath = 'token.json';
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                // Request authorization from the user.
                $authUrl = $client->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $authUrl);
                print 'Enter verification code: ';
                $authCode = trim(fgets(STDIN));

                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);

                // Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    throw new Exception(join(', ', $accessToken));
                }
            }
            // Save the token to a file.
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        }
        return $client;
    }

    public function createSpreadsheet($title, $data) {
        //dd("Sid".time());
        $client = $this->getClient();
        //$title = $title . time();
        $service = new \Google_Service_Sheets($client);
        $valueInputOption = "USER_ENTERED";
        $spreadsheet = new \Google_Service_Sheets_Spreadsheet([
            'properties' => [
                'title' => $title
            ]
        ]);
        $spreadsheet = $service->spreadsheets->create($spreadsheet, [
            'fields' => 'spreadsheetId'
        ]);
        foreach ($data as $value) {
            $body2 = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest(array(
                'requests' => array('addSheet' => array('properties' => array('title' => $value['name'])))));
            $service->spreadsheets->batchUpdate($spreadsheet->spreadsheetId, $body2);
            $values = $this->reorganizeArray($value["rows"]);
            $body = new \Google_Service_Sheets_ValueRange([
                'values' => $values
            ]);
            $params = [
                'valueInputOption' => $valueInputOption
            ];
            $range = $value['name'] . "!A1";
            $service->spreadsheets_values->update($spreadsheet->spreadsheetId, $range, $body, $params);
        }
        return true;
    }

    public function reorganizeArray($data) {
        $resultPage = [];
        foreach ($data as $row) {
            $resultRow = [];
            foreach ($row as $key => $value) {
                array_push($resultRow, $value);
            }
            array_push($resultPage, $resultRow);
        }
        dd($resultPage);
        return $resultPage;
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
                    }
                }
                $excel->sheet(substr($page["name"], 0, 30), function($sheet) use($page) {
                    $sheet->fromArray($page["rows"], null, 'A1', true);
                });
            }
        })->store('xlsx');
    }

    public function run() {
        $analytics = $this->initializeAnalytics();
        $VIEW_ID = "166256215";
        //Eccomerce
        $metrics = [
            ["name" => "Super", "id" => "166256215"],
            ["name" => "Archies", "id" => "89564891"],
            ["name" => "Ezpot", "id" => "155384411"],
            ["name" => "Vilaseca", "id" => "180729051"],
            //    ["name" => "Bodega y cocina", "id" => "149085830"],
            ["name" => "Daportare", "id" => "114361275"],
            ["name" => "Cano Col", "id" => "186528565"],
            ["name" => "Cano Int", "id" => "192819811"],
            //    ["name" => "Brasa Roja", "id" => "114368786"],
            //    ["name" => "Calimio", "id" => "131329703"],
            //    ["name" => "Calivea", "id" => "131319203"],
            //    ["name" => "Dilucca", "id" => "99509236"],
            ["name" => "Celsia", "id" => "180921091"],
        ];
        foreach ($metrics as $value) {
            $this->runProject($analytics, $value);
        }
    }

    public function runProject($analytics, $client) {
        $clientResults = [];

        //Eccomerce
        $metrics = [
            ["name" => "ga:sessions", "alias" => "sessions"],
            ["name" => "ga:bounces", "alias" => "bounces"],
            ["name" => "ga:pageviews", "alias" => "pageviews"],
            ["name" => "ga:avgTimeOnPage", "alias" => "Average time on site"],
            ["name" => "ga:pageviewsPerSession", "alias" => "Pageviews per session"],
            ["name" => "ga:transactions", "alias" => "Transactions"],
            ["name" => "ga:itemsPerPurchase", "alias" => "Items per purchase"],
            ["name" => "ga:productDetailViews", "alias" => "Product detail views"],
            ["name" => "ga:productAddsToCart", "alias" => "Products Add to cart"],
            ["name" => "ga:productCheckouts", "alias" => "Products to checkout"]
        ];
        $dimensions = ["ga:day"];
        $response = $this->getReport($analytics, $metrics, $dimensions, $client["id"]);
        $results = $this->printResults($response, "day");
        $results["name"] = "Day";
        array_push($clientResults, $results);

        //Eccomerce
        $metrics = [
            ["name" => "ga:sessions", "alias" => "sessions"],
            ["name" => "ga:pageviews", "alias" => "pageviews"],
            ["name" => "ga:avgTimeOnPage", "alias" => "Average time on site"],
            ["name" => "ga:pageviewsPerSession", "alias" => "Pageviews per session"],
            ["name" => "ga:transactions", "alias" => "Transactions"],
            ["name" => "ga:itemsPerPurchase", "alias" => "Items per purchase"],
            ["name" => "ga:productDetailViews", "alias" => "Product detail views"],
            ["name" => "ga:productAddsToCart", "alias" => "Products Add to cart"],
            ["name" => "ga:productCheckouts", "alias" => "Products to checkout"]
        ];
        $dimensions = ["ga:source"];
        $response = $this->getReport($analytics, $metrics, $dimensions, $client["id"]);
        $results = $this->printResults($response, "source");
        $results["name"] = "Source";
        array_push($clientResults, $results);

        //Eccomerce
        $metrics = [
            ["name" => "ga:organicSearches", "alias" => "Organic Searches"],
            ["name" => "ga:sessions", "alias" => "sessions"],
            ["name" => "ga:pageviews", "alias" => "pageviews"],
            ["name" => "ga:avgTimeOnPage", "alias" => "Average time on site"],
            ["name" => "ga:pageviewsPerSession", "alias" => "Pageviews per session"],
            ["name" => "ga:transactions", "alias" => "Transactions"],
            ["name" => "ga:itemsPerPurchase", "alias" => "Items per purchase"],
            ["name" => "ga:productDetailViews", "alias" => "Product detail views"],
            ["name" => "ga:productAddsToCart", "alias" => "Products Add to cart"],
            ["name" => "ga:productCheckouts", "alias" => "Products to checkout"]
        ];
        $dimensions = ["ga:keyword"];
        $response = $this->getReport($analytics, $metrics, $dimensions, $client["id"]);
        $results = $this->printResults($response, "keyword");
        $results["name"] = "Keyword";
        array_push($clientResults, $results);

        //Eccomerce
        $metrics = [
            ["name" => "ga:sessions", "alias" => "sessions"],
            ["name" => "ga:pageviews", "alias" => "pageviews"],
            ["name" => "ga:avgTimeOnPage", "alias" => "Average time on site"],
            ["name" => "ga:pageviewsPerSession", "alias" => "Pageviews per session"],
            ["name" => "ga:transactions", "alias" => "Transactions"],
            ["name" => "ga:itemsPerPurchase", "alias" => "Items per purchase"],
            ["name" => "ga:productDetailViews", "alias" => "Product detail views"],
            ["name" => "ga:productAddsToCart", "alias" => "Products Add to cart"],
            ["name" => "ga:productCheckouts", "alias" => "Products to checkout"]
        ];
        $dimensions = ["ga:landingPagePath"];
        $response = $this->getReport($analytics, $metrics, $dimensions, $client["id"]);
        $results = $this->printResults($response, "landing page");
        $results["name"] = "Landing page Path";
        array_push($clientResults, $results);

        //Eccomerce
        $metrics = [
            ["name" => "ga:sessions", "alias" => "sessions"],
            ["name" => "ga:pageviews", "alias" => "pageviews"],
            ["name" => "ga:avgTimeOnPage", "alias" => "Average time on site"],
            ["name" => "ga:pageviewsPerSession", "alias" => "Pageviews per session"]
        ];
        $dimensions = ["ga:pagePath"];
        $response = $this->getReport($analytics, $metrics, $dimensions, $client["id"]);
        $results = $this->printResults($response, "page path");
        $results["name"] = "Datos por seccion";
        array_push($clientResults, $results);
        $this->writeFile($clientResults, "Reporte SEO " . $client["name"] . time());
    }

    /**
     * Initializes an Analytics Reporting API V4 service object.
     *
     * @return An authorized Analytics Reporting API V4 service object.
     */
    function initializeAnalytics() {

        // Use the developers console and download your service account
        // credentials in JSON format. Place them in this directory or
        // change the key file location if necessary.
        $KEY_FILE_LOCATION = '/home/hoovert/My Project-c532002d40af.json';

        // Create and configure a new client object.
        $client = new \Google_Client();
        $client->setApplicationName("Hello Analytics Reporting");
        $client->setAuthConfig($KEY_FILE_LOCATION);
        $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
        $analytics = new \Google_Service_AnalyticsReporting($client);

        return $analytics;
    }

    /**
     * Queries the Analytics Reporting API V4.
     *
     * @param service An authorized Analytics Reporting API V4 service object.
     * @return The Analytics Reporting API V4 response.
     */
    function getReport($analytics, $metrics, $dimensions, $VIEW_ID) {

        // Replace with your view ID, for example XXXX.
        // Create the DateRange object.
        $dateRange = new \Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate("30daysAgo");
        $dateRange->setEndDate("today");
        $metricsCont = [];


        foreach ($metrics as $value) {
            $metric = new \Google_Service_AnalyticsReporting_Metric();
            $metric->setExpression($value["name"]);
            $metric->setAlias($value["alias"]);
            array_push($metricsCont, $metric);
        }


        $dimensionsCont = [];
        foreach ($dimensions as $value) {
            $dimension = new \Google_Service_AnalyticsReporting_Dimension();
            $dimension->setName($value);
            array_push($dimensionsCont, $dimension);
        }
        // Create the Metrics object.
        // Create the ReportRequest object.
        $request = new \Google_Service_AnalyticsReporting_ReportRequest();
        $request->setViewId($VIEW_ID);
        $request->setDateRanges($dateRange);
        $request->setMetrics($metricsCont);
        $request->setDimensions($dimensionsCont);

        $body = new \Google_Service_AnalyticsReporting_GetReportsRequest();
        $body->setReportRequests(array($request));
        return $analytics->reports->batchGet($body);
    }

    /**
     * Parses and prints the Analytics Reporting API V4 response.
     *
     * @param An Analytics Reporting API V4 response.
     */
    function printResults($reports, $type) {
        $results = [];
        $results["rows"] = [];
        for ($reportIndex = 0; $reportIndex < count($reports); $reportIndex++) {
            $report = $reports[$reportIndex];
            $header = $report->getColumnHeader();
            $dimensionHeaders = $header->getDimensions();
            $metricHeaders = $header->getMetricHeader()->getMetricHeaderEntries();
            $rows = $report->getData()->getRows();
            $home = [];
            $home["name"] = "home";
            $count = 0;
            for ($rowIndex = 0; $rowIndex < count($rows); $rowIndex++) {
                $row = $rows[$rowIndex];
                $dimensions = $row->getDimensions();
                $metrics = $row->getMetrics();
                $name = "";
                for ($i = 0; $i < count($dimensionHeaders) && $i < count($dimensions); $i++) {
                    $name .= $dimensions[$i];
                    //print($dimensionHeaders[$i] . ": " . $dimensions[$i] . "\n");
                }
                $page = [];
                $page["name"] = $name;

                for ($j = 0; $j < count($metrics); $j++) {
                    $values = $metrics[$j]->getValues();
                    for ($k = 0; $k < count($values); $k++) {
                        $entry = $metricHeaders[$k];
                        if ($type == "page path" || $type == "landing page") {
                            if (substr($name, 0, 2) === "/?" || $name == "/" || $name == "(not set)") {
                                $count ++;
                                if (array_key_exists($entry->getName(), $home)) {
                                    $home[$entry->getName()] += $values[$k];
                                } else {
                                    $home[$entry->getName()] = $values[$k];
                                }
                            } else {
                                $page[$entry->getName()] = $values[$k];
                            }
                        } else {
                            $page[$entry->getName()] = $values[$k];
                        }
                    }
                }
                if (substr($name, 0, 2) === "/?" || $name == "/" || strpos($name, 'checkout') !== false) {
                    
                } else {
                    array_push($results["rows"], $page);
                }
            }
            if ($type == "page path" || $type == "landing page") {
                array_unshift($results["rows"], $home);
            }
            if ($type == "day") {
                $finalResults = [];
                if (count($results["rows"])) {
                    foreach ($results["rows"][0] as $key => $value) {
                        $finalResults[$key] = [$key];
                    }
                    foreach ($results["rows"] as $row) {
                        foreach ($row as $key => $value) {
                            array_push($finalResults[$key], $value);
                        }
                    }
                    $results["rows"] = $finalResults;
                }
            }
        }
        return $results;
    }

}
