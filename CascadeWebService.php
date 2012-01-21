<?php

	/**
	* Abstract class to extend the Cascade Server web services API.
	*/
	abstract class CascadeWebService 
	{
		private $_authentication;
		private $_service;
		public $eventLog = array();
		
		/**
	     * Create a new SOAP service object using the supplied WSDL path.
	     *
         * @param  string $wsdlPath
	     */
		protected function setService($wsdlPath)
		{
			$this->_service = new SoapClient($wsdlPath, array('trace' => 1));
		}
		
		/**
		 * Get the SOAP service.
		 *
		 * @return SoapClient
		 */
		protected function getService()
		{
			return $this->_service;
		}
		
		/**
		 * Get the last SOAP service request.
		 *
		 * @return string
		 */
		protected function getLastServiceRequest() 	
		{ 
			return $this->_service->__getLastRequest(); 
		}
		
		/**
		 * Get the last SOAP service response.
		 *
		 * @return string
		 */
		protected function getLastServiceResponse() 	
		{ 
			return $this->_service->__getLastResponse(); 
		}
		
		/**
		 * Set the authentication information for the SOAP service calls.
		 *
		 * @param  string $username
		 * @param  string $password
		 */
		protected function setAuthentication($username, $password)
		{
			$this->_authentication = array('username' => $username, 'password' => $password);
		}
		
		/**
		 * Get the authentication information for the SOAP service calls.
		 *
		 * @return array
		 */
		protected function getAuthentication()
		{
			return $this->_authentication;
		}
		
		/**
		 * Add a message the event log.
		 *
		 * @param  string $message
		 * @param  string $messageType
		 */
		protected function setEventLogItem($message, $messageType = '')
		{
			$this->eventLog[time()] = array('message' => $message, 'messageType' => $messageType);
		}
		
		/**
		 * Get the event log.
		 *
		 * @return array
		 */
		public function getEventLog($printLog = FALSE)
		{			
			if ($printLog !== FALSE) {
				?><pre><?
				print_r($this->eventLog);
				?></pre><?
			} else {
				return $this->eventLog;
			}
		}
		
		/**
		 * Get an asset using the SOAP service.
		 *
		 * @param  array $assetArgs
		 * @param  array $siteArgs
		 * @return stdClass
		 */
		public function readAsset(array $assetArgs, array $siteArgs = null)
		{						
			$assetIdentifier = array();
			
			try {
				// The Asset's type is required when reading.
				if (!isset($assetArgs['type'])) {
					throw new Exception("The asset's type was not supplied");
				} 
				
				// Make sure either the Asset's id or path is supplied.
				if (!isset($assetArgs['id']) && !isset($assetArgs['path'])) {
					throw new Exception("The asset's unique identifier or path was not supplied");
				}
				
				// If site information was not supplied, default to the Global area.
				if (!isset($siteArgs['siteId']) && !isset($siteArgs['siteName'])) {
					$siteArgs = array('siteName' => 'global'); 
				}		
			
				// If the Asset's ID was specified, use the ID.
				// Otherwise, use the Asset's path.
				if ($assetArgs['id']) {
					$assetIdentifier = array('type' => $assetArgs['type'], 'id' => $assetArgs['id']);
				} else {
					$assetIdentifier = array('type' => $assetArgs['type'], 
											 'path' => array('path' => $assetArgs['path']));
					
					// Site information is required when using the Asset's path,
					// append the site information with the Asset's path.						 				 
					$assetIdentifier['path'] = array_merge($assetIdentifier['path'], $siteArgs);						 				
				}
	
				$readParams = array('authentication' => $this->_authentication, 'identifier' => $assetIdentifier); 
								
				// Attempt to read the asset from Cascade.
				if ($response = $this->_service->read($readParams)) {
					switch($response->readReturn->success) {
						case 'true':
							$message = 'SOAP RESPONSE: ' . $this->getLastServiceResponse();
							$this->setEventLogItem($message, 'SUCCESS');	
							
							return $response;
							break;
							
						case 'false':
						default:							
							throw new Exception('Error reading asset. READ RESPONSE: ' . $response->readReturn->message);
							break;
					}
				} else {
					throw new Exception('There was a problem with the SOAP request. SOAP RESPONSE: ' 
										. $this->getLastServiceResponse());
				}
			} catch (Exception $e) {
				$this->setEventLogItem($e->getMessage(), 'ERROR');								
				return null;
			}
		}
		
		/**
		 * Create an asset using the SOAP service.
		 *
		 * @param  array $assetData
		 * @param  string $assetType
		 * @return stdClass
		 */
		public function createAsset(array $assetData, $assetType)
		{
			try {
				$createParams = array('authentication' => $this->_authentication,
									  'asset' => array($assetType => $assetData));
			
				// Attempt to create the asset.
				if ($response = $this->_service->create($createParams)) {
					switch($response->createReturn->success) {
						case 'true':
							$message = $assetType . ' creation successful. SOAP RESPONSE: ' 
										. $this->getLastServiceResponse();
							$this->setEventLogItem($message, 'SUCCESS');	
							
							return $response;
							break;
							
						case 'false':
						default:							
							throw new Exception('Error creating ' . $assetType 
												. ' CREATE RESPONSE: ' . $response->createReturn->message);
							break;
					}
				} else {
					throw new Exception('There was a problem with the SOAP request. SOAP RESPONSE: ' 
										. $this->getLastServiceResponse());
				}
			} catch (Exception $e) {
				$this->setEventLogItem($e->getMessage(), 'ERROR');								
				return null;
			}
		}
		
		/**
		 * Edit an asset using the SOAP service.
		 *
		 * @param  string $assetType
		 * @param  array $assetData
		 * @return stdClass
		 */
		public function editAsset($assetType, $assetData)
		{
			try {
				$editParams = array('authentication' => $this->_authentication, 
									'asset' => array((string)$assetType => $assetData));
								
				// Attempt to create the asset.
				if ($response = $this->_service->edit($editParams)) {
					switch($response->editReturn->success) {
						case 'true':
							$message = $assetType . ' edit successful. SOAP RESPONSE: ' 
										. $this->getLastServiceResponse();
							$this->setEventLogItem($message, 'SUCCESS');	
							
							return $response;
							break;
							
						case 'false':
						default:							
							throw new Exception('Error editing ' . $assetType 
												. ' EDIT RESPONSE: ' . $response->editReturn->message);
							break;
					}
				} else {
					throw new Exception('There was a problem with the SOAP request. SOAP RESPONSE: ' 
										. $this->getLastServiceResponse());
				}
			} catch (Exception $e) {
				$this->setEventLogItem($e->getMessage(), 'ERROR');								
				return null;
			}
		}
		
		/**
		 * Helper function to clean up some special characters that cause unexpected content.
		 *
		 * @param  string $str
		 * @return string
		 */
		public function _cleanCharacterEncoding($str)
		{
			// Special Double Quotes.
			$find = array('‰ÛÏ', '‰Û?', '‰Û¢', 'Ò', 'Ó');
			$str = str_replace($find, '"', $str);
			
			// Special Single Quotes.
			$find = array('‰Û÷', '‰Ûª', "Õ", "Ô");
			$str = str_replace($find, "'", $str);
			
			// Everything else.
			$find[] = '‰Û?'; 	// elipsis
			$find[] = '‰ÛÓ';  	// em dash
			$find[] = 'Ð';
			$find[] = '‰ÛÒ';
			$find[] = '‰ÛÒ';  	// en dash
			$find[] = 'Ž';
			
			$replace = array("...", "-", "-", "-", '-', 'e');
			
  			return str_replace($find, $replace, $str);
		}
		
		/**
		 * Helper function to clean up some special characters that cause isues with asset names.
		 *
		 * @param  string $str
		 * @return string
		 */
		public function _cleanAssetName($name)
		{
			// Run through _cleanCharacterEncoding() to clean up special characters.
			$name = $this->_cleanCharacterEncoding($name);
			
			// Remove additional restricted characters.
			$restrictedChars = array("'", '&#039;', '"', '&' , ':', ',', '/', '(', ')', ';', ' ');		# replace this
			return strtolower(str_replace($restrictedChars, '', $name));	
		}
	}

?>
