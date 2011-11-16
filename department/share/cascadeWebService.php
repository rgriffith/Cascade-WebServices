<?
	/**
	* Class CascadeWebEvent
	* Description: Provides functions to create, edit, read assets belonging to Cascade Server.
	
	Changelog:
		
		- 4.20.2009: Ryan Griffith
			Added new method (cleanWordCharacters) to clean up special characters created in rich text editors, namely Word & Outlook.
			
		- 8.31.2009: Ryan Griffith
			With the upgrade to v6.2, the read fznction now requires a siteId when trying to read based on path.

		- 10.2.2009: Ryan Griffith
			Added new methods for page congiruation sets (and containers) and content types.
		
		- 10.6.2009: Ryan Griffith
			Re-added a couple of attributes (configSetPath and metadataSetPath) to the createPage function due to issues with the calendar event script. 
			
		- 10.6.2009: Ryan Griffith
			The createPage function now takes in a pre-formatted array for the page's information.
			
	*/	
	abstract class CascadeWebService 
	{
		public $auth;
		public $service;
		public $eventlog = array();
				
		public function getLastRequest() 	{ return $this->service->__getLastRequest(); }
		public function getLastResponse() 	{ return $this->service->__getLastResponse(); }
		public function getLog() 			{ return $this->eventlog; }
		public function getService() 		{ return $this->service; }
		
		/********************************************************************
		* Function: readAsset
		* Description: Read asset in and return result
		*********************************************************************/
		public function readAsset($assetID = '', $assetPath = '', $type)
		{				
			#set up params
			if($assetID != '')
				$id = array('type' => $type, 'id' => $assetID);
			elseif($assetPath != '')
				$id = array('type' => $type, 'path' => array('path' => $assetPath, 'siteName' => 'global'));
			else
				die('No id or path set');

			$readParams = array('authentication' => $this->auth, 'identifier' => $id); 

			# read in asset from CMS
			$asset = $this->service->read($readParams);

			return $asset;
		}
		
		
		/********************************************************************
		* Function: editAsset
		* Description: Edit given asset in the system.
		*********************************************************************/
		public function editAsset($params, $type = '')
		{		
			try
			{
				$out = $this->service->edit($params);

				if($out->editReturn->success == true)
				{
					$result = $out->editReturn;
					
					$msg = '<div style="background-color: #eaf1f6; border: 1px dashed #d3dde5; margin: 5px 0; padding: 10px;"><p>Edit Asset of type "'.$type.'" Successful.</p>';
				}	
				else
				{
					$result = false;
					
					$msg = '<div style="background-color: #f6eaea; border: 1px dashed #e5d3d3; margin: 5px 0; padding: 10px;"><p>Edit Asset of type "'.$type.'" Failed.</p>';
				}
					
				$msg .= '<p><pre>' . print_r($out) . '</pre></p>';
				$msg .= '</div>';
							
				$this->eventlog[] = $msg;
			}
			catch(Exception $e)
			{	
				$msg = '<div style="background-color: #f6eaea; border: 1px dashed #e5d3d3; margin: 5px 0; padding: 10px;"><p>Edit Asset of type "'.$type.'" Failed.</p>';
				$msg .= '<pre>'.print_r($e, 1).'</pre></p></div>';
				
				$this->eventlog[] = $msg;
				
				$result = false;
			}

			return $result;
		}
		
		
		/********************************************************************
		* Function: createAsset
		* Description: Create asset in the system, if the asset is a group, the
		* result is slightly different (there is no 'createReturn').
		*********************************************************************/
		public function createAsset($params, $type = '')
		{		
			try
			{
				$out = $this->service->create($params);
				
				if($out->createReturn->success == true || $out->success == true)
				{
					$result = $out->createReturn ? $out->createReturn : $out;
					
					$msg = '<div style="background-color: #eaf1f6; border: 1px dashed #d3dde5; margin: 5px 0; padding: 10px;"><p>Create Asset of type "'.$type.'" Successful.</p>';
				}	
				else
				{
					$result = false;
					
					$msg = '<div style="background-color: #f6eaea; border: 1px dashed #e5d3d3; margin: 5px 0; padding: 10px;"><p>Create Asset of type "'.$type.'" Failed.</p>';
				}
					
				$msg .= '<p><pre>' . print_r($out, 1) . '</pre></p>';
				$msg .= '</div>';
							
				$this->eventlog[] = $msg;
				
			}
			catch(Exception $e)
			{	
				$msg = '<div style="background-color: #f6eaea; border: 1px dashed #e5d3d3; margin: 5px 0; padding: 10px;"><p>Create Asset of type "'.$type.'" Failed.</p>';
				$msg .= '<pre>'.print_r($e, 1).'</pre></p></div>';
				
				$this->eventlog[] = $msg;
				
				$result = false;
			}

			return $result;
		}
		
		
		/********************************************************************
		* Function: createUserGroup
		* Description: Assembles the appropriate structure for a group and calls
		* the generic createAsset function.
		*********************************************************************/
		public function createUserGroup($data)
		{
			$params = 
				array(
					'authentication' => $this->auth,
					'asset' => array(
						'group' => $data
					)
				);
			
			return $this->createAsset($params, 'Group');
		}
		
		
		/********************************************************************
		* Function: editUserGroup
		* Description: Assembles the appropriate structure for a group and calls
		* the generic editAsset function.
		*********************************************************************/
		public function editUserGroup($data)
		{
			$params = 
				array(
					'authentication' => $this->auth,
					'asset' => array(
						'group' => $data
					)
				);

			return $this->editAsset($params, 'Group');
		}
		
		
		/********************************************************************
		* Function: createFolder
		* Description: Assembles the appropriate structure for a folder and calls
		* the generic createAsset function.
		*********************************************************************/
		public function createFolder($data)
		{
			$params = 
				array(
					'authentication' => $this->auth,
						'asset' => array(
							'folder' => $data
						)
					);
			
			return $this->createAsset($params, 'Folder');
		}
		
		
		/********************************************************************
		* Function: createFile
		* Description: Assembles the appropriate structure for a file and calls
		* the generic createAsset function.
		*********************************************************************/
		public function createFile($data)
		{
			$params = 
				array(
					'authentication' => $this->auth,
						'asset' => array(
							'file' => $data
						)
					);
			
			return $this->createAsset($params, 'File');
		}
		
		
		/********************************************************************
		* Function: createTemplate
		* Description: Assembles the appropriate structure for a template and calls
		* the generic createAsset function.
		*********************************************************************/
		public function createTemplate($data)
		{
			$params = 
				array(
					'authentication' => $this->auth,
						'asset' => array(
							'template' => array(
								'name' => $data['system-name'], 
								'parentFolderPath'	=>  $data['path'],
								'targetPath' => 'millersville',
								'xml' => $data['xml'],
								'pageRegions' => $data['page-regions']
							)
						)
					);
			
			return $this->createAsset($params, 'Template');
		}
		
		
		/********************************************************************
		* Function: createPage
		* Description: Assembles the appropriate structure for a page and calls
		* the generic createAsset function.
		*********************************************************************/
		public function createPage($data)
		{
			$params = 
				array(
					'authentication' => $this->auth,
					'asset' => array(						
						'page' => $data
					)	
				);
			
			//if(isset($data['workflowConfiguration']))
				//$params['asset']['workflowConfiguration'] = $data['workflowConfiguration'];
			
			return $this->createAsset($params, 'Page');
		}
		
		
		/********************************************************************
		* Function: createIndexBlock
		* Description: Assembles the appropriate structure for an index block
		* and calls the generic createAsset function.
		*********************************************************************/
		public function createIndexBlock($data)
		{
			$params = 
				array(
					'authentication' => $this->auth,
					'asset' => array(
						'indexBlock' => $data
					)
				);
	
			return $this->createAsset($params, 'Index Block');
		}
		
		
		/********************************************************************
		* Function: createAssetFactoryContainer
		* Description: Assembles the appropriate structure for an asset factory container.
		*********************************************************************/
		public function createAssetFactoryContainer($data)
		{
			$params = 
				array(
					'authentication' => $this->auth,
						'asset' => array(
							'assetFactoryContainer' => $data
						)
					);
	
			return $this->createAsset($params, 'Asset Factory Container');
		}
		
		
		/********************************************************************
		* Function: createAssetFactory
		* Description: Assembles the appropriate structure for an asset factory.
		*********************************************************************/
		public function createAssetFactory($data)
		{
			$params = 
				array(
					'authentication' => $this->auth,
						'asset' => array(
							'assetFactory' => $data
						)
					);

			return $this->createAsset($params, 'Asset Factory');
		}
		
		/********************************************************************
		* Function: createConfigSetContainer
		* Description: Assembles the appropriate structure for a configuration set container.
		*********************************************************************/
		public function createConfigSetContainer($data)
		{
			$params = 
				array(
					'authentication' => $this->auth,
						'asset' => array(
							'pageConfigurationSetContainer' => $data
						)
					);
	
			return $this->createAsset($params, 'Page Configuration Set Container');
		}
		
		/********************************************************************
		* Function: createConfigSet
		* Description: Assembles the appropriate structure for a configuration set.
		*********************************************************************/
		public function createConfigSet($data)
		{
			$params = 
				array(
					'authentication' => $this->auth,
						'asset' => array(
							'pageConfigurationSet' => $data
						)
					);

			return $this->createAsset($params, 'Page Configuration Set');
		}
		
		/********************************************************************
		* Function: createContentTypeContainer
		* Description: Assembles the appropriate structure for a content type container.
		*********************************************************************/
		public function createContentTypeContainer($data)
		{
			$params = 
				array(
					'authentication' => $this->auth,
						'asset' => array(
							'contentTypeContainer' => $data
						)
					);
	
			return $this->createAsset($params, 'Content Type Container');
		}
		
		/********************************************************************
		* Function: createContentType
		* Description: Assembles the appropriate structure for a content type.
		*********************************************************************/
		public function createContentType($data)
		{
			$params = 
				array(
					'authentication' => $this->auth,
						'asset' => array(
							'contentType' => $data
						)
					);

			return $this->createAsset($params, 'Content Type');
		}
		
		public function clean_character_encoding($str)
		{
			# Special Double Quotes.
			$find = array('���', '��?', '�ۢ', '�', '�');
			$str = str_replace($find, '"', $str);
			
			# Special Single Quotes.
			$find = array('���', '�۪', "�", "�");
			$str = str_replace($find, "'", $str);
			
			# Everything else.
			$find[] = '��?'; 	// elipsis
			$find[] = '...';
			$find[] = '���';  	// em dash
			$find[] = '�';
			$find[] = '���';
			$find[] = '���';  	// en dash
			$find[] = '�';
			
			$replace = array("...", "...", "-", "-", "-", '-', 'e');
			
  			return str_replace($find, $replace, $str);
		}
		
		/***************************************************
		*
		* Func: cleanAssetName
		* Desc: Removes chars that make the CMS error on name
		* 
		***************************************************/
		public function cleanAssetName($name)
		{
			# Run through cleanWordCharacters to remove any special characters.
			$name = $this->clean_character_encoding($name);
			
			# remove restricted characters from assetTitle
			$restrictedChars = array("'", '&#039;', '"', '&' , ':', ',', '/', '(', ')', ';', ' ');		# replace this
			return strtolower(str_replace($restrictedChars, '', $name));	
		}
	

		/***************************************************
		*
		* Func: isAssetCreated
		* Desc: Checks to see if another asset exists with the
		*		  same path.
		* 
		***************************************************/
		public function isAssetCreated($path, $type)
		{
			# try and read any asset that has the same path
			$readAsset = $this->readAsset('', $path, $type);

			return $readAsset->readReturn->success == 'true' ? true : false;		
		}


		/***************************************************
		*
		* Func: getImageData
		* Desc: Grab image data and return its encoded data
		* 
		***************************************************/
		function getImageData($filename)
		{
			$data = file_get_contents($filename);		
			return $data ? base64_encode($data): false;
		}
	}

?>
