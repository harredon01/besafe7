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
        
        //dd($data);
        $file = Excel::store(new ArrayMultipleSheetExport($data), "exports/" . $title . ".xls", "local");
    }

}
