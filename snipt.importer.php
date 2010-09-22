<?php

/**
 * Snipt.net Importer
 */

// Define the Snipt API settings
define("SNIPT_API",			"http://snipt.net/api");
define("SNIPT_USER",		"/users/robertbanh.");
define("SNIPT_FORMAT",	"json");
define("SNIPT_SNIPT",		"/snipts/");
define("SNIPT_STYLE",		"?style=default");

// Define CodeSnipp.it "API" settings
define( "CODESNIPPIT_USERNAME", "username" );
define( "CODESNIPPIT_PASSWORD", "password" );

// The actual application run
$sniptImporter = new Snipt_Importer();
if( $sniptImporter -> retrieve_user_details() ) :
	// $sniptImporter -> push();
	echo "Application Run Complete\n";
endif;

// ==================================================================================================================================================================================
//		DO NOT EDIT PAST THIS POINT
// ==================================================================================================================================================================================

/**
 * Class Snipt_Importer
 * @package CodeSnipp.it
 * @subpackage API_Test
 * @version 1.0
 * @author JA Clarke
 * @link http://www.jc-interactive.co.za
 * @since 2010/09/22
 */
class Snipt_Importer
{
	private $user_details = null;
	private $snipts		 		= null;
	
	// ==================================================================================================================================================================================
	/**
	 * Constructor
	 */
	// ==================================================================================================================================================================================
	public function __construct()
	{
		$this -> Snipt_Importer();
	}
	
	public function Snipt_Importer()
	{
		echo "Application Started...\n";
		return;
	}
	
	// ==================================================================================================================================================================================
	/**
	 * Retrieval
	 */
	// ==================================================================================================================================================================================
	public function retrieve_user_details()
	{
		// Get the stream to the user page via the Snipt API
		$request = new HTTPRequest( SNIPT_API . SNIPT_USER . SNIPT_FORMAT, HTTP_METH_GET );
		echo "Connecting to Snipt API....\n";
		$request -> send();
		if( $request -> getResponseCode() == 200 ) :
			$this -> user_details = json_decode( $request -> getResponseBody() );
			echo "Snipt entries to retrieve : {$this->user_details->count}\n";
			foreach( $this -> user_details -> snipts as $snipt ) :
				// Retrieve the snipt entry
				$request = new HTTPRequest( SNIPT_API . SNIPT_SNIPT . $snipt . "." . SNIPT_FORMAT . SNIPT_STYLE );
				$request -> send();
				if( $request -> getResponseCode() == 200 ) :
					$this -> snipts[$snipt] = json_decode( $request -> getResponseBody() );
				else :
					echo "[ERROR] Could not retrieve the data for snipt entry ${snipt}\n";
				endif;
			endforeach;
			return true;
		else :
			echo "Invalid data received, exiting....\n";
			return false;
		endif;
	}
	
	// ==================================================================================================================================================================================
	/**
	 * CodeSnipp.it Push
	 */
	// ==================================================================================================================================================================================
	public function push()
	{
		// Snippet details
		$snippet 	= new CodeSnippit();
		$lib 			= new CodeSnippit_Library();
		
		foreach( $this -> snipts as $id => $snipt ) :
			$snippet -> title 		= $snipt -> description;
			$snippet -> tags 			= $snipt -> tags;
			$snippet -> code 			= $snipt -> code;
			$snippet -> type			=	0;	// 0 - Normal, 1 - Question
			
			$codesnippit_categories = array(
				"html", "css", "php", "rubyy", "asp", "Obj C", "wordpress", "joomla", "javascript", "drupal", "python", "perl", "13 ??", "sql", "java", "regex", "coldfusion", "18 ??", 
				"htaccess", "actionscript", "codeigniter", "c#", "magento", "zend", "vb", "c++", "typo", "shell", "applescript", "linux", "c", "µformats", "cakephp" );
			$category = array_search( $snipt -> lexer, $codesnippit_categories );
			$snippet -> category 	= $category ? $category : 3; // Default : PHP
			
			if( $lib -> push( CODESNIPPIT_USERNAME, CODESNIPPIT_PASSWORD, $snippet ) ) :
				echo "Snipt ${id} was successfully imported\n";
			else :
				echo "[ERROR] ErrorCode : {$lib->errorCode}\tError Message : {$lib->errorMsg}\n";
				echo "[ERROR] Snipt ${id} could not be imported\n";
			endif;
		endforeach;
		
		echo "Importing completed....\n";
	}
}

/**
 * Class CodesSnippit
 *
 * @package CodeSnipp.it
 * @subpackage API_Test
 * @version 1.0
 * @author JA Clarke
 * @link http://www.jc-interactive.co.za
 * @since 2010/08/24
 * 
 */
class CodeSnippit
{
	/**
	 * The title of the snippet
	 */
	var $title 			=	null;
	/**
	 * The tags of the snippet (array) 
	 */
	var $tags 			=	null;
	/**
	 * The code of the snippet 
	 */
	var $code 			=	null;
	/**
	 * The type of snippet (question or normal post)
	 */
	var $type 			=	null;
	/**
	 * The category of the snippet
	 */
	var $category 	=	null;

	// ==================================================================================================================================================================================
	/**
	 * Constructor
	 */
	// ==================================================================================================================================================================================
	public function CodeSnippit()
	{
		return;
	}
	
	public function __construct()
	{
		$this -> CodeSnippit();
	}
	// ==================================================================================================================================================================================
	
	// ==================================================================================================================================================================================
	/**
	 * Getters and setters
	 */
	// ==================================================================================================================================================================================
	public function __set( $name, $value )
	{
		if( property_exists( $this, $name ) ) :
			$this -> $name = $value;
		else :
			return false;
		endif;
	}
	
	public function __get( $name )
	{
		if( property_exists( $this, $name ) ) :
			return $this -> $name;
		else :
			return false;
		endif;
	}
	// ==================================================================================================================================================================================
}

class CodeSnippit_Library
{
	var $errorCode 		= 0;	// Error code
	var $errorMsg			= ""; // Error message
	var $httpHandler	=	null;
	var $url					=	"http://codesnipp.it/";
	var $authurl			=	"index.php?m=login";
	var $authHidden		=	array( "m" => "login", "xsubmit" => "Y" );
	var $saveUrl			=	"index.php";
	var $saveHidden		=	array( "m" => "snippit_ajax_save" );
	var $authToken		= null;
	var $cookies			=	null;
	var $userAgent 		= "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_4; en-US) AppleWebKit/533.4 (KHTML, like Gecko) Chrome/5.0.375.127 Safari/533.4";
	
	// ==================================================================================================================================================================================
	/**
	 * Push the snipp.it
	 */
	// ==================================================================================================================================================================================
	public function push( $username, $password, $snippit )
	{
		// Verification check
		if( !$this -> verify( $snippit ) ) :
			return false;
		endif;
		
		if( !$this -> auth( $username, $password ) ) :
			return false;
		endif;
		
		// Setup the next push
		$this -> setupSecurity();
		
		return $this -> sendSnippet( $snippit );
	}
	// ==================================================================================================================================================================================
	
	// ==================================================================================================================================================================================
	/**
	 * Private functions
	 */
	// ==================================================================================================================================================================================	
	/**
	 * Verifies that the important/required fields are completed
	 */
	private function verify( $snippit )
	{
		// Check the title
		if( empty( $snippit -> title ) || is_null( $snippit -> title ) ) :
			$this -> errorCode 	= 1;
			$this -> errorMsg 	= "No title specified"; 
			return false;
		endif;
		
		// Check the code
		if( empty( $snippit -> code ) || is_null( $snippit -> code ) ) :
			$this -> errorCode	=	2;
			$this -> errorMsg		= "No code/data specified";
			return false; 
		endif;
		
		return true;
	}
	
	/**
	 * Authentication function
	 * @description
	 * Does what it's name says it does :P
	 */
	private	function auth( $username = '', $password = '' )
	{
		if( empty( $username ) || empty( $password ) ) :
			$this -> errorCode 	= 3;
			$this -> errorMsg		= "Username/Password invalid";
			return false;
		endif;
		
		// Setup the request
		$this -> httpHandler = new HTTPRequest( $this -> url . $this -> authurl, HTTP_METH_POST );
		// Enable cookies
		$this -> httpHandler -> enableCookies();
		// Set content-type
		$this -> httpHandler -> setContentType( "application/x-www-form-urlencoded" );
		// Setup headers
		$this -> httpHandler -> addHeaders( 
				array( 
						"Accept" => "*/*", 
						"Origin" => $this -> url, 
						"Referer" => $this -> url, 
						"User-Agent" => $this -> userAgent
				) 
			);
		// Setup the form data
		$form_data = array(
				"email"			=>	$username,
				"password"	=>	$password
			);
		$form_data = array_merge( $form_data, $this -> authHidden );
		$this -> httpHandler -> addPostFields( $form_data );
		// Send the request
		$this -> httpHandler -> send();
		
		if( $this -> httpHandler -> getResponseCode() != 302 ) :
			$this -> errorCode	= 4;
			$this -> errorMsg		=	"Authentication failed";
			return false;
		endif;
		
		return true;
	}
	
	/**
	 * Gets the cookies generated from the authentication and saves them along with the auth token :)
	 */
	private function setupSecurity()
	{
		$rd = $this -> httpHandler -> getResponseData();

		$counter = 0;
		foreach( $rd['headers']['Set-Cookie'] as $cookie ) :
			$cd = explode( "=", $cookie );
			$this -> cookies[$cd[0]] = rtrim( $cd[1], "; path" );
			if( $counter == 0 ) :
				$this -> authToken = array( "name" => $cd[0], "value" => rtrim( $cd[1], "; path" ) );
				$counter ++;
			endif;
		endforeach;
	}
	
	/**
	 * Tries to save the snippet *holds thumbs* :)
	 */
	private function sendSnippet( $snippit )
	{
		$post_fields = array(
				"categoryId"	=>	$snippit -> category,
				"name"				=>	htmlspecialchars( $snippit -> title ),
				"body"				=>	htmlspecialchars( $snippit -> code ),
				"tag1"				=>	$snippit -> tags[0],
				"tag2"				=>	$snippit -> tags[1],
				"tag3"				=>	$snippit -> tags[2],
				"tag4"				=>	$snippit -> tags[3],
				"type"				=>	($snippit -> type == 1 ? "question" : "regular"),
			);
		$post_fields[$this -> authToken["name"]] = $this -> authToken['value'];
		
		// Setup the request
		$this -> httpHandler = new HTTPRequest( $this -> url, HTTP_METH_POST );
		// Enable cookies
		$this -> httpHandler -> enableCookies();
		// Set content-type
		$this -> httpHandler -> setContentType( "application/x-www-form-urlencoded" );
		
		$this -> httpHandler -> setCookies( $this -> cookies );
		$this -> httpHandler -> setHeaders( 
				array( 
						"X-Requested-With" => "XMLHttpRequest",
						"Accept" => "*/*", 
						"Origin" => $this -> url, 
						"Referer" => $this -> url . "index.php?m=timeline&scope=everyone", 
						"User-Agent" => $this -> userAgent
				) 
			);
		
		// Setup the form data
		$form_data = array_merge( $this -> saveHidden, $post_fields );
		$this -> httpHandler -> addPostFields( $form_data );
		
		// Send the request
		$this -> httpHandler -> send();
		
		// Check if the addition was a success
		if( $this -> httpHandler -> getResponseBody() != "success" ) :
			$this -> errorCode	= 5;
			$this -> errorMsg		=	"Snippet save failed";
			return false;
		endif;
		
		return true;
	}
	// ==================================================================================================================================================================================
}

// Close Application
exit();

/* End of File : snipt.importer.php */