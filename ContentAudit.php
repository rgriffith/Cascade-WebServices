<?php
	/**
	* Extension of the Cascade Web Service class.
	*/
	require('CascadeWebService.php');
	
	class ContentAudit extends CascadeWebService
	{		
		public $flaggedAssets = array();
		
		private $rootPath = '';
		private $ageTrigger = 31556926;
		private $auditableAssets = array('page','file','folder');
		
		function __construct()
		{	
			// Set the SOAP service.
			$this->setService('');
			
			// Set the authenication information.
			// Note: user must have read access to the rootPath folder.
			$this->setAuthentication('', '');
			
		    $args = array_shift(func_get_args());  
		    
		    // Define the folder to start scanning.
		  	if (isset($args['rootPath']) && !empty($args['rootPath'])) {
		  		$this->rootPath = $args['rootPath'];
		  	} else {
		  		die('You must specify a root path.');
		  	}
		  
		  	// Define the a trigger for asset age, in seconds. Default: 1 year.
		    if (isset($args['ageTrigger']) 
		    	&& !empty($args['ageTrigger']) 
		    	&& is_numeric($args['ageTrigger'])
		    	) {
		    	$this->ageTrigger = $ageTrigger;
		    }
		}
		
		/**
		 * Perform an audit on dated content using the SOAP service.
		 *
		 * @param  array $auditArgs
		 * @return stdClass
		 */
		public function performContentAudit()
		{						
			try {
				if (!$this->_auditFolder(array('path'=>$this->rootPath))) {
					throw new Exception('There was a problem auditing root folder. SOAP RESPONSE: ' 
										. parent::getLastServiceResponse());
				}
			} catch (Exception $e) {
				$this->setEventLogItem($e->getMessage(), 'ERROR');								
				return null;
			}
		}
		
		private function _auditFolder($assetArgs)
		{
			$assetArgs['type'] = 'folder';
			if ($response = parent::readAsset($assetArgs)) {			
				if ($children = $response->readReturn->asset->folder->children->child) {
					foreach ($children as $k => $child) {
						if (in_array($child->type, $this->auditableAssets)) {
						
							// We want to skip internal assets...
							if ($child->type == 'folder' && preg_match('/(_cms|assets)/i', $child->path->path)) {
								continue;
							}
						
							$assetArgs = array('id'=>$child->id);									
							$auditFunction = '_audit'.ucfirst($child->type);
							$this->$auditFunction($assetArgs);
						}
					}
				} else {
					return false;
				}
			} else {
				return false;
			}
		}
		
		private function _auditPage($assetArgs)
		{
			$assetArgs['type'] = 'page';			
			if ($response = parent::readAsset($assetArgs)) {
				$lastModified = strtotime($response->readReturn->asset->page->lastModifiedDate);
				if (time()-$lastModified > $this->ageTrigger) {
					array_push($this->flaggedAssets, $response->readReturn->asset->page);
				}
				return true;
			} else {
				return false;
			}
		}
		
		private function _auditFile($assetArgs)
		{
			$assetArgs['type'] = 'file';
			if ($response = parent::readAsset($assetArgs)) {
				$lastModified = strtotime($response->readReturn->asset->file->lastModifiedDate);
				if (time()-$lastModified > $this->ageTrigger) {
					array_push($this->flaggedAssets, $response->readReturn->asset->file);
				}
				return true;
			} else {
				return false;
			}
		}
	}
	
	
?>