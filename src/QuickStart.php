<?php

require_once 'vendor/autoload.php';


/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient()
{
    $client = new Google_Client();
    $client->setApplicationName('Gmail API PHP Quickstart');
    $client->setScopes(Google_Service_Gmail::GMAIL_READONLY);
    $client->setAuthConfig('client_secret.json');
    $client->setAccessType('offline');

    // Load previously authorized credentials from a file.
    $credentialsPath = expandHomeDirectory('credentials.json');
    
    if (file_exists($credentialsPath)) {
        $accessToken = json_decode(file_get_contents($credentialsPath), true);
    } else {

        // Request authorization from the user.
        $authUrl = $client->createAuthUrl();
        printf("Open the following link in your browser:\n%s\n", $authUrl);
        print 'Enter verification code: ';
        $authCode = trim(fgets(STDIN));

        // Exchange authorization code for an access token.
        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

        // Store the credentials to disk.
        if (!file_exists(dirname($credentialsPath))) {
            mkdir(dirname($credentialsPath), 0700, true);
        }
        file_put_contents($credentialsPath, json_encode($accessToken));
        printf("Credentials saved to %s\n", $credentialsPath);
    }
    $client->setAccessToken($accessToken);

    // Refresh the token if it's expired.
    if ($client->isAccessTokenExpired()) {
        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
    }
    return $client;
}

/**
 * Expands the home directory alias '~' to the full path.
 * @param string $path the path to expand.
 * @return string the expanded path.
 */
function expandHomeDirectory($path)
{
    $homeDirectory = getenv('HOME');
    if (empty($homeDirectory)) {
        $homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
    }
    return str_replace('~', realpath($homeDirectory), $path);
}


/**
 * Get list of Messages in user's mailbox.
 *
 * @param  Google_Service_Gmail $service Authorized Gmail API instance.
 * @param  string $userId User's email address. The special value 'me'
 * can be used to indicate the authenticated user.
 * @return array Array of Messages.
 */
function listMessages($service, $userId) {
  $pageToken = NULL;
  $messages = [];
  $opt_param = [];

  do {
    try {

      // $opt_param['maxResults'] = 10;

      //query
      $opt_param['q'] = 'is:unread after:2018/05/12';

      $opt_param['includeSpamTrash'] = false;

      if ($pageToken) {
        $opt_param['pageToken'] = $pageToken;
      }
      
      //gets messageId.
      $messagesResponse = $service->users_messages->listUsersMessages($userId, $opt_param);

      $messages = [];
      foreach ($messagesResponse as $message) {
        $formattedMessage = [];
        $formattedMessage['id'] = $message['id'];
        $formattedMessage['thread_id'] = $message['threadId'];
        var_dump(getMessage($service, 'me', $message['id']));
        die;
        array_push($messages, $formattedMessage);    
      }

      var_dump($messages);
      die;
    

      if ($messagesResponse->getMessages()) {
        $messages = array_merge($messages, $messagesResponse->getMessages());
        $pageToken = $messagesResponse->getNextPageToken();
      }
    } catch (Exception $e) {
      // print 'An error occurred: ' . $e->getMessage();
    }
  } while ($pageToken);

  // foreach ($messages as $message) {
  //   print 'Message with ID: ' . $message->getId() . '<br/>';
  // }

  var_dump($messages);
  return $messages;
}

    /**
     * Get Message with given ID.
     *
     * @param  Google_Service_Gmail $service Authorized Gmail API instance.
     * @param  string $userId User's email address. The special value 'me'
     * can be used to indicate the authenticated user.
     * @param  string $messageId ID of Message to get.
     * @return Google_Service_Gmail_Message Message retrieved.
     */
    function getMessage($service, $userId, $messageId) {
      try {
        $message = $service->users_messages->get($userId, $messageId);
        return $message;
      } catch (Exception $e) {
        throw new Exception($e);
      }
    }

// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_Gmail($client);

$messages = listMessages($service, 'me');
// Print the labels in the user's account.
$results = $service->users_labels->listUsersLabels('me');

var_dump($service);
die;

if (count($results->getLabels()) == 0) {
  print "No labels found.\n";
} else {
  print "Labels:\n";
  var_dump($results->getLabels());
  die;
  foreach ($results->getLabels() as $label) {
    // var_dump(get_class_methods($label));
    var_dump($label->getThreadsTotal());
    // printf("- %s\n", $label->getName());
  }
}
