<?
	die('You\'re not supposed to be here.');
		
	session_start();

	require('deptWebevent.php');

	$_SESSION = $_POST;

	if($_SESSION['name'] == '' || strlen($_SESSION['name']) < 2)
	{
		$_SESSION['errors'][] = 'A valid deparment name was not entered.';
	}
	
	if(isset($_SESSION['errors']))
	{
		header('Location: department.php');
		die();
	}
	else
	{	
		$config = array(
			'name' => $_SESSION['name'],
			'groupName' => !empty($_SESSION['groupName']) ? $_SESSION['groupName'] : $_SESSION['name'],
			'keywords' => $_SESSION['keywords'],
			'facOrStaff' => $_SESSION['facOrStaff'],
			'hasNews' => (!empty($_SESSION['hasNews']) ? true : false),
			'hasEvents' => (!empty($_SESSION['hasEvents']) ? true : false),
			'hasReverseNav' => (!empty($_SESSION['reverseMainNav']) ? true : false),
			'navCategory' => $_SESSION['type'],
			'parentFolderPath' => $_SESSION['publicpath'],
			'configSetPath' => $_SESSION['configsetpath'],			
			'contentTypePath' => $_SESSION['contenttypepath'],			
			'assetFactoryPath' => $_SESSION['assetpath']
		);
		
		$webEvent = new WEDepartment($config);			
		
		//$webEvent->initialize($_SESSION);
		
		$result = $webEvent->create();
		
		foreach($webEvent->getLog() as $msg)
			echo $msg."\r\n";
			
		die();
		
		/*
		if($result)
		{
			$SESSION['success'] = '<p>Your department was successfully created with the following information.</p>';
			$SESSION['success'] .= '<ul><li>'.$SESSION['name'].'</li><li>'.$SESSION['type'].'</li></ul>';
			
			header('Location: index.php');
			die();
		}
		else
		{
			echo 'Failed....'."\r\n";
			$webEvent->getErrorLog();
		}
		*/
		
	/*  Sample readAsset call for debugging purposes.
		echo '<pre>';
		print_r($webEvent->readAsset('3974ea05a6425666019b9b1b4378dfcc', '', 'file'));
		echo '</pre>';
		die();
	*/
	}
?>