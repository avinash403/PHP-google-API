<?php 

namespace PHPGoogleAPI;

require_once 'vendor/autoload.php';

//TODO: get attachment
class GmailAPI
{
	private $service;

	/**
	 * query for filtering messages from gmail. for understanding the format follow this :
	 * https://developers.google.com/gmail/api/v1/reference/query-parameters
	 * @var string
	 */
	private $query;

	private $limit;

	public function __construct($query = 'is:unread', $limit = 10)
	{
		$this->query = $query;
		$this->limit = $limit;
	}

	public function fetchMailById()
	{
		try {
		    	//get connection first.
				$client = $this->getConnection();
				$this->service = new Google_Service_Gmail($client);

				//get mailIds
				$mailIds = $this->fetchMailIds();

				//get mails by id    $message = $service->users_messages->get($userId, $messageId);
			  	$message = $service->users_messages->get($userId, $messageId);
		        return $message;
	      	} 
	    catch (Exception $e) {
	        throw new Exception($e);
	    	}

	}

	public function fetchMailIds()
	{
		$pageToken = NULL;
  		$messages = [];
  		$params = [];

	    try {

		      $params['maxResults'] = $this->limit;

		      //query
		      $params['q'] = $this->query;

		      //excluding trash and spam
		      $params['includeSpamTrash'] = false;

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
			  return $messages;  
		}
	}

	/**
	 * Returns an authorized API client.
	 * @return Google_Client the authorized client object
	 */
	public function getConnection()
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


	public function getService()
	{
		// Get the API client and construct the service object.
		$client = $this->getConnection();
		$service = new Google_Service_Gmail($client);

		$messages = listMessages($service, 'me');
		// Print the labels in the user's account.
		$results = $service->users_labels->listUsersLabels('me');

		var_dump($service);
		die;
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


	/**
	 * Expands the home directory alias '~' to the full path.
	 * @param string $path the path to expand.
	 * @return string the expanded path.
	 */
	public function expandHomeDirectory($path)
	{
	    $homeDirectory = getenv('HOME');
	    if (empty($homeDirectory)) {
	        $homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
	    }
	    return str_replace('~', realpath($homeDirectory), $path);
	}


}
