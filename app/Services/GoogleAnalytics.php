<?php

namespace App\Services;
use App\Exports\ArrayMultipleSheetExport;
use Excel;

class GoogleAnalytics {



    public function reorganizeArray($data) {
        $resultPage = [];
        foreach ($data as $row) {
            $resultRow = [];
            foreach ($row as $key => $value) {
                array_push($resultRow, $value);
            }
            array_push($resultPage, $resultRow);
        }
        return $resultPage;
    }

    public function writeFile($data, $title) {
        
        //dd($data);
        $file = Excel::store(new ArrayMultipleSheetExport($data), "exports/" . $title . ".xls", "local");
    }

    public function run() {
        $analytics = $this->initializeAnalytics();
        //Eccomerce
        $metrics = [
//            ["name" => "Super", "id" => "166256215"],
            ["name" => "Every Day", "id" => "236177570"],
//            ["name" => "Ezpot", "id" => "155384411"],
//            ["name" => "Vilaseca", "id" => "180729051"],
            //    ["name" => "Bodega y cocina", "id" => "149085830"],
//            ["name" => "Daportare", "id" => "114361275"],
//            ["name" => "Cano Col", "id" => "186528565"],
//            ["name" => "Cano Int", "id" => "192819811"],
            //    ["name" => "Brasa Roja", "id" => "114368786"],
            //    ["name" => "Calimio", "id" => "131329703"],
            //    ["name" => "Calivea", "id" => "131319203"],
            //    ["name" => "Dilucca", "id" => "99509236"],
//            ["name" => "Celsia", "id" => "180921091"],
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
//        $metrics = [
//            ["name" => "ga:sessions", "alias" => "sessions"],
//            ["name" => "ga:pageviews", "alias" => "pageviews"],
//            ["name" => "ga:avgTimeOnPage", "alias" => "Average time on site"],
//            ["name" => "ga:pageviewsPerSession", "alias" => "Pageviews per session"],
//            ["name" => "ga:transactions", "alias" => "Transactions"],
//            ["name" => "ga:itemsPerPurchase", "alias" => "Items per purchase"],
//            ["name" => "ga:productDetailViews", "alias" => "Product detail views"],
//            ["name" => "ga:productAddsToCart", "alias" => "Products Add to cart"],
//            ["name" => "ga:productCheckouts", "alias" => "Products to checkout"]
//        ];
//        $dimensions = ["ga:landingPagePath"];
//        $response = $this->getReport($analytics, $metrics, $dimensions, $client["id"]);
//        $results = $this->printResults($response, "landing page");
//        $results["name"] = "Landing page Path";
//        array_push($clientResults, $results);

        //Eccomerce
//        $metrics = [
//            ["name" => "ga:sessions", "alias" => "sessions"],
//            ["name" => "ga:pageviews", "alias" => "pageviews"],
//            ["name" => "ga:avgTimeOnPage", "alias" => "Average time on site"],
//            ["name" => "ga:pageviewsPerSession", "alias" => "Pageviews per session"]
//        ];
//        $dimensions = ["ga:pagePath"];
//        $response = $this->getReport($analytics, $metrics, $dimensions, $client["id"]);
//        $results = $this->printResults($response, "page path");
//        $results["name"] = "Datos por seccion";
//        array_push($clientResults, $results);
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
        //$KEY_FILE_LOCATION = '/home/hoovert/My Project-c532002d40af.json';
        $KEY_FILE_LOCATION = '/home/hoovert/data-warehouse-325605-1130670781ab.json';

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
        array_unshift($results['rows'], null);
        $results['rows'] = call_user_func_array('array_map', $results['rows']);
        return $results;
    }

}
