<?
	/**
	Extension of the WebEvent class, specific to creating a new department.
	
	Changelog:
		
		- 10.7.2009: Ryan Griffith
			The createPage function now takes in a pre-formatted array for the page.
		
		- 10.16.2009: Ryan Griffith
			The pending org page is now created using a Content Type, combining the metadata set, config set, and data definition. 
	*/
	require('cascadeWebEvent.php');
	
	class WEStudentOrg extends CascadeWebEvent
	{		
		protected $org = null;
		protected $sData = array();
		
		function __construct()
		{
			$this->service = new SoapClient("http://cascade.millersville.edu:8080/ws/services/AssetOperationService?wsdl", array('trace' => 1));
			$this->auth = array('username' => '', 'password' => '');
		}
		
		/***************************************************
		*
		* Func: cleanAssetName
		* Desc: Removes chars that make the CMS error on name
		* 
		***************************************************/
		function cleanAssetName($name)
		{
			# remove restricted characters from assetTitle
			$restrictedChars = array("'", '"', '&' , ':', ',', '/', '(', ')', ';', ' ', '&#039;');		# replace this
			return strtolower(str_replace($restrictedChars, '', $name));	
		}
	

		/***************************************************
		*
		* Func: isAssetAlreadyCreated
		* Desc: Checks to see if another asset exists with the
		*		  same path.
		* 
		***************************************************/
		function isAssetAlreadyCreated($path, $type)
		{
			# try and read any asset that has the same path
			$readAsset = $this->readAsset('', $path, $type);

			return $readAsset->readReturn->success == 'true' ? true : false;		
		}
		
				
		function creatOrg($org = null)
		{				
			$this->org = $org;
					
			$basepath = '/millersville/services/csil/organizations/profiles';
			
			$sysname = $this->cleanAssetName($this->org['orgname']);
			
			# Setup the structured data.
			$this->_setupOfficerArray($this->org['officers']);
			$this->_setupAdvisorArray($this->org['advisors']);
			
			# Prepare the selected categories.
			$temp = '';
			if($this->org['category'])
			{
				foreach($this->org['category'] as $cat)
					$temp .= '::CONTENT-XML-SELECTOR::'.str_replace('&','and',$cat);
			}
			$this->org['category'] = $temp;
			
			$this->sData[] = array(
				'type' => 'group',
				'identifier' => 'about',
				'structuredDataNodes' => array(
					'structuredDataNode' => array(
						array('type' => 'text', 'identifier' => 'purpose', 'text' => $this->org['purpose']),
						array('type' => 'text', 'identifier' => 'category', 'text' => $this->org['category']),
						array('type' => 'text', 'identifier' => 'website', 'text' => $this->org['website'] != 'http://' ? $this->org['website'] : ''),
						array('type' => 'text', 'identifier' => 'displayWebsite', 'text' => $this->org['confidential'] == 'Y' ? 'Yes' : 'No'),
						array('type' => 'text', 'identifier' => 'numMembers', 'text' => $this->org['nummembers']),
						array(
							'type' => 'group',
							'identifier' => 'meetings',
							'structuredDataNodes' => array(
								'structuredDataNode' => array(
									array('type' => 'text', 'identifier' => 'day', 'text' => $this->org['day']),
									array('type' => 'text', 'identifier' => 'time', 'text' => $this->org['time']),
									array('type' => 'text', 'identifier' => 'location', 'text' => $this->org['location'])
								)
							)
						)
					)
				)
			);
			
			# Find the expiration date and folder.
			#
			# If the Month is after August, the expiration date needs to be the next year
			# If the Month is August and the Day is greater than 20, the experiation date needs to be the next year
			# If the Month is before August or August 20th, the expiration date needs to be the current year
			#
			$currentDate = getDate();
			
			if(($currentDate['mon'] > 8) || ($currentDate['mon'] == 8 && $currentDate['mday'] > 20))
			{
				$expirationDate = mktime(0,0,0,8,20,$currentDate['year']+1);
				$expirationFolder = $basepath.'/archived/'.$currentDate['year'].'_'.($currentDate['year']+1);
			}
			else
			{
				$expirationDate = mktime(0,0,0,8,20,$currentDate['year']);
				$expirationFolder = $basepath.'/archived/'.($currentDate['year']-1).'_'.$currentDate['year'];
			}
			
			//echo $expirationFolder;
			
			# Check to be sure the archived folder exists (i.e. archives/YYYY).
			#	If not, create the folder.
			if(!$this->isAssetAlreadyCreated($expirationFolder, 'folder'))
			{
				# Create the root public folder
				$data = array('system-name' => str_replace($basepath.'/archived/', '' , $expirationFolder), 'path' => $basepath.'/archived', 'indexable' => 0, 'publishable' => 0);
				if(!$this->createFolder($data))			
					return false;
			}

			# Create the page.
			$data = array(
				'name' => $sysname, 
				'parentFolderPath'	=>  $basepath.'/pending',
				'shouldBePublished' => true,
				'shouldBeIndexed' => true,
				'expirationFolderPath' => $expirationFolder,
				'metadata' => array(
					'displayName' => $this->org['orgname'],
					'title' => $this->org['orgname'],
					'endDate' => $expirationDate					
				),
				'contentTypeId' => '5d9f032c7f000001000225f08612c787',
				'structuredData'  => array(
					'structuredDataNodes' => array(
						'structuredDataNode' => $this->sData
					)
				)
			);

			if(!$this->createPage($data))
				return false;
			
			unset($data);
			
			return true;
		}
		
		
		function _setupOfficerArray($arr)
		{			
			$output = array();
			$officerTitles = array('President', 'Vice President', 'Treasurer', 'Secretary');
			
			$counter = 0;
			
			# Loop through the officers, if their name is blank, don't add them.
			foreach($arr as $officer)
			{
				if(!empty($officer['name']))
				{
					$this->sData[] = array(
						'type' => 'group',
						'identifier' => 'officer',
						'structuredDataNodes' => array(
							'structuredDataNode' => array(
								array('type' => 'text', 'identifier' => 'title', 'text' => $officerTitles[$counter]),
								array('type' => 'text', 'identifier' => 'name', 'text' => $officer['name']),
								array('type' => 'text', 'identifier' => 'email', 'text' => $officer['email']),
								array('type' => 'text', 'identifier' => 'phone', 'text' => $officer['phone']),
								array('type' => 'text', 'identifier' => 'address', 'text' => $officer['address']),
								array('type' => 'text', 'identifier' => 'city', 'text' => $officer['city']),
								array('type' => 'text', 'identifier' => 'state', 'text' => $officer['state']),
								array('type' => 'text', 'identifier' => 'zip', 'text' => $officer['zip'])
							)
						)
					);
				}
				
				$counter++;
			}
			
			return $output;
		}
		
		
		function _setupAdvisorArray($arr)
		{
			$output = array();
			
			# Loop through the advisors, if their name is blank, don't add them.
			foreach($arr as $advisor)
			{
				if(!empty($advisor['name']))
				{
					$this->sData[] = array(
						'type' => 'group',
						'identifier' => 'advisor',
						'structuredDataNodes' => array(
							'structuredDataNode' => array(
								array('type' => 'text', 'identifier' => 'name', 'text' => $advisor['name']),
								array('type' => 'text', 'identifier' => 'email', 'text' => $advisor['email']),
								array('type' => 'text', 'identifier' => 'extension', 'text' => $advisor['phone']),
								array('type' => 'text', 'identifier' => 'building', 'text' => $advisor['building'])
							)
		
						)
					);
				}
			}
			
			return $output;
		}
		
	}
		
?>