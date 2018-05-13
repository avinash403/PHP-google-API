<?php 

use PHPUnit\Framework\TestCase;
use PHPGoogleAPI\GmailAPI;

class GmailApiTest extends TestCase
{
	public function test_gmailAPI_forSuccess()
	{
		$gmailApi = new GmailAPI;
		$this->assertTrue($gmailApi->getConnection());
	}

}