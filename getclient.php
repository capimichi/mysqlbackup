<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . "readconfig.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . "startlog.php";

$client = new \Google_Client();
$client->setApplicationName('Google Drive API PHP Quickstart');
$client->setScopes(\Google_Service_Drive::DRIVE);
$client->setAuthConfig($config['gd_credentials_path']);
$client->setAccessType('offline');
$client->setPrompt('select_account consent');
$client->setAccessType('offline');

// Load previously authorized token from a file, if it exists.
$tokenPath = $config['gd_token_path'];
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
            $exceptionLogger->error(join(', ', $accessToken));
            throw new Exception(join(', ', $accessToken));
        }
    }
    // Save the token to a file.
    if (!file_exists(dirname($tokenPath))) {
        mkdir(dirname($tokenPath), 0700, true);
    }
    file_put_contents($tokenPath, json_encode($client->getAccessToken()));
    $systemLogger->info('Created token');
}


