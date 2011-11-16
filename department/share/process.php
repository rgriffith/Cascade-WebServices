<?
	die();
	
	session_start();

	require('departmentWebService.php');

	$_SESSION = $_POST;

	if($_SESSION['name'] == '' || strlen($_SESSION['name']) < 2)
	{
		$_SESSION['errors'][] = 'A valid department name was not entered.';
	}
	
	if(isset($_SESSION['errors']))
	{
		header('Location: index.php');
		die();
	}
	else
	{	
		$config = array(
			'name' => $_SESSION['name'],
			'keywords' => $_SESSION['keywords'],
			'facOrStaff' => $_SESSION['facOrStaff'],
			'hasNews' => (!empty($_SESSION['hasNews']) ? true : false),
			'hasEvents' => (!empty($data['hasEvents']) ? true : false),
			'hasReverseNav' => (!empty($data['reverseMainNav']) ? true : false),
			'navCategory' => $_SESSION['type'],
			'parentFolderPath' => $_SESSION['publicpath'],
			'configSetPath' => $_SESSION['configsetpath'],			
			'contentTypePath' => $_SESSION['contenttypepath'],			
			'assetFactoryPath' => $_SESSION['assetpath']
		);
		
		$department = new WSDepartment($config);	
		
		$department->create();
		
		foreach($department->getLog() as $msg)
			echo $msg."\r\n";
			
		die();

		
	/*  Sample readAsset call for debugging purposes.
		echo '<pre>';
		print_r($webEvent->readAsset('3974ea05a6425666019b9b1b4378dfcc', '', 'file'));
		echo '</pre>';
		die();
	*/
	}
?>