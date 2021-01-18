<?php

namespace App\Services;

use Excel;
use App\Exports\ArrayMultipleSheetExport;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\Merchant;
use App\Models\Report;
use App\Models\Category;
use App\Models\Product;
use App\Mail\StoreReports;
use Google\Auth\CredentialsLoader;
use Google\Auth\OAuth2;
use GetOpt\GetOpt;
use Google\Ads\GoogleAds\Lib\OAuth2TokenBuilder;
use Google\Ads\GoogleAds\Lib\V6\GoogleAdsClient;
use Google\Ads\GoogleAds\Lib\V6\GoogleAdsClientBuilder;
use Google\Ads\GoogleAds\Lib\V6\GoogleAdsException;
use Google\Ads\GoogleAds\Util\V6\ResourceNames;
use Google\Ads\GoogleAds\V6\Enums\KeywordMatchTypeEnum\KeywordMatchType;
use Google\Ads\GoogleAds\V6\Enums\KeywordPlanForecastIntervalEnum\KeywordPlanForecastInterval;
use Google\Ads\GoogleAds\V6\Enums\KeywordPlanNetworkEnum\KeywordPlanNetwork;
use Google\Ads\GoogleAds\V6\Errors\GoogleAdsError;
use Google\Ads\GoogleAds\V6\Resources\KeywordPlan;
use Google\Ads\GoogleAds\V6\Resources\KeywordPlanAdGroup;
use Google\Ads\GoogleAds\V6\Resources\KeywordPlanAdGroupKeyword;
use Google\Ads\GoogleAds\V6\Resources\KeywordPlanCampaign;
use Google\Ads\GoogleAds\V6\Resources\KeywordPlanCampaignKeyword;
use Google\Ads\GoogleAds\V6\Resources\KeywordPlanForecastPeriod;
use Google\Ads\GoogleAds\V6\Resources\KeywordPlanGeoTarget;
use Google\Ads\GoogleAds\V6\Services\KeywordPlanAdGroupKeywordOperation;
use Google\Ads\GoogleAds\V6\Services\KeywordPlanAdGroupOperation;
use Google\Ads\GoogleAds\V6\Services\KeywordPlanCampaignKeywordOperation;
use Google\Ads\GoogleAds\V6\Services\KeywordPlanCampaignOperation;
use Google\Ads\GoogleAds\V6\Services\KeywordPlanOperation;
use Google\ApiCore\ApiException;

class Adwords {

    /**
     * @var string the OAuth2 scope for the Google Ads API
     * @see https://developers.google.com/google-ads/api/docs/oauth/internals#scope
     */
    const SCOPE = 'https://www.googleapis.com/auth/adwords';

    /**
     * The Auth implementation.
     *
     */
    protected $clientData;
    protected $locations = [
        ["id" => 2170, "name" => "Colombia"],
        ["id" => 1003652, "name" => "Envigado"],
        ["id" => 1003653, "name" => "Itagui"],
        ["id" => 1003654, "name" => "Medellin"],
        ["id" => 1003655, "name" => "Barranquillita"],
        ["id" => 1003656, "name" => "Cartagenita"],
        ["id" => 1003657, "name" => "Manizales"],
        ["id" => 1003658, "name" => "Tocancipa"],
        ["id" => 1003659, "name" => "Bogota"],
        ["id" => 1003660, "name" => "Neiva"],
        ["id" => 1003661, "name" => "Santa Marta"],
        ["id" => 1003662, "name" => "Villavicencio"],
        ["id" => 1003663, "name" => "Pasto"],
        ["id" => 1003664, "name" => "San Francisco"],
        ["id" => 1003665, "name" => "Pereira"],
        ["id" => 1003666, "name" => "Bucaramanga"],
        ["id" => 1003667, "name" => "Buenaventura"],
        ["id" => 1003668, "name" => "Cali"],
        ["id" => 1003669, "name" => "Popayan"],
        ["id" => 1003670, "name" => "Yumbo"],
    ];

    public function writeFile($data, $title, $sendMail) {
        //dd($data);
        $file = Excel::store(new ArrayMultipleSheetExport($data), "exports/" . $title . ".xls", "local");
        $path = 'exports/' . $title . ".xls";
        $path = 'exports/' . $title . ".xls";
        if ($sendMail) {
            $users = User::whereIn('id', [1, 2])->get();
            Mail::to($users)->send(new StoreReports($path));
        } else {
            return $path;
        }
    }

    public function run() {
        $KEY_FILE_LOCATION = '/home/hoovert/adwords_data.json';
        $string = file_get_contents($KEY_FILE_LOCATION);
        $this->clientData = json_decode($string, true);
        //$this->authentication();
        $this->initializeKeywordPlan();
        //Eccomerce
    }

    /**
     * Initializes an Analytics Reporting API V4 service object.
     * 753234669761-7l7nrnal8hprn0tf4n5b5saj3d52ooms.apps.googleusercontent.com
     * @return An authorized Analytics Reporting API V4 service object.
     */
    function authentication() {

        // Use the developers console and download your service account
        // credentials in JSON format. Place them in this directory or
        // change the key file location if necessary.
        // Poner el developer token en el credentials para hacer todo de una
        $KEY_FILE_LOCATION = '/home/hoovert/credentials.json';
        $string = file_get_contents($KEY_FILE_LOCATION);
        $json_a = json_decode($string, true);




        $scopes = self::SCOPE . ' ';

        $oauth2 = new OAuth2(
                [
            'authorizationUri' => $json_a['installed']['auth_uri'],
            'redirectUri' => $json_a['installed']['redirect_uris'][0],
            'tokenCredentialUri' => $json_a['installed']['token_uri'],
            'clientId' => $json_a['installed']['client_id'],
            'clientSecret' => $json_a['installed']['client_secret'],
            'scope' => $scopes
                ]
        );

        printf(
                'Log into the Google account you use for Google Ads and visit the following URL:'
                . '%1$s%2$s%1$s%1$s',
                PHP_EOL,
                $oauth2->buildFullAuthorizationUri()
        );
        print 'After approving the application, enter the authorization code here: ';
        $stdin = fopen('php://stdin', 'r');
        $code = trim(fgets($stdin));
        fclose($stdin);

        $oauth2->setCode($code);
        $authToken = $oauth2->fetchAuthToken();
        print "Your refresh token is: {$authToken['refresh_token']}" . PHP_EOL . PHP_EOL;
        $clientId = $json_a['installed']['client_id'];
        $clientSecret = $json_a['installed']['client_secret'];
        $developer_token = $this->clientData['developer_token'];

        $propertiesToCopy = '[GOOGLE_ADS]' . PHP_EOL;
        $propertiesToCopy .= "developerToken = \"$developer_token\"" . PHP_EOL;
        $propertiesToCopy .= <<<EOD
; Required for manager accounts only: Specify the login customer ID used to authenticate API calls.
; This will be the customer ID of the authenticated manager account. You can also specify this later
; in code if your application uses multiple manager account + OAuth pairs.
; loginCustomerId = "INSERT_LOGIN_CUSTOMER_ID_HERE"
EOD;
        $propertiesToCopy .= PHP_EOL . '[OAUTH2]' . PHP_EOL;
        $propertiesToCopy .= "clientId = \"$clientId\"" . PHP_EOL;
        $propertiesToCopy .= "clientSecret = \"$clientSecret\"" . PHP_EOL;
        $propertiesToCopy .= "refreshToken = \"{$authToken['refresh_token']}\"" . PHP_EOL;

        print 'Copy the text below into a file named "google_ads_php.ini" in your home '
                . 'directory, and replace "INSERT_DEVELOPER_TOKEN_HERE" with your developer '
                . 'token:' . PHP_EOL;
        file_put_contents("/home/hoovert/google_ads_php.ini", $propertiesToCopy);
        print PHP_EOL . $propertiesToCopy;
    }

    private function initializeKeywordPlan() {
        /*
          2170 Colombia
          1003652	Envigado
          1003653	Itagui
          1003654	Medellin
          1003655	Barranquillita
          1003656	Cartagenita
          1003657	Manizales
          1003658	Tocancipa
          1003659	Bogota
          1003660	Neiva
          1003661	Santa Marta
          1003662	Villavicencio
          1003663	Pasto
          1003664	San Francisco
          1003665	Pereira
          1003666	Bucaramanga
          1003667	Buenaventura
          1003668	Cali
          1003669	Popayan
          1003670	Yumbo
         */
        $location = "1003659";

//        $options = (new ArgumentParser())->parseCommandArguments([
//            ArgumentNames::CUSTOMER_ID => GetOpt::REQUIRED_ARGUMENT
//        ]);
        // Generate a refreshable OAuth2 credential for authentication.
        $oAuth2Credential = (new OAuth2TokenBuilder())->fromFile()->build();
        // Construct a Google Ads client configured from a properties file and the
        // OAuth2 credentials above.
        $googleAdsClient = (new GoogleAdsClientBuilder())
                ->fromFile()
                ->withOAuth2Credential($oAuth2Credential)
                ->build();
        $keywords = [
            ['text' => 'veterinario'],
            ['text' => 'comida para perro'],
            ['text' => 'comida para gato'],
        ];
        $finalKeywordResults = [];
        $bids = [1000000, 2000000, 10000000];
        $locations = ["2170", "1003659", "1003654", "1003668"];
        foreach ($bids as $bid) {
            foreach ($locations as $location) {
                try {
                    $keywordPlan = $this->runExample(
                            $googleAdsClient,
                            $this->clientData['client_id'],
                            $keywords,
                            $location,
                            $bid
                    );
                } catch (GoogleAdsException $googleAdsException) {
                    printf(
                            "Request with ID '%s' has failed.%sGoogle Ads failure details:%s",
                            $googleAdsException->getRequestId(),
                            PHP_EOL,
                            PHP_EOL
                    );
                    foreach ($googleAdsException->getGoogleAdsFailure()->getErrors() as $error) {
                        /** @var GoogleAdsError $error */
                        printf(
                                "\t%s: %s%s",
                                $error->getErrorCode()->getErrorCode(),
                                $error->getMessage(),
                                PHP_EOL
                        );
                    }
                    exit(1);
                } catch (ApiException $apiException) {
                    printf(
                            "ApiException was thrown with message '%s'.%s",
                            $apiException->getMessage(),
                            PHP_EOL
                    );
                    exit(1);
                }
                $keywordResults = [];
                try {
                    $keywordResults = $this->getStats(
                            $googleAdsClient,
                            $this->clientData['client_id'],
                            $keywordPlan
                    );
                } catch (GoogleAdsException $googleAdsException) {
                    printf(
                            "Request with ID '%s' has failed.%sGoogle Ads failure details:%s",
                            $googleAdsException->getRequestId(),
                            PHP_EOL,
                            PHP_EOL
                    );
                    foreach ($googleAdsException->getGoogleAdsFailure()->getErrors() as $error) {
                        /** @var GoogleAdsError $error */
                        printf(
                                "\t%s: %s%s",
                                $error->getErrorCode()->getErrorCode(),
                                $error->getMessage(),
                                PHP_EOL
                        );
                    }
                    exit(1);
                } catch (ApiException $apiException) {
                    printf(
                            "ApiException was thrown with message '%s'.%s",
                            $apiException->getMessage(),
                            PHP_EOL
                    );
                    exit(1);
                }
                $finalKeywordResults = array_merge($finalKeywordResults,$keywordResults['keywords']);
            }
            
        }



        $results = [];
        $tablehead = array_keys($finalKeywordResults[0]);
        array_unshift($finalKeywordResults, $tablehead);
        $page = [
            "name" => "Keywords",
            "rows" => $finalKeywordResults
        ];
        array_push($results, $page);
        $this->writeFile($results, "Adwords", true);
    }

    public function runExample(GoogleAdsClient $googleAdsClient, int $customerId, $keywordsArray, $location, $bid) {

        $keywordPlanResource = $this->createKeywordPlan(
                $googleAdsClient,
                $customerId
        );



        $planCampaignResource = $this->createKeywordPlanCampaign(
                $googleAdsClient,
                $customerId,
                $keywordPlanResource,
                $location
        );

        $planAdGroupResource = $this->createKeywordPlanAdGroup(
                $googleAdsClient,
                $customerId,
                $planCampaignResource
        );

        $keywords = $this->createKeywordPlanAdGroupKeywords(
                $googleAdsClient,
                $customerId,
                $planAdGroupResource,
                $keywordsArray,
                $location,
                $bid
        );

//        $this->createKeywordPlanNegativeCampaignKeywords(
//                $googleAdsClient,
//                $customerId,
//                $planCampaignResource
//        );
        return ["resource" => $keywordPlanResource, 'keywords' => $keywords];
    }

    /**
     * Creates a keyword plan.
     *
     * @param GoogleAdsClient $googleAdsClient the Google Ads API client
     * @param int $customerId the customer ID
     * @return string the newly created keyword plan resource
     */
    private function createKeywordPlan(
            GoogleAdsClient $googleAdsClient,
            int $customerId
    ) {
        // Creates a keyword plan.
        $keywordPlan = new KeywordPlan([
            'name' => 'Keyword plan for traffic estimate #',
            'forecast_period' => new KeywordPlanForecastPeriod([
                'date_interval' => KeywordPlanForecastInterval::NEXT_QUARTER
                    ])
        ]);

        // Creates a keyword plan operation.
        $keywordPlanOperation = new KeywordPlanOperation();
        $keywordPlanOperation->setCreate($keywordPlan);

        // Issues a mutate request to add the keyword plan.
        $keywordPlanServiceClient = $googleAdsClient->getKeywordPlanServiceClient();
        $response = $keywordPlanServiceClient->mutateKeywordPlans(
                $customerId,
                [$keywordPlanOperation]
        );

        $resourceName = $response->getResults()[0]->getResourceName();
        printf("Created keyword plan: '%s'%s", $resourceName, PHP_EOL);

        return $resourceName;
    }

    /**
     * Creates the campaign for the keyword plan.
     *
     * @param GoogleAdsClient $googleAdsClient the Google Ads API client
     * @param int $customerId the customer ID
     * @param string $keywordPlanResource the keyword plan resource
     * @return string the newly created campaign resource
     */
    private function createKeywordPlanCampaign(
            GoogleAdsClient $googleAdsClient,
            int $customerId,
            string $keywordPlanResource,
            string $location
    ) {
        // Creates a keyword plan campaign.
        $keywordPlanCampaign = new KeywordPlanCampaign([
            'name' => 'Keyword plan campaign #',
            'cpc_bid_micros' => 1000000,
            'keyword_plan_network' => KeywordPlanNetwork::GOOGLE_SEARCH,
            'keyword_plan' => $keywordPlanResource,
        ]);

        // See https://developers.google.com/adwords/api/docs/appendix/geotargeting
        // for the list of geo target IDs.
        $keywordPlanCampaign->setGeoTargets([
            new KeywordPlanGeoTarget([
                'geo_target_constant' => ResourceNames::forGeoTargetConstant($location)
                    ])
        ]);

        // See https://developers.google.com/adwords/api/docs/appendix/codes-formats#languages
        // for the list of language criteria IDs.
        // Set English as a language constant.
        $keywordPlanCampaign->setLanguageConstants([ResourceNames::forLanguageConstant(1003)]);

        // Creates a keyword plan campaign operation.
        $keywordPlanCampaignOperation = new KeywordPlanCampaignOperation();
        $keywordPlanCampaignOperation->setCreate($keywordPlanCampaign);

        $keywordPlanCampaignServiceClient = $googleAdsClient->getKeywordPlanCampaignServiceClient();
        $response = $keywordPlanCampaignServiceClient->mutateKeywordPlanCampaigns(
                $customerId,
                [$keywordPlanCampaignOperation]
        );

        $planCampaignResource = $response->getResults()[0]->getResourceName();
        printf("Created campaign for keyword plan: '%s'%s", $planCampaignResource, PHP_EOL);

        return $planCampaignResource;
    }

    /**
     * Creates the ad group for the keyword plan.
     *
     * @param GoogleAdsClient $googleAdsClient the Google Ads API client
     * @param int $customerId the customer ID
     * @param string $planCampaignResource the resource name of the campaign under which the
     *     ad group is created
     * @return string the newly created ad group resource
     */
    private function createKeywordPlanAdGroup(
            GoogleAdsClient $googleAdsClient,
            int $customerId,
            string $planCampaignResource
    ) {
        // Creates a keyword plan ad group.
        $keywordPlanAdGroup = new KeywordPlanAdGroup([
            'name' => 'Keyword plan ad group #',
            'cpc_bid_micros' => 2500000,
            'keyword_plan_campaign' => $planCampaignResource
        ]);

        // Creates a keyword plan ad group operation.
        $keywordPlanAdGroupOperation = new KeywordPlanAdGroupOperation();
        $keywordPlanAdGroupOperation->setCreate($keywordPlanAdGroup);

        $keywordPlanAdGroupServiceClient = $googleAdsClient->getKeywordPlanAdGroupServiceClient();
        $response = $keywordPlanAdGroupServiceClient->mutateKeywordPlanAdGroups(
                $customerId,
                [$keywordPlanAdGroupOperation]
        );

        $planAdGroupResource = $response->getResults()[0]->getResourceName();
        printf("Created ad group for keyword plan: '%s'%s", $planAdGroupResource, PHP_EOL);

        return $planAdGroupResource;
    }

    /**
     * Creates ad group keywords for the keyword plan.
     *
     * @param GoogleAdsClient $googleAdsClient the Google Ads API client
     * @param int $customerId the customer ID
     * @param string $planAdGroupResource the resource name of the ad group under which the
     *     keywords are created
     */
    private function createKeywordPlanAdGroupKeywords(
            GoogleAdsClient $googleAdsClient,
            int $customerId,
            string $planAdGroupResource,
            array $keywords,
            $location,
            $bid
    ) {
        $location_text = "";
        foreach ($this->locations as $value) {
            if ($location == $value['id']) {
                $location_text = $value['name'];
            }
        }
        // Creates the ad group keywords for the keyword plan.
        $newKeywords = [];
        foreach ($keywords as $value) {
            $value['type'] = KeywordMatchType::BROAD;
            $value['type_t'] = 'BROAD';
            $value['location'] = $location_text;
            $value['bid_micros'] = $bid;
            $value['bid'] = $value['bid_micros'] / 1000000;
            array_push($newKeywords, $value);
            $value['type'] = KeywordMatchType::PHRASE;
            $value['type_t'] = 'PHRASE';
            $value['location'] = $location_text;
            $value['bid_micros'] = $bid;
            $value['bid'] = $value['bid_micros'] / 1000000;
            array_push($newKeywords, $value);
            $value['type'] = KeywordMatchType::EXACT;
            $value['type_t'] = 'EXACT';
            $value['location'] = $location_text;
            $value['bid_micros'] = $bid;
            $value['bid'] = $value['bid_micros'] / 1000000;
            array_push($newKeywords, $value);
        }
        $keywordPlanAdGroupKeywords = [];
        foreach ($newKeywords as $value) {
            $keywordPlanAdGroupKeyword1 = new KeywordPlanAdGroupKeyword([
                'text' => $value['text'],
                'cpc_bid_micros' => $value['bid_micros'],
                'match_type' => $value['type'],
                'keyword_plan_ad_group' => $planAdGroupResource
            ]);
            array_push($keywordPlanAdGroupKeywords, $keywordPlanAdGroupKeyword1);
        }

        // Creates an array of keyword plan ad group keyword operations.
        $keywordPlanAdGroupKeywordOperations = [];

        foreach ($keywordPlanAdGroupKeywords as $keyword) {
            $keywordPlanAdGroupKeywordOperation = new KeywordPlanAdGroupKeywordOperation();
            $keywordPlanAdGroupKeywordOperation->setCreate($keyword);
            $keywordPlanAdGroupKeywordOperations[] = $keywordPlanAdGroupKeywordOperation;
        }

        $keywordPlanAdGroupKeywordServiceClient = $googleAdsClient->getKeywordPlanAdGroupKeywordServiceClient();

        // Adds the keyword plan ad group keywords.
        $response = $keywordPlanAdGroupKeywordServiceClient->mutateKeywordPlanAdGroupKeywords(
                $customerId,
                $keywordPlanAdGroupKeywordOperations
        );

        $counter = 0;
        /** @var KeywordPlanAdGroupKeyword $result */
        foreach ($response->getResults() as $result) {
            $res = $result->getResourceName();
            $resArray = explode("/", $res);
            $newKeywords[$counter]["id"] = $resArray[count($resArray) - 1];
            printf(
                    "Created ad group keyword for keyword plan: '%s'%s",
                    $res,
                    PHP_EOL
            );
            $counter++;
        }
        return $newKeywords;
    }

    /**
     * Creates negative campaign keywords for the keyword plan.
     *
     * @param GoogleAdsClient $googleAdsClient the Google Ads API client
     * @param int $customerId the customer ID
     * @param string $planCampaignResource the resource name of the campaign under which
     *     the keywords are created
     */
    private function createKeywordPlanNegativeCampaignKeywords(
            GoogleAdsClient $googleAdsClient,
            int $customerId,
            string $planCampaignResource
    ) {
        // Creates a negative campaign keyword for the keyword plan.
        $keywordPlanCampaignKeyword = new KeywordPlanCampaignKeyword([
            'text' => 'moon walk',
            'match_type' => KeywordMatchType::BROAD,
            'keyword_plan_campaign' => $planCampaignResource,
            'negative' => true
        ]);

        $keywordPlanCampaignKeywordOperation = new KeywordPlanCampaignKeywordOperation();
        $keywordPlanCampaignKeywordOperation->setCreate($keywordPlanCampaignKeyword);

        $keywordPlanCampaignKeywordServiceClient = $googleAdsClient->getKeywordPlanCampaignKeywordServiceClient();

        // Adds the negative campaign keyword.
        $response = $keywordPlanCampaignKeywordServiceClient->mutateKeywordPlanCampaignKeywords(
                $customerId,
                [$keywordPlanCampaignKeywordOperation]
        );

        /** @var KeywordPlanCampaignKeyword $result */
        foreach ($response->getResults() as $result) {
            printf(
                    "Created negative campaign keyword for keyword plan: '%s'%s",
                    $result->getResourceName(),
                    PHP_EOL
            );
        }
    }

    public function getStats(
            GoogleAdsClient $googleAdsClient,
            int $customerId,
            array $keywordPlan
    ) {
        $planArray = explode("/", $keywordPlan['resource']);
        $planId = $planArray[count($planArray) - 1];
        $keywordPlanServiceClient = $googleAdsClient->getKeywordPlanServiceClient();

        // Issues a request to generate forecast metrics based on the specific keyword plan ID.
        $generateHistoricMetricsResponse = $keywordPlanServiceClient->generateHistoricalMetrics(
                ResourceNames::forKeywordPlan($customerId, $planId)
        );

        $i = 0;
        $counter = 0;
        foreach ($generateHistoricMetricsResponse->getMetrics() as $forecast) {
            /** @var KeywordPlanKeywordForecast $forecast */
            $metrics = $forecast->getKeywordMetrics();
            printf(
                    "%d) Keyword: %s%s",
                    ++$i,
                    $keywordPlan['keywords'][$counter]['text'],
                    PHP_EOL
            );
            $res = $forecast->getSearchQuery();
            $resArray = explode("/", $res);
            $keywordPlan['keywords'][$counter]['historic_id'] = $res;
            $keywordPlan['keywords'][$counter]['historic_avg_srch'] = $metrics->getAvgMonthlySearches();
            $keywordPlan['keywords'][$counter]['historic_competition'] = $metrics->getCompetition();
            $keywordPlan['keywords'][$counter]['historic_competition_index'] = $metrics->getCompetitionIndex();
            $keywordPlan['keywords'][$counter]['historic_low_top_of_page'] = $metrics->getLowTopOfPageBidMicros();
            $keywordPlan['keywords'][$counter]['historic_high_top_of_page'] = $metrics->getHighTopOfPageBidMicros();
            $counter++;
            $keywordPlan['keywords'][$counter]['historic_id'] = $res;
            $keywordPlan['keywords'][$counter]['historic_avg_srch'] = $metrics->getAvgMonthlySearches();
            $keywordPlan['keywords'][$counter]['historic_competition'] = $metrics->getCompetition();
            $keywordPlan['keywords'][$counter]['historic_competition_index'] = $metrics->getCompetitionIndex();
            $keywordPlan['keywords'][$counter]['historic_low_top_of_page'] = $metrics->getLowTopOfPageBidMicros();
            $keywordPlan['keywords'][$counter]['historic_high_top_of_page'] = $metrics->getHighTopOfPageBidMicros();
            $counter++;
            $keywordPlan['keywords'][$counter]['historic_id'] = $res;
            $keywordPlan['keywords'][$counter]['historic_avg_srch'] = $metrics->getAvgMonthlySearches();
            $keywordPlan['keywords'][$counter]['historic_competition'] = $metrics->getCompetition();
            $keywordPlan['keywords'][$counter]['historic_competition_index'] = $metrics->getCompetitionIndex();
            $keywordPlan['keywords'][$counter]['historic_low_top_of_page'] = $metrics->getLowTopOfPageBidMicros();
            $keywordPlan['keywords'][$counter]['historic_high_top_of_page'] = $metrics->getHighTopOfPageBidMicros();
            printf(
                    "Estimated getMonthlySearchVolumes: %s%s",
                    is_null($keywordPlan['keywords'][$counter]['historic_avg_srch']) ? 'null' : sprintf("%.2f", $keywordPlan['keywords'][$counter]['historic_avg_srch']),
                    PHP_EOL
            );
            $searchResultsMonths = $metrics->getMonthlySearchVolumes();
            $months = [];
            foreach ($searchResultsMonths as $value) {
                array_push($months, ["month" => $value->getYear() . "-" . $value->getMonth(), "total" => $value->getMonthlySearches()]);
            }
            //$keywordPlan['keywords'][$counter]['historic']['month_srch_vol']=$months;

            printf(
                    "Estimated Competition: %s%s",
                    is_null($keywordPlan['keywords'][$counter]['historic_competition']) ? 'null' : $keywordPlan['keywords'][$counter]['historic_competition'],
                    PHP_EOL
            );

            printf(
                    "Estimated visits per $100: %s%s",
                    is_null($keywordPlan['keywords'][$counter]['historic_competition_index']) ? 'null' : $keywordPlan['keywords'][$counter]['historic_competition_index'],
                    PHP_EOL
            );

            printf(
                    "Estimated LowTopOfPageBidMicros: %s%s",
                    is_null($keywordPlan['keywords'][$counter]['historic_low_top_of_page']) ? 'null' : $keywordPlan['keywords'][$counter]['historic_low_top_of_page'],
                    PHP_EOL
            );

            printf(
                    "Estimated getHighTopOfPageBidMicros: %s%s",
                    is_null($keywordPlan['keywords'][$counter]['historic_high_top_of_page']) ? 'null' : $keywordPlan['keywords'][$counter]['historic_high_top_of_page'],
                    PHP_EOL
            );
            $counter++;
        }
        $generateForecastMetricsResponse = $keywordPlanServiceClient->generateForecastMetrics(
                ResourceNames::forKeywordPlan($customerId, $planId)
        );

        $i = 0;
        $counter = 0;
        foreach ($generateForecastMetricsResponse->getKeywordForecasts() as $forecast) {
            /** @var KeywordPlanKeywordForecast $forecast */
            $metrics = $forecast->getKeywordForecast();
            printf(
                    "%d) Keyword: %s%s",
                    ++$i,
                    $keywordPlan['keywords'][$counter]['text'],
                    PHP_EOL
            );
            $res = $forecast->getKeywordPlanAdGroupKeyword();
            $resArray = explode("/", $res);
            $keywordPlan['keywords'][$counter]['forecast_id'] = $resArray[count($resArray) - 1];
            $keywordPlan['keywords'][$counter]['forecast_clicks'] = $metrics->getClicks();
            printf(
                    "Estimated daily clicks: %s%s",
                    is_null($metrics->getClicks()) ? 'null' : sprintf("%.2f", $keywordPlan['keywords'][$counter]['forecast_clicks']),
                    PHP_EOL
            );
            $keywordPlan['keywords'][$counter]['forecast_impressions'] = $metrics->getImpressions();
            printf(
                    "Estimated daily impressions: %s%s",
                    is_null($metrics->getImpressions()) ? 'null' : sprintf("%.2f", $keywordPlan['keywords'][$counter]['forecast_impressions']),
                    PHP_EOL
            );
            $keywordPlan['keywords'][$counter]['forecast_cpc'] = $metrics->getAverageCpc() / 1000000;
            printf(
                    "Estimated average cpc (micros): %s%s",
                    is_null($metrics->getAverageCpc()) ? 'null' : $keywordPlan['keywords'][$counter]['forecast_cpc'],
                    PHP_EOL
            );
            if($metrics->getAverageCpc() > 0){
                $keywordPlan['keywords'][$counter]['forecast_hundred'] = 100 / ($metrics->getAverageCpc() / 1000000);
            } else {
                $keywordPlan['keywords'][$counter]['forecast_hundred'] = 0;
            }
            
            printf(
                    "Estimated visits per $100: %s%s",
                    is_null($metrics->getAverageCpc()) ? 'null' : $keywordPlan['keywords'][$counter]['forecast_hundred'],
                    PHP_EOL
            );
            $counter++;
        }
        return $keywordPlan;
    }

}
