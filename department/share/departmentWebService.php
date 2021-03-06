<?
	/**
	* Extension of the WebEvent class, specific to creating a new department.
	*/
	require('cascadeWebService.php');
	
	class WSDepartment extends CascadeWebService
	{
		protected $dept = array();
		
		# Predefined (color coded) navigation XSL formats.
		protected $sidenavXSLFormats = array(
			'simple' => 'b753d91ba642566600aa780b1abe9d00',
			'president' => '36216b1aa642566600a7fa9a95ca2fed',
			'academicaffairs' => '3621b607a642566600a7fa9aa07df5b9',
			'educ' => 'aac4987ca64256660057607d857257d8',
			'hmss' => '37909bb5a642566600b689e0bacdca34',
			'scma' => '3837d068a642566600b689e0978a5444',
			'finadmin' => '7ffb9084a642566600b689e000c5ba7b',
			'infotech' => 'ec3607c7a642566601a4033b4d6be5c9',
			'stuaffairs' => '36223913a642566600a7fa9a9735dc7a',
			'advancement' => '36299276a642566600a7fa9a930c75ee',
			'nonschool' => '36229b6fa642566600a7fa9a32944ba8',
			'other' => '36229b6fa642566600a7fa9a32944ba8'
		);
		
		function __construct(array $config)
		{
			$this->service = new SoapClient("PATH TO WSDL", array('trace' => 1));
			
			# Auth must be a user that has write access to whatever folder(s) you are trying to write into.
			$this->auth = array('username' => 'USERNAME', 'password' => 'PASSWORD');
			
			# Set up the department name and system information.
			$this->dept['name'] = $config['name'];
			$this->dept['sysname'] = $this->cleanAssetName($this->dept['name']);
			
			# Set up the placement location and internal assets folder (_cms).
			$this->dept['publicFolderPath'] = $config['parentFolderPath'].'/'.$this->dept['sysname'];
			$this->dept['_cmsFolderPath'] = $this->dept['publicFolderPath'].'/_cms';
			
			# Set up the supplied keywords, if any.
			$this->dept['keywords'] = $config['keywords'];
			
			# Does this department have a Reverse Main Navigation?
			$this->dept['mainNavBlockId'] = !empty($config['hasReverseNav']) ? '4e586d7e7f000001000225f0d87b830f' : '65ba16d0a642566600fee7c4f6f07c1b';
	
			# Set the colored department navigation stylesheet
			$this->dept['navFormatId'] = $this->sidenavXSLFormats[$config['navCategory']];
			
			# Paths used for the configuration sets, content types and asset factories.
			$this->dept['configSetPath'] = $config['configSetPath'].'/'.$this->dept['sysname'];			
			$this->dept['contentTypePath'] = $config['contentTypePath'].'/'.$this->dept['sysname'];			
			$this->dept['assetFactoryPath'] = $config['assetFactoryPath'];
			
			# Does this department have Faculty or Staff assets?
			$this->dept['facOrStaff'] = $config['facOrStaff'];
			
			# Does this department have News or Events?
			$this->dept['hasNews'] = !empty($config['hasNews']) ? true : false;
			$this->dept['hasEvents'] = !empty($config['hasEvents']) ? true : false;
		}
	
	
		/***************************************************
		* Func: create
		* Desc: Creates a department, and all of it's corresponding assets.
		***************************************************/
		function create()
		{						
			# Does the department already exist?
			#	We're assuming the if the public folder exists, everything else does as well.
			if($this->isAssetCreated($this->dept['publicFolderPath'], 'folder'))
				return false;

			# Create the initial public folders.
			if(!$this->setupPublicAssets())					
				return false;
			
			# Create the initial _cms folder.
			if(!$this->setupCMSAssets()) 				
				return false;			
			
			# Create Department Specific Content Type Container
			$data = array(
				'name' => $this->dept['sysname'], 
				'parentContainerPath' => str_replace('/'.$this->dept['sysname'],'',$this->dept['contentTypePath']),
			);
			if(!$this->createContentTypeContainer($data))
				return false;
			
			# Create Department Specific Config Set Container
			$data = array(
				'name' => $this->dept['sysname'], 
				'parentContainerPath' => str_replace('/'.$this->dept['sysname'],'',$this->dept['configSetPath']),
			);
			if(!$this->createConfigSetContainer($data))
				return false;
			
			# Create Department Specific Config Sets and Content Types
			$data = array(
				'name' => '3 Column Page', 
				'parentContainerPath' => $this->dept['configSetPath'],
				'pageConfigurations' => array(
					array(		
						'name' => 'XHTML',	
						'defaultConfiguration' => true, 
						'templateId' => '4e58e3647f000001000225f0d638d965',
						'pageRegions' => array(
							'pageRegion' => array(
								array('name' => 'BASETAG'),
								array('name' => 'BREADCRUMBS', 'blockId' => '4e587c397f000001000225f089f133e0', 'formatId' => '4e58b5e37f000001000225f00b4dc7b8'),
								array('name' => 'CORE CSS', 'blockId' => '4e586df77f000001000225f0f4b74905'),
								array('name' => 'CORE JAVASCRIPT', 'blockId' => '4e586dba7f000001000225f09248e621'),
								array('name' => 'CUSTOM JAVASCRIPT', 'blockId' => '4e587c8a7f000001000225f0fe8d3165', 'formatId' => 'f9bcf644a6425666015205a42d997b42'),
								array('name' => 'CUSTOM CSS', 'blockId' => '4e587c8a7f000001000225f0fe8d3165', 'formatId' => 'f9b0bb5aa6425666015205a4cf067547'),
								array('name' => 'DEFAULT', 'blockId' => '4e587c8a7f000001000225f0fe8d3165', 'formatId' => '4e58c4f57f000001000225f077cdee3c'),
								array('name' => 'FOOTER', 'blockId' => '4e586c777f000001000225f00867285b'),
								array('name' => 'GOOGLE ANALYTICS', 'blockId' => '4e5875af7f000001000225f0e884f4c4'),
								array('name' => 'HEADER', 'blockId' => '4e586cc77f000001000225f01ecea644'),
								array('name' => 'MODULE1'),
								array('name' => 'MODULE2'),
								array('name' => 'MODULE3'),
								array('name' => 'MODULE4', 'blockId' => '4e587c8a7f000001000225f0fe8d3165', 'formatId' => '4e58dc3b7f000001000225f04a19a126'),
								array('name' => 'MAIN IMG', 'blockId' => '4e587c8a7f000001000225f0fe8d3165', 'formatId' => '4e58dc547f000001000225f08a1525b3'),
								array('name' => 'QUICKLINKS', 'blockPath' => $this->dept['_cmsFolderPath'].'/blocks/index/quick links', 'formatId' => '4e58dbe77f000001000225f0bfc0dd69'),
								array('name' => 'RIGHTMODULE1'),
								array('name' => 'RIGHTMODULE2'),
								array('name' => 'RIGHTMODULE3'),
								array('name' => 'RIGHTMODULE4', 'blockId' => '4e587c8a7f000001000225f0fe8d3165', 'formatId' => '4e58d50d7f000001000225f049886eb4'),
								array('name' => 'SIDENAV', 'blockPath' => $this->dept['_cmsFolderPath'].'/blocks/index/'.$this->dept['name'].' index', 'formatId' => $this->dept['navFormatId']),
								array('name' => 'TOP LEVEL NAVIGATION', 'blockId' => $this->dept['mainNavBlockId'])
							)
						)
					),
					array(
						'name' => 'XML',
						'defaultConfiguration' => false,
						'templateId' => 'f0b837c5a642566600aa780b8fbd67f3',
						'pageRegions' => array(
							'pageRegion' => array(
								array('name' => 'DEFAULT')
							)
						)					
					)					
				)
			);
			if(!$this->createConfigSet($data))
				return false;
				
			# Create a content type for the 3 column page
			$data = array(
				'name' => '3 Column Page', 
				'parentContainerPath' => $this->dept['contentTypePath'],
				'pageConfigurationSetPath' => $this->dept['configSetPath'].'/3 Column Page',
				'metadataSetId' => '6a3ae375a642566600fee7c4b8213c52',
				'structuredDataDefinitionId' => 'e6d52091a642566601a4033bb8c77134'
			);
			if(!$this->createContentType($data))
				return false;
			
			$data = array(
				'name' => '2 Column Page', 
				'parentContainerPath' => $this->dept['configSetPath'],
				'pageConfigurations' => array(
					array(		
						'name' => 'XHTML',	
						'defaultConfiguration' => true, 
						'templateId' => '4e58e3117f000001000225f0ad7bb753',
						'pageRegions' => array(
							'pageRegion' => array(
								array('name' => 'BASETAG'),
								array('name' => 'BREADCRUMBS', 'blockId' => '4e587c397f000001000225f089f133e0', 'formatId' => '4e58b5e37f000001000225f00b4dc7b8'),
								array('name' => 'CORE CSS', 'blockId' => '4e586df77f000001000225f0f4b74905'),
								array('name' => 'CORE JAVASCRIPT', 'blockId' => '4e586dba7f000001000225f09248e621'),
								array('name' => 'CUSTOM JAVASCRIPT', 'blockId' => '4e587c8a7f000001000225f0fe8d3165', 'formatId' => 'f9bcf644a6425666015205a42d997b42'),
								array('name' => 'CUSTOM CSS', 'blockId' => '4e587c8a7f000001000225f0fe8d3165', 'formatId' => 'f9b0bb5aa6425666015205a4cf067547'),
								array('name' => 'DEFAULT', 'blockId' => '4e587c8a7f000001000225f0fe8d3165', 'formatId' => '4e58cbca7f000001000225f020792cec'),
								array('name' => 'FOOTER', 'blockId' => '4e586c777f000001000225f00867285b'),
								array('name' => 'GOOGLE ANALYTICS', 'blockId' => '4e5875af7f000001000225f0e884f4c4'),
								array('name' => 'HEADER', 'blockId' => '4e586cc77f000001000225f01ecea644'),
								array('name' => 'MODULE1'),
								array('name' => 'MODULE2'),
								array('name' => 'MODULE3'),
								array('name' => 'MODULE4', 'blockId' => '4e587c8a7f000001000225f0fe8d3165', 'formatId' => '4e58dc3b7f000001000225f04a19a126'),
								array('name' => 'MAIN IMG', 'blockId' => '4e587c8a7f000001000225f0fe8d3165', 'formatId' => '4e58dc547f000001000225f08a1525b3'),
								array('name' => 'QUICKLINKS', 'blockPath' => $this->dept['_cmsFolderPath'].'/blocks/index/quick links', 'formatId' => '4e58dbe77f000001000225f0bfc0dd69'),
								array('name' => 'SIDENAV', 'blockPath' => $this->dept['_cmsFolderPath'].'/blocks/index/'.$this->dept['name'].' index', 'formatId' => $this->dept['navFormatId']),
								array('name' => 'TOP LEVEL NAVIGATION', 'blockId' => $this->dept['mainNavBlockId'])
							)
						)
					),
					array(
						'name' => 'XML',
						'defaultConfiguration' => false,
						'templateId' => 'f0b837c5a642566600aa780b8fbd67f3',
						'pageRegions' => array(
							'pageRegion' => array(
								array('name' => 'DEFAULT')
							)
						)					
					)
				)
			);
			if(!$this->createConfigSet($data))
				return false;
				
			# Create a content type for the 2 column page
			$data = array(
				'name' => '2 Column Page', 
				'parentContainerPath' => $this->dept['contentTypePath'],
				'pageConfigurationSetPath' => $this->dept['configSetPath'].'/2 Column Page',
				'metadataSetId' => '6a3ae375a642566600fee7c4b8213c52',
				'structuredDataDefinitionId' => '23855b76a642566600aa780bb4f87f0d'
			);
			if(!$this->createContentType($data))
				return false;
							
		
			# Create an index page, doing this after the initial setup functions
			#	because we need the index blocks to be created first.
			$data = array(
				'name' => 'index', 
				'parentFolderPath'	=>  $this->dept['publicFolderPath'],
				'shouldBePublished' => true,
				'shouldBeIndexed' => true,
				'metadata' => array(
					'displayName' => $this->dept['name'].' Home',
					'title' => $this->dept['name'],
					'keywords' => $this->dept['keywords']					
				),
				'contentTypePath' => $this->dept['contentTypePath'].'/3 Column Page',
				'structuredData'  => array(
					'structuredDataNodes' => array(
						'structuredDataNode' => array(
							array(
								'type' => 'group',
								'identifier' => 'nav_col',
								'structuredDataNodes' => array(
									'structuredDataNode' => array(
										array('type' => 'asset', 'identifier' => 'module', 'assetType' => 'page', 'pageId' => '')
									)
								)
							),
							array(
								'type' => 'group',
								'identifier' => 'main_img',
								'structuredDataNodes' => array(
									'structuredDataNode' => array(
										array('type' => 'asset', 'identifier' => 'page-content', 'assetType' => 'file', 'fileId' => '')
									)
								)
							),
							array(
								'type' => 'group',
								'identifier' => 'left_col',
								'structuredDataNodes' => array(
									'structuredDataNode' => array(
										array('type' => 'text', 'identifier' => 'title', 'text' => 'Title Goes Here'),
										array('type' => 'text', 'identifier' => 'content', 'text' => '<p>Place the page\'s content here.</p>')
									)
								)
							),
							array(
								'type' => 'group',
								'identifier' => 'right_col_blocks',
								'structuredDataNodes' => array(
									'structuredDataNode' => array(
										array('type' => 'asset', 'identifier' => 'page-content', 'assetType' => 'page', 'pageId' => '')
									)
								)
							)
						)
					)
				)
			);

			if(!$this->createPage($data))
				return false;
			
			# Create a config set for the faculty/staff profile asset if necessary
			if($this->dept['facOrStaff'] == 'faculty' || $this->dept['facOrStaff'] == 'staff')
			{
				$data = array(
					'name' => ucfirst($this->dept['facOrStaff']).' Listing', 
					'parentContainerPath' => $this->dept['configSetPath'],
					'pageConfigurations' => array(
						array(		
							'name' => 'XHTML',	
							'defaultConfiguration' => true, 
							'templateId' => '4e58e3117f000001000225f0ad7bb753',
							'pageRegions' => array(
								'pageRegion' => array(
									array('name' => 'BASETAG'),
									array('name' => 'BREADCRUMBS', 'blockId' => '4e587c397f000001000225f089f133e0', 'formatId' => '4e58b5e37f000001000225f00b4dc7b8'),
									array('name' => 'CORE CSS', 'blockId' => '4e586df77f000001000225f0f4b74905'),
									array('name' => 'CORE JAVASCRIPT', 'blockId' => '4e586dba7f000001000225f09248e621'),
									array('name' => 'CUSTOM JAVASCRIPT', 'blockId' => '4e587c8a7f000001000225f0fe8d3165', 'formatId' => 'f9bcf644a6425666015205a42d997b42'),
									array('name' => 'CUSTOM CSS', 'blockId' => '4e587c8a7f000001000225f0fe8d3165', 'formatId' => 'f9b0bb5aa6425666015205a4cf067547'),
									array(
										'name' => 'DEFAULT', 
										'blockId' => '4e587c8a7f000001000225f0fe8d3165', 
										'formatId' => ($this->dept['facOrStaff'] == 'faculty' ? '4e58cc4f7f000001000225f0c575678b' : '4e58cc687f000001000225f062293aee')
									),
									array('name' => 'FOOTER', 'blockId' => '4e586c777f000001000225f00867285b'),
									array('name' => 'GOOGLE ANALYTICS', 'blockId' => '4e5875af7f000001000225f0e884f4c4'),
									array('name' => 'HEADER', 'blockId' => '4e586cc77f000001000225f01ecea644'),
									array('name' => 'MODULE1'),
									array('name' => 'MODULE2'),
									array('name' => 'MODULE3'),
									array('name' => 'MODULE4', 'blockId' => '4e587c8a7f000001000225f0fe8d3165', 'formatId' => '4e58dc3b7f000001000225f04a19a126'),
									array('name' => 'MAIN IMG', 'blockId' => '4e587c8a7f000001000225f0fe8d3165', 'formatId' => '4e58dc547f000001000225f08a1525b3'),
									array('name' => 'QUICKLINKS', 'blockPath' => $this->dept['_cmsFolderPath'].'/blocks/index/quick links', 'formatId' => '4e58dbe77f000001000225f0bfc0dd69'),
									array('name' => 'SIDENAV', 'blockPath' => $this->dept['_cmsFolderPath'].'/blocks/index/'.$this->dept['name'].' index', 'formatId' => $this->dept['navFormatId']),
									array('name' => 'TOP LEVEL NAVIGATION', 'blockId' => $this->dept['mainNavBlockId'])
								)
							)
						),
						array(
							'name' => 'XML',
							'defaultConfiguration' => false,
							'templateId' => 'f0b837c5a642566600aa780b8fbd67f3',
							'pageRegions' => array(
								'pageRegion' => array(
									array('name' => 'DEFAULT')
								)
							)					
						)
					)
				);
				if(!$this->createConfigSet($data))
					return false;
					
				# Create a content type for the faculty/staff listing
				$data = array(
					'name' => ucfirst($this->dept['facOrStaff']).' Listing', 
					'parentContainerPath' => $this->dept['contentTypePath'],
					'pageConfigurationSetPath' => $this->dept['configSetPath'].'/'.ucfirst($this->dept['facOrStaff']).' Listing',
					'metadataSetId' => '6a3ae375a642566600fee7c4b8213c52',
					'structuredDataDefinitionId' => ($this->dept['facOrStaff'] == 'faculty' ? '4c1a9007a642566600a7fa9aaec32f96' : '9139fea7a642566601b6903fdb52d28c')
				);
				if(!$this->createContentType($data))
					return false;
				
				$data = array(
					'name' => ucfirst($this->dept['facOrStaff']).' Profile', 
					'parentContainerPath' => $this->dept['configSetPath'],
					'pageConfigurations' => array(
						array(		
							'name' => 'XHTML',	
							'defaultConfiguration' => true, 
							'templateId' => '4e58e3117f000001000225f0ad7bb753',
							'pageRegions' => array(
								'pageRegion' => array(
									array('name' => 'BASETAG'),
									array('name' => 'BREADCRUMBS', 'blockId' => '4e587c397f000001000225f089f133e0', 'formatId' => '4e58b5e37f000001000225f00b4dc7b8'),
									array('name' => 'CORE CSS', 'blockId' => '4e586df77f000001000225f0f4b74905'),
									array('name' => 'CORE JAVASCRIPT', 'blockId' => '4e586dba7f000001000225f09248e621'),
									array('name' => 'CUSTOM JAVASCRIPT', 'blockId' => '4e587c8a7f000001000225f0fe8d3165', 'formatId' => 'f9bcf644a6425666015205a42d997b42'),
									array('name' => 'CUSTOM CSS', 'blockId' => '4e587c8a7f000001000225f0fe8d3165', 'formatId' => 'f9b0bb5aa6425666015205a4cf067547'),
									array(
										'name' => 'DEFAULT', 
										'blockId' => '4e587c8a7f000001000225f0fe8d3165', 
										'formatId' => ($this->dept['facOrStaff'] == 'faculty' ? '4e58ccbc7f000001000225f06e3581da' : '4e58cc8a7f000001000225f0b5ee4e80')
									),
									array('name' => 'FOOTER', 'blockId' => '4e586c777f000001000225f00867285b'),
									array('name' => 'GOOGLE ANALYTICS', 'blockId' => '4e5875af7f000001000225f0e884f4c4'),
									array('name' => 'HEADER', 'blockId' => '4e586cc77f000001000225f01ecea644'),
									array('name' => 'MODULE1'),
									array('name' => 'MODULE2'),
									array('name' => 'MODULE3'),
									array('name' => 'MODULE4', 'blockId' => '4e587c8a7f000001000225f0fe8d3165', 'formatId' => '4e58dc3b7f000001000225f04a19a126'),
									array('name' => 'MAIN IMG', 'blockId' => '4e587c8a7f000001000225f0fe8d3165', 'formatId' => '4e58dc547f000001000225f08a1525b3'),
									array('name' => 'QUICKLINKS', 'blockPath' => $this->dept['_cmsFolderPath'].'/blocks/index/quick links', 'formatId' => '4e58dbe77f000001000225f0bfc0dd69'),
									array('name' => 'SIDENAV', 'blockPath' => $this->dept['_cmsFolderPath'].'/blocks/index/'.$this->dept['name'].' index', 'formatId' => $this->dept['navFormatId']),
									array('name' => 'TOP LEVEL NAVIGATION', 'blockId' => $this->dept['mainNavBlockId'])
								)
							)
						),
						array(
							'name' => 'XML',
							'defaultConfiguration' => false,
							'templateId' => 'f0b837c5a642566600aa780b8fbd67f3',
							'pageRegions' => array(
								'pageRegion' => array(
									array('name' => 'DEFAULT')
								)
							)					
						)						
					)
				);
				if(!$this->createConfigSet($data))
					return false;
					
				# Create a content type for the faculty/staff profile
				$data = array(
					'name' => ucfirst($this->dept['facOrStaff']).' Profile', 
					'parentContainerPath' => $this->dept['contentTypePath'],
					'pageConfigurationSetPath' => $this->dept['configSetPath'].'/'.ucfirst($this->dept['facOrStaff']).' Profile',
					'metadataSetId' => '6a3ae375a642566600fee7c4b8213c52',
					'structuredDataDefinitionId' => ($this->dept['facOrStaff'] == 'faculty' ? '4c0f66a0a642566600a7fa9a8ba0462c' : '912cec8fa642566601b6903f6c5e170e')
				);
				if(!$this->createContentType($data))
					return false;
			}
							
			if($this->dept['facOrStaff'] == 'faculty')
			{
				# Create an index page for faculty
				$data = array(
					'name' => 'index', 
					'parentFolderPath'	=>  $this->dept['publicFolderPath'].'/faculty',
					'shouldBePublished' => true,
					'shouldBeIndexed' => true,
					'metadata' => array(
						'displayName' => 'Faculty & Staff',
						'title' => 'Faculty & Staff',
						'keywords' => $this->dept['keywords']					
					),
					'contentTypePath' => $this->dept['contentTypePath'].'/'.ucfirst($this->dept['facOrStaff']).' Listing',
					'structuredData'  => array(
						'structuredDataNodes' => array(
							'structuredDataNode' => array(
								array(
									'type' => 'group',
									'identifier' => 'main_img',
									'structuredDataNodes' => array(
										'structuredDataNode' => array(
											array('type' => 'asset', 'identifier' => 'page-content', 'assetType' => 'file', 'fileId' => ''),
										)
									)
								),
								array(
									'type' => 'group',
									'identifier' => 'faculty',
									'structuredDataNodes' => array(
										'structuredDataNode' => array(
											array('type' => 'asset', 'identifier' => 'page-content', 'assetType' => 'page', 'pageId' => '')
										)
									)
								)
							)
						)
					)
				);
	
				if(!$this->createPage($data))
					return false;
			}
			
			if($this->dept['facOrStaff'] == 'staff')
			{
				# Create an index page for staff
				$data = array(
					'name' => 'index',
					'parentFolderPath' => $this->dept['publicFolderPath'].'/staff',
					'shouldBePublished' => true,
					'shouldBeIndexed' => true,
					'metadata' => array(
						'displayName' => 'Staff',
						'title' => 'Staff',
						'keywords' => $this->dept['keywords']
					),
					'contentTypePath' => $this->dept['contentTypePath'].'/'.ucfirst($this->dept['facOrStaff']).' Listing',
					'structuredData' => array(
						'structuredDataNodes' => array(
							'structuredDataNode' => array(
								array(
									'type' => 'group',
									'identifier' => 'main_img',
									'structuredDataNodes' => array(
										'structuredDataNode' => array(
											array('type' => 'asset', 'identifier' => 'page-content', 'assetType' => 'file', 'fileId' => ''),
										)
									)
								),
								array(
									'type' => 'group',
									'identifier' => 'staff',
									'structuredDataNodes' => array(
										'structuredDataNode' => array(
											array('type' => 'asset', 'identifier' => 'page-content', 'assetType' => 'page', 'pageId' => '')
										)
									)
								)
							)
						)
					)
				);
	
				if(!$this->createPage($data))
					return false;
			}
			
			if($this->dept['hasNews'] === true)
			{
				$data = array(
					'name' => 'News Article', 
					'parentContainerPath' => $this->dept['configSetPath'],
					'pageConfigurations' => array(
						array(		
							'name' => 'XHTML',	
							'defaultConfiguration' => true, 
							'templateId' => '4e58e3117f000001000225f0ad7bb753',
							'pageRegions' => array(
								'pageRegion' => array(
									array('name' => 'BASETAG'),
									array('name' => 'BREADCRUMBS', 'blockId' => '4e587c397f000001000225f089f133e0', 'formatId' => '4e58b5e37f000001000225f00b4dc7b8'),
									array('name' => 'CORE CSS', 'blockId' => '4e586df77f000001000225f0f4b74905'),
									array('name' => 'CORE JAVASCRIPT', 'blockId' => '4e586dba7f000001000225f09248e621'),
									array('name' => 'CUSTOM JAVASCRIPT', 'blockId' => '4e587c8a7f000001000225f0fe8d3165', 'formatId' => 'f9bcf644a6425666015205a42d997b42'),
									array('name' => 'CUSTOM CSS', 'blockId' => '4e587c8a7f000001000225f0fe8d3165', 'formatId' => 'f9b0bb5aa6425666015205a4cf067547'),
									array('name' => 'DEFAULT', 'blockId' => '4e587c8a7f000001000225f0fe8d3165', 'formatId' => '541608737f000001000225f05e37af42'),
									array('name' => 'FOOTER', 'blockId' => '4e586c777f000001000225f00867285b'),
									array('name' => 'GOOGLE ANALYTICS', 'blockId' => '4e5875af7f000001000225f0e884f4c4'),
									array('name' => 'HEADER', 'blockId' => '4e586cc77f000001000225f01ecea644'),
									array('name' => 'MODULE1'),
									array('name' => 'MODULE2'),
									array('name' => 'MODULE3'),
									array('name' => 'MODULE4', 'blockId' => '4e587c8a7f000001000225f0fe8d3165', 'formatId' => '4e58dc3b7f000001000225f04a19a126'),
									array('name' => 'MAIN IMG', 'blockId' => '4e587c8a7f000001000225f0fe8d3165', 'formatId' => '4e58dc547f000001000225f08a1525b3'),
									array('name' => 'QUICKLINKS', 'blockPath' => $this->dept['_cmsFolderPath'].'/blocks/index/quick links', 'formatId' => '4e58dbe77f000001000225f0bfc0dd69'),
									array('name' => 'SIDENAV', 'blockPath' => $this->dept['_cmsFolderPath'].'/blocks/index/'.$this->dept['name'].' index', 'formatId' => $this->dept['navFormatId']),
									array('name' => 'TOP LEVEL NAVIGATION', 'blockId' => $this->dept['mainNavBlockId'])
								)
							)
						),
						array(
							'name' => 'XML',
							'defaultConfiguration' => false,
							'templateId' => 'f0b837c5a642566600aa780b8fbd67f3',
							'pageRegions' => array(
								'pageRegion' => array(
									array('name' => 'DEFAULT')
								)
							)					
						)						
					)
				);
				if(!$this->createConfigSet($data))
					return false;
					
				# Create a content type for the news article
				$data = array(
					'name' => 'News Article', 
					'parentContainerPath' => $this->dept['contentTypePath'],
					'pageConfigurationSetPath' => $this->dept['configSetPath'].'/News Article',
					'metadataSetId' => 'cc429785a642566601a4033b4ec06996',
					'structuredDataDefinitionId' => '1e30e9baa642566600aa780bf9963678'
				);
				if(!$this->createContentType($data))
					return false;
			}
			
			if($this->dept['hasEvents'] === true)
			{
				$data = array(
					'name' => 'Event', 
					'parentContainerPath' => $this->dept['configSetPath'],
					'pageConfigurations' => array(
						array(		
							'name' => 'XHTML',	
							'defaultConfiguration' => true, 
							'templateId' => '4e58e3117f000001000225f0ad7bb753',
							'pageRegions' => array(
								'pageRegion' => array(
									array('name' => 'BASETAG'),
									array('name' => 'BREADCRUMBS', 'blockId' => '4e587c397f000001000225f089f133e0', 'formatId' => '4e58b5e37f000001000225f00b4dc7b8'),
									array('name' => 'CORE CSS', 'blockId' => '4e586df77f000001000225f0f4b74905'),
									array('name' => 'CORE JAVASCRIPT', 'blockId' => '4e586dba7f000001000225f09248e621'),
									array('name' => 'CUSTOM JAVASCRIPT', 'blockId' => '4e587c8a7f000001000225f0fe8d3165', 'formatId' => 'f9bcf644a6425666015205a42d997b42'),
									array('name' => 'CUSTOM CSS', 'blockId' => '4e587c8a7f000001000225f0fe8d3165', 'formatId' => 'f9b0bb5aa6425666015205a4cf067547'),
									array('name' => 'DEFAULT', 'blockId' => '4e587c8a7f000001000225f0fe8d3165', 'formatId' => '5898903d7f000001000225f06c7a9c87'),
									array('name' => 'FOOTER', 'blockId' => '4e586c777f000001000225f00867285b'),
									array('name' => 'GOOGLE ANALYTICS', 'blockId' => '4e5875af7f000001000225f0e884f4c4'),
									array('name' => 'HEADER', 'blockId' => '4e586cc77f000001000225f01ecea644'),
									array('name' => 'MODULE1'),
									array('name' => 'MODULE2'),
									array('name' => 'MODULE3'),
									array('name' => 'MODULE4', 'blockId' => '4e587c8a7f000001000225f0fe8d3165', 'formatId' => '4e58dc3b7f000001000225f04a19a126'),
									array('name' => 'MAIN IMG', 'blockId' => '4e587c8a7f000001000225f0fe8d3165', 'formatId' => '4e58dc547f000001000225f08a1525b3'),
									array('name' => 'QUICKLINKS', 'blockPath' => $this->dept['_cmsFolderPath'].'/blocks/index/quick links', 'formatId' => '4e58dbe77f000001000225f0bfc0dd69'),
									array('name' => 'SIDENAV', 'blockPath' => $this->dept['_cmsFolderPath'].'/blocks/index/'.$this->dept['name'].' index', 'formatId' => $this->dept['navFormatId']),
									array('name' => 'TOP LEVEL NAVIGATION', 'blockId' => $this->dept['mainNavBlockId'])
								)
							)
						),
						array(
							'name' => 'XML',
							'defaultConfiguration' => false,
							'templateId' => 'f0b837c5a642566600aa780b8fbd67f3',
							'pageRegions' => array(
								'pageRegion' => array(
									array('name' => 'DEFAULT')
								)
							)					
						)						
					)
				);
				if(!$this->createConfigSet($data))
					return false;
					
				# Create a content type for the event
				$data = array(
					'name' => 'Event', 
					'parentContainerPath' => $this->dept['contentTypePath'],
					'pageConfigurationSetPath' => $this->dept['configSetPath'].'/Event',
					'metadataSetId' => 'cc429785a642566601a4033b4ec06996',
					'structuredDataDefinitionId' => 'c7d96c4fa642566601a4033b0c585821'
				);
				if(!$this->createContentType($data))
					return false;
			}
				
			# Create the user group for the department.
			$data = array(			
				'groupName' => $this->dept['name'],
				'role' => 'contributor',
				'groupStartingPagePath' => $this->dept['publicFolderPath'].'/index',
				'groupBaseFolderPath' => $this->dept['publicFolderPath'],
				'wysiwygAllowFontAssignment' => true,
		        'wysiwygAllowFontFormatting' => true,
		        'wysiwygAllowTextFormatting' => true,
		        'wysiwygAllowViewSource' => true,
		        'wysiwygAllowImageInsertion' => true,
		        'wysiwygAllowTableInsertion' => true
			);
			
			if(!$this->createUserGroup($data))
				return false;
				
			# Create the asset factories for the department.
			if(!$this->setupAssetFactories())
				return false;
			
			# Edit the user group to account for the created asset factory container.
			$data = array(
				'groupName' => $this->dept['name'],
				'groupAssetFactoryContainerPath' => $this->dept['assetFactoryPath'].'/'.$this->dept['name'],
			);
			
			if(!$this->editUserGroup($data))
				return false;
			
			unset($data);
			
			return true;
		}
		
		
		/***************************************************
		* Func: setupPublicAssets
		* Desc: Creates the necessary assets for the millersville folder.
		*		- millersville/departmentPath
		*		- millersville/departmentPath/img
		*		- millersville/departmentPath/quick links
		*		- millersville/departmentPath/shared blocks
		*		- millersville/departmentPath/files
		*		- millersville/departmentPath/facultypages
		*		- millersville/departmentPath/announcements
		*			- archives folder
		*		- millersville/departmentPath/events
		*			- archives folder
		***************************************************/
		function setupPublicAssets()
		{
			# Create the root public folder
			$data = array(
				'name' => $this->dept['sysname'], 
				'parentFolderPath' => str_replace('/'.$this->dept['sysname'], '', $this->dept['publicFolderPath']),
				'metadataSetPath' => '/Millersville/No Fields',
				'shouldBeIndexed' => 1,
				'shouldBePublished' => 1
			);						
			if(!$this->createFolder($data))			
				return false;
					
			# Create the img folder
			$data = array(
				'name' => 'img', 
				'parentFolderPath' => $this->dept['publicFolderPath'],
				'metadataSetPath' => '/Millersville/No Fields',
				'shouldBeIndexed' => 0,
				'shouldBePublished' => 1
			);						
			if(!$this->createFolder($data))
				return false;
				
			# Create the files folder
			$data = array(
				'name' => 'files', 
				'parentFolderPath' => $this->dept['publicFolderPath'],
				'metadataSetPath' => '/Millersville/No Fields',
				'shouldBeIndexed' => 0,
				'shouldBePublished' => 1
			);						
			if(!$this->createFolder($data))
				return false;
				
			if($this->dept['facOrStaff'] == 'faculty')
			{
				# Create the faculty folder
				$data = array(
					'name' => 'faculty', 
					'parentFolderPath' => $this->dept['publicFolderPath'],
					'metadataSetPath' => '/Millersville/No Fields',
					'shouldBeIndexed' => 1,
					'shouldBePublished' => 1
				);						
				if(!$this->createFolder($data))
					return false;
			}
			else if($this->dept['facOrStaff'] == 'staff')
			{
				# Create the staff folder
				$data = array(
					'name' => 'staff', 
					'parentFolderPath' => $this->dept['publicFolderPath'],
					'metadataSetPath' => '/Millersville/No Fields',
					'shouldBeIndexed' => 1,
					'shouldBePublished' => 1
				);						
				if(!$this->createFolder($data))
					return false;
			}
			
			if($this->dept['hasNews'] === true)
			{
				# Create the announcements folder
				$data = array(
					'name' => 'announcements', 
					'parentFolderPath' => $this->dept['publicFolderPath'],
					'metadataSetPath' => '/Millersville/No Fields',
					'shouldBeIndexed' => 0,
					'shouldBePublished' => 1
				);						
				if(!$this->createFolder($data))
					return false;
	
				# Create the announcements/archives folder
				$data = array(
					'name' => 'archives', 
					'parentFolderPath' => $this->dept['publicFolderPath'].'/announcements',
					'metadataSetPath' => '/Millersville/No Fields',
					'shouldBeIndexed' => 0,
					'shouldBePublished' => 1
				);						
				if(!$this->createFolder($data))
					return false;
			}
			
			if($this->dept['hasEvents'] === true)
			{
				# Create the events folder
				$data = array(
					'name' => 'events', 
					'parentFolderPath' => $this->dept['publicFolderPath'],
					'metadataSetPath' => '/Millersville/No Fields',
					'shouldBeIndexed' => 0,
					'shouldBePublished' => 1
				);						
				if(!$this->createFolder($data))
					return false;
					
				# Create the events/archives folder				
				$data = array(
					'name' => 'archives', 
					'parentFolderPath' => $this->dept['publicFolderPath'].'/events',
					'metadataSetPath' => '/Millersville/No Fields',
					'shouldBeIndexed' => 0,
					'shouldBePublished' => 1
				);						
				if(!$this->createFolder($data))
					return false;
			}
			
			return true;
		}
		
		
		/***************************************************
		* Func: setupCMSAssets
		* Desc: Creates the necessary assets for the millersville/_cms folder.
		*		- millersville/departmentPath/_cms
		*		- millersville/departmentPath/_cms/shared blocks
		*			- millersville/departmentPath/_cms/shared blocks/Nav Column
		*			- millersville/departmentPath/_cms/shared blocks/Right Column
		*		- millersville/departmentPath/_cms/stylesheets
		*		- millersville/departmentPath/_cms/templates
		*		- millersville/departmentPath/_cms/blocks
		*			- millersville/departmentPath/_cms/blocks/index
		*				- department index
		*				- announcements index
		*				- events index
		*				- quick links index
		*			- millersville/departmentPath/_cms/blocks/xhtml
		*		- millersville/departmentPath/_cms/assets
		*			- 2 Column Page
		*			- 3 Column Page
		*			- Announcement
		*			- Department Event
		*			- Faculty Profile
		*			- Left Block
		*			- Right Block
		***************************************************/
		function setupCMSAssets()
		{
			# Create the root _cms folder
			$data = array(
				'name' => '_cms', 
				'parentFolderPath' => str_replace('/_cms', '', $this->dept['_cmsFolderPath']),
				'metadataSetPath' => '/Millersville/No Fields',
				'shouldBeIndexed' => 0,
				'shouldBePublished' => 0
			);
			if(!$this->createFolder($data))
				return false;
			
			# Create the quick links folder
			$data = array(
				'name' => 'quick links', 
				'parentFolderPath' => $this->dept['_cmsFolderPath'],
				'metadataSetPath' => '/Millersville/No Fields',
				'shouldBeIndexed' => 0,
				'shouldBePublished' => 0
			);
			if(!$this->createFolder($data))
				return false;
				
			# Create the shared blocks folder
			$data = array(
				'name' => 'shared blocks', 
				'parentFolderPath'	=>  $this->dept['_cmsFolderPath'],
				'metadataSetPath' => '/Millersville/No Fields',
				'shouldBeIndexed' => 0,
				'shouldBePublished' => 0
			);
			if(!$this->createFolder($data))
				return false;
				
			# Create the Sidebar (Left Column) shared blocks folder			
			$data = array(
				'name' => 'Nav Column', 
				'parentFolderPath'	=>  $this->dept['_cmsFolderPath'].'/shared blocks',
				'metadataSetPath' => '/Millersville/No Fields',
				'shouldBeIndexed' => 0,
				'shouldBePublished' => 0
			);
			if(!$this->createFolder($data))
				return false;
				
			# Create the Right Column shared blocks folder
			$data = array(
				'name' => 'Right Column', 
				'parentFolderPath'	=>  $this->dept['_cmsFolderPath'].'/shared blocks',
				'metadataSetPath' => '/Millersville/No Fields',
				'shouldBeIndexed' => 0,
				'shouldBePublished' => 0
			);
			if(!$this->createFolder($data))
				return false;
			
			# Create the blocks folder (contains index and xhtml blocks)
			#	If successful, create the necessary index blocks.
			$data = array(
				'name' => 'blocks', 
				'parentFolderPath' => $this->dept['_cmsFolderPath'],
				'metadataSetPath' => '/Millersville/No Fields',
				'shouldBeIndexed' => 0,
				'shouldBePublished' => 0
			);			
			if(!$this->createFolder($data))
				return false;

			# Create the blocks/xhtml folder			
			$data = array(
				'name' => 'xhtml', 
				'parentFolderPath' => $this->dept['_cmsFolderPath'].'/blocks',
				'metadataSetPath' => '/Millersville/No Fields',
				'shouldBeIndexed' => 0,
				'shouldBePublished' => 0
			);						
			if(!$this->createFolder($data))
				return false;
			
			# Create the blocks/index folder
			$data = array(
				'name' => 'index', 
				'parentFolderPath' => $this->dept['_cmsFolderPath'].'/blocks',
				'metadataSetPath' => '/Millersville/No Fields',
				'shouldBeIndexed' => 0,
				'shouldBePublished' => 0
			);			
			if(!$this->createFolder($data))						
				return false;

			# Create the department index
			$data = array(
				'name' => $this->dept['name'].' index',
				'parentFolderPath' => $this->dept['_cmsFolderPath'].'/blocks/index',
                'metadataSetPath' => '/Millersville/No Fields',
                'indexedFolderPath' => $this->dept['publicFolderPath'],
                'maxRenderedAssets' => 0,
                'depthOfIndex' => 4,
                'indexPages' => 1,
                'indexLinks' => 1,
                'includeCurrentPageXML' => 1,
                'indexRegularContent' => 1,
                'indexUserMetadata' => 1,
                'sortMethod' => 'folder-order'
			);
			if(!$result = $this->createIndexBlock($data))
				return false;
			
			if($this->dept['hasNews'] === true)
			{
				# Create the announcements index
				$data = array(
					'name' => 'announcements',
					'parentFolderPath' => $this->dept['_cmsFolderPath'].'/blocks/index',
	                'metadataSetPath' => '/Millersville/No Fields',
	                'indexedFolderPath' => $this->dept['publicFolderPath'].'/announcements',
	                'maxRenderedAssets' => 0,
	                'depthOfIndex' => 4,
	                'indexPages' => 1,
	                'indexLinks' => 1,
	                'includeCurrentPageXML' => 1,
	                'indexRegularContent' => 1,
	                'indexUserMetadata' => 1,
	                'sortMethod' => 'folder-order'
				);
				if(!$result = $this->createIndexBlock($data))
					return false;
			}
			
			if($this->dept['hasEvents'] === true)
			{			
				# Create the events index
				$data = array(
					'name' => 'events',
					'parentFolderPath' => $this->dept['_cmsFolderPath'].'/blocks/index',
	                'metadataSetPath' => '/Millersville/No Fields',
	                'indexedFolderPath' => $this->dept['publicFolderPath'].'/events',
	                'maxRenderedAssets' => 0,
	                'depthOfIndex' => 4,
	                'indexPages' => 1,
	                'indexLinks' => 1,
	                'includeCurrentPageXML' => 1,
	                'indexRegularContent' => 1,
	                'indexUserMetadata' => 1,
	                'sortMethod' => 'folder-order'
				);
				if(!$result = $this->createIndexBlock($data))
					return false;
			}
						
			# Create the quick links index
			$data = array(
				'name' => 'quick links',
				'parentFolderPath' => $this->dept['_cmsFolderPath'].'/blocks/index',
                'metadataSetPath' => '/Millersville/No Fields',
                'indexedFolderPath' => $this->dept['_cmsFolderPath'].'/quick links',
                'maxRenderedAssets' => 0,
                'depthOfIndex' => 4,
                'indexPages' => 1,
                'indexLinks' => 1,
                'includeCurrentPageXML' => 1,
                'indexRegularContent' => 1,
                'indexUserMetadata' => 1,
                'sortMethod' => 'folder-order'
			);
			if(!$result = $this->createIndexBlock($data))
				return false;
				
			if($this->dept['facOrStaff'] == 'faculty')
			{
				# Create the faculty index
				$data = array(
					'name' => 'faculty',
					'parentFolderPath' => $this->dept['_cmsFolderPath'].'/blocks/index',
	                'metadataSetPath' => '/Millersville/No Fields',
	                'indexedFolderPath' => $this->dept['publicFolderPath'].'/faculty',
	                'maxRenderedAssets' => 0,
	                'depthOfIndex' => 4,
	                'indexPages' => 1,
	                'indexLinks' => 1,
	                'includeCurrentPageXML' => 1,
	                'indexRegularContent' => 1,
	                'indexUserMetadata' => 1,
	                'sortMethod' => 'folder-order'
				);				
				if(!$result = $this->createIndexBlock($data))
					return false;
			}
			else if($this->dept['facOrStaff'] == 'staff')
			{
				# Create the faculty index
				$data = array(
					'name' => 'staff',
					'parentFolderPath' => $this->dept['_cmsFolderPath'].'/blocks/index',
	                'metadataSetPath' => '/Millersville/No Fields',
	                'indexedFolderPath' => $this->dept['publicFolderPath'].'/staff',
	                'maxRenderedAssets' => 0,
	                'depthOfIndex' => 4,
	                'indexPages' => 1,
	                'indexLinks' => 1,
	                'includeCurrentPageXML' => 1,
	                'indexRegularContent' => 1,
	                'indexUserMetadata' => 1,
	                'sortMethod' => 'folder-order'
				);				
				if(!$result = $this->createIndexBlock($data))
					return false;
			}
			
			return true;
		}
		
		
		/***************************************************
		* Func: setupAssetFactories
		* Desc: Creates the necessary asset factories.
		*			- 2 Column Page
		*			- 3 Column Page
		*			- Announcement
		*			- Department Event
		*			- Faculty Profile
		*			- Left Block
		*			- Right Block
		*			- File
		*			- Folder
		*			- External Link
		***************************************************/
		function setupAssetFactories()
		{
			# Create the base assets folder
			#	If succesful, create the base assets.
			$data = array(
				'name' => 'assets', 
				'parentFolderPath'	=>  $this->dept['_cmsFolderPath'],
				'metadataSetPath' => '/Millersville/No Fields',
				'shouldBeIndexed' => 0,
				'shouldBePublished' => 0
			);			
			if(!$this->createFolder($data))
				return false;
				
			$data = array(
				'name' => '2columnpage',
				'parentFolderPath' => $this->dept['_cmsFolderPath'].'/assets',
				'shouldeBePublished' => true,
				'shouldBeIndexed' => true,
				'metadata' => array(
					'displayName' => '2 Column Page',
					'title' => '2 Column Page',
					'keywords' => $this->dept['keywords']
				),
				'contentTypePath' => $this->dept['contentTypePath'].'/2 Column Page',
				'structuredData' => array(
					'structuredDataNodes' => array(
						'structuredDataNode' => array(
							array(
								'type' => 'group',
								'identifier' => 'main_img',
								'structuredDataNodes' => array(
									'structuredDataNode' => array(
										array('type' => 'asset', 'identifier' => 'page-content', 'assetType' => 'file', 'fileId' => ''),
									)
								)
							),
							array(
								'type' => 'group',
								'identifier' => 'fw_section',
								'structuredDataNodes' => array(
									'structuredDataNode' => array(
										array('type' => 'text', 'identifier' => 'title', 'text' => 'Content Title'),
										array('type' => 'text', 'identifier' => 'content', 'text' => '<p>Place the content for this section here.</p>')
									)
								)
							)
						)
					)
				)
			);

			if(!$this->createPage($data))
				return false;
			
			$data = array(
				'name' => '3columnpage',
				'parentFolderPath' => $this->dept['_cmsFolderPath'].'/assets',
				'shouldBePublished' => true,
				'shouldBeIndexed' => true,
				'metadata' => array(
					'displayName' => '3 Column Page',
					'title' => '3 Column Page',
					'keywords' => $this->dept['keywords']
				),
				'contentTypePath' => $this->dept['contentTypePath'].'/3 Column Page',
				'structuredData' => array(
					'structuredDataNodes' => array(
						'structuredDataNode' => array(
							array(
								'type' => 'group',
								'identifier' => 'nav_col',
								'structuredDataNodes' => array(
									'structuredDataNode' => array(
										array('type' => 'asset', 'identifier' => 'module', 'assetType' => 'page', 'pageId' => ''),
									)
								)
							),
							array(
								'type' => 'group',
								'identifier' => 'main_img',
								'structuredDataNodes' => array(
									'structuredDataNode' => array(
										array('type' => 'asset', 'identifier' => 'page-content', 'assetType' => 'file', 'fileId' => ''),
									)
								)
							),
							array(
								'type' => 'group',
								'identifier' => 'left_col',
								'structuredDataNodes' => array(
									'structuredDataNode' => array(
										array('type' => 'text', 'identifier' => 'title', 'text' => 'Content Title'),
										array('type' => 'text', 'identifier' => 'content', 'text' => '<p>Place the content for this section here.</p>')
									)
								)
							),
							array(
								'type' => 'group',
								'identifier' => 'right_col_blocks',
								'structuredDataNodes' => array(
									'structuredDataNode' => array(
										array('type' => 'asset', 'identifier' => 'page-content', 'assetType' => 'page', 'pageId' => ''),
									)
								)
							)
						)
					)
				)
			);

			if(!$this->createPage($data))
				return false;
				
			if($this->dept['hasNews'] === true)
			{			
				$data = array(
					'name' => 'announcement', 
					'parentFolderPath' => $this->dept['_cmsFolderPath'].'/assets',
					'shouldBePublished' => true,
					'shouldBeIndexed' => true,
					'metadata' => array(
						'displayName' => 'Announcement',
						'title' => 'Announcement',
						'keywords' => $this->dept['keywords'].', announcement'						
					),
					'expirationFolderPath' => $this->dept['publicFolderPath'].'/announcements/archives',
					'contentTypePath' => $this->dept['contentTypePath'].'/News Article',
					'structuredData' => array(										
						'structuredDataNodes' => array(
							'structuredDataNode' => array(
								array(
									'type' => 'group',
									'identifier' => 'fw_section',
									'structuredDataNodes' => array(
										'structuredDataNode' => array(
											array('type' => 'text', 'identifier' => 'start_date', 'text' => date('m-d-Y', time())),
											array('type' => 'text', 'identifier' => 'end_date', 'text' => ''),
											array('type' => 'text', 'identifier' => 'content', 'text' => '<p>Leave this field blank to remove the \'View More\' button for this announcement.</p>')
										)
									)
								)
							)
						)
					)
				);
	
				if(!$this->createPage($data))
					return false;
			}
			
			if($this->dept['hasEvents'] === true)
			{
				$data = array(
					'name' => 'departmentevent', 
					'parentFolderPath' => $this->dept['_cmsFolderPath'].'/assets',
					'shouldBePublished' => true,
					'shouldBeIndexed' => true,
					'metadata' => array(
						'displayName' => 'Department Event',
						'title' => 'Department Event',
						'keywords' => $this->dept['keywords'].', event'
					),
					'expirationFolderPath' => $this->dept['publicFolderPath'].'/events/archives',
					'contentTypePath' => $this->dept['contentTypePath'].'/Event',					
					'structuredData' => array(
						'structuredDataNodes' => array(
							'structuredDataNode' => array(
								array(
									'type' => 'group',
									'identifier' => 'fw_section',
									'structuredDataNodes' => array(
										'structuredDataNode' => array(
											array('type' => 'text', 'identifier' => 'start_date', 'text' => date('m-d-Y', time())),
											array('type' => 'text', 'identifier' => 'end_date', 'text' => ''),
											array('type' => 'text', 'identifier' => 'content', 'text' => '<p>Leave this field blank to remove the \'View More\' button for this event.</p>')
										)
									)
								)
							)
						)
					)
				);
	
				if(!$this->createPage($data))
					return false;
			}
						
			if($this->dept['facOrStaff'] == 'faculty')
			{						
				$data = array(
					'name' => 'facultyprofile',
					'parentFolderPath' => $this->dept['_cmsFolderPath'].'/assets',
					'shouldBePublished' => true,
					'shouldBeIndexed' => false,				
					'metadata' => array(
						'displayName' => 'Faculty Profile',
						'title' => 'Faculty Profile',
						'keywords' => $this->dept['keywords']
					),
					'contentTypePath' => $this->dept['contentTypePath'].'/'.ucfirst($this->dept['facOrStaff']).' Profile',
					'structuredData' => array(
						'structuredDataNodes' => array(
							'structuredDataNode' => array(
								array(
									'type' => 'group',
									'identifier' => 'personal_info',
									'structuredDataNodes' => array(
										'structuredDataNode' => array(
											array('type' => 'text', 'identifier' => 'name', 'text' => ''),
											array('type' => 'asset', 'identifier' => 'img', 'assetType' => 'file', 'fileId' => ''),
											array('type' => 'text', 'identifier' => 'title', 'text' => ''),
											array('type' => 'text', 'identifier' => 'office', 'text' => ''),
											array('type' => 'text', 'identifier' => 'phone', 'text' => ''),
											array('type' => 'text', 'identifier' => 'fax', 'text' => ''),
											array('type' => 'text', 'identifier' => 'email', 'text' => ''),
											array('type' => 'asset', 'identifier' => 'page', 'assetType' => 'page', 'fileId' => ''),
											array('type' => 'text', 'identifier' => 'website', 'text' => 'http://')
										)
									)
								),
								array(
									'type' => 'group',
									'identifier' => 'office_hours',
									'structuredDataNodes' => array(
										'structuredDataNode' => array(
											array('type' => 'text', 'identifier' => 'monday', 'text' => ''),
											array('type' => 'text', 'identifier' => 'tuesday', 'text' => ''),
											array('type' => 'text', 'identifier' => 'wednesday', 'text' => ''),
											array('type' => 'text', 'identifier' => 'thursday', 'text' => ''),
											array('type' => 'text', 'identifier' => 'friday', 'text' => ''),
											array('type' => 'text', 'identifier' => 'comments', 'text' => '')
										)
									)
								),
								array('type' => 'text', 'identifier' => 'extra', 'text' => '')
							)
						)
					)
				);

				if(!$this->createPage($data))
					return false;
			}
			else if($this->dept['facOrStaff'] == 'staff')
			{						
				$data = array(
					'name' => 'staffprofile',
					'parentFolderPath' => $this->dept['_cmsFolderPath'].'/assets',
					'shouldBePublished' => true,
					'shouldBeIndexed' => false,					
					'metadata' => array(
						'displayName' => 'Staff Profile',
						'title' => 'Staff Profile',
						'keywords' => $this->dept['keywords']
					),
					'contentTypePath' => $this->dept['contentTypePath'].'/'.ucfirst($this->dept['facOrStaff']).' Profile',
					'structuredData' => array(					
						'structuredDataNodes' => array(
							'structuredDataNode' => array(
								array(
									'type' => 'group',
									'identifier' => 'personal_info',
									'structuredDataNodes' => array(
										'structuredDataNode' => array(
											array('type' => 'text', 'identifier' => 'name', 'text' => ''),
											array('type' => 'asset', 'identifier' => 'img', 'assetType' => 'file', 'fileId' => ''),
											array('type' => 'text', 'identifier' => 'title', 'text' => ''),
											array('type' => 'text', 'identifier' => 'office', 'text' => ''),
											array('type' => 'text', 'identifier' => 'phone', 'text' => ''),
											array('type' => 'text', 'identifier' => 'fax', 'text' => ''),
											array('type' => 'text', 'identifier' => 'email', 'text' => ''),
											array('type' => 'asset', 'identifier' => 'page', 'assetType' => 'page', 'fileId' => ''),
											array('type' => 'text', 'identifier' => 'website', 'text' => 'http://')
										)
									)
								),
								array('type' => 'text', 'identifier' => 'extra', 'text' => '')
							)
						)
					)
				);

				if(!$this->createPage($data))
					return false;
			}
			
			
			# Create the asset factory container for department.
			$data = array(
				'name' => $this->dept['name'], 
				'parentContainerPath' => '/'.$this->dept['assetFactoryPath'],
				'applicableGroups' => $this->dept['name']
			);
			if(!$this->createAssetFactoryContainer($data))
				return false;
				
			# Create the 2 Column Page asset factory.
			$data = array(
				'name' => '2 Column Page', 
				'parentContainerPath' => $this->dept['assetFactoryPath'].'/'.$this->dept['name'],
				'applicableGroups' => $this->dept['name'],
				'assetType' => 'page',
				'baseAssetPath' => $this->dept['_cmsFolderPath'].'/assets/2columnpage',
				'placementFolderPath' => $this->dept['publicFolderPath'],
				'allowSubFolderPlacement' => 1,
				'workflowMode' => 'none',
				'folderPlacementPosition' => 0
			);
			if(!$this->createAssetFactory($data))
				return false;
				
			# Create the 3 Column Page asset factory.
			$data = array(
				'name' => '3 Column Page', 
				'parentContainerPath' => $this->dept['assetFactoryPath'].'/'.$this->dept['name'],
				'applicableGroups' => $this->dept['name'],
				'assetType' => 'page',
				'baseAssetPath' => $this->dept['_cmsFolderPath'].'/assets/3columnpage',
				'placementFolderPath' => $this->dept['publicFolderPath'],
				'allowSubFolderPlacement' => 1,
				'workflowMode' => 'none',
				'folderPlacementPosition' => 0
			);
			if(!$this->createAssetFactory($data))
				return false;
				
			if($this->dept['hasNews'] === true)
			{
				# Create the Announcement asset factory.
				$data = array(
					'name' => 'Announcement', 
					'parentContainerPath' => $this->dept['assetFactoryPath'].'/'.$this->dept['name'],
					'applicableGroups' => $this->dept['name'],
					'assetType' => 'page',
					'baseAssetPath' => $this->dept['_cmsFolderPath'].'/assets/announcement',
					'placementFolderPath' => $this->dept['publicFolderPath'].'/announcements',
					'allowSubFolderPlacement' => 0,
					'workflowMode' => 'none',
					'folderPlacementPosition' => 0
				);
				if(!$this->createAssetFactory($data))
					return false;
			}
			
			if($this->dept['hasEvents'] === true)
			{	
				# Create the Department Event asset factory.
				$data = array(
					'name' => 'Department Event', 
					'parentContainerPath' => $this->dept['assetFactoryPath'].'/'.$this->dept['name'],
					'applicableGroups' => $this->dept['name'],
					'assetType' => 'page',
					'baseAssetPath' => $this->dept['_cmsFolderPath'].'/assets/departmentevent',
					'placementFolderPath' => $this->dept['publicFolderPath'].'/events',
					'allowSubFolderPlacement' => 0,
					'workflowMode' => 'none',
					'folderPlacementPosition' => 0
				);
				if(!$this->createAssetFactory($data))
					return false;
			}
				
			if($this->dept['facOrStaff'] == 'faculty')
			{
				# Create the Faculty Profile asset factory.
				$data = array(
					'name' => 'Faculty Profile', 
					'parentContainerPath' => $this->dept['assetFactoryPath'].'/'.$this->dept['name'],
					'applicableGroups' => $this->dept['name'],
					'assetType' => 'page',
					'baseAssetPath' => $this->dept['_cmsFolderPath'].'/assets/facultyprofile',
					'placementFolderPath' => $this->dept['publicFolderPath'].'/faculty',
					'allowSubFolderPlacement' => 0,
					'workflowMode' => 'none',
					'folderPlacementPosition' => 0					
				);
				if(!$this->createAssetFactory($data))
					return false;
			}
			else if($this->dept['facOrStaff'] == 'staff')
			{
				# Create the Staff Profile asset factory.
				$data = array(
					'name' => 'Staff Profile', 
					'parentContainerPath' => $this->dept['assetFactoryPath'].'/'.$this->dept['name'],
					'applicableGroups' => $this->dept['name'],
					'assetType' => 'page',
					'baseAssetPath' => $this->dept['_cmsFolderPath'].'/assets/staffprofile',
					'placementFolderPath' => $this->dept['publicFolderPath'].'/staff',
					'allowSubFolderPlacement' => 0,
					'workflowMode' => 'none',
					'folderPlacementPosition' => 0
				);
				if(!$this->createAssetFactory($data))
					return false;
			}
			
			# Create the Left Block asset factory.
			$data = array(
				'name' => 'Left Block', 
				'parentContainerPath' => $this->dept['assetFactoryPath'].'/'.$this->dept['name'],
				'applicableGroups' => $this->dept['name'],
				'assetType' => 'page',
				'baseAssetPath' => 'millersville/_cms/common/assets/modules/navigation col/Left Module',
				'placementFolderPath' => $this->dept['_cmsFolderPath'].'/shared blocks/Nav Column',
				'allowSubFolderPlacement' => 1,
				'workflowMode' => 'none',
				'folderPlacementPosition' => 0
			);
			if(!$this->createAssetFactory($data))
				return false;
				
			# Create the Right Block asset factory.
			$data = array(
				'name' => 'Right Block', 
				'parentContainerPath' => $this->dept['assetFactoryPath'].'/'.$this->dept['name'],
				'applicableGroups' => $this->dept['name'],
				'assetType' => 'page',
				'baseAssetPath' => 'millersville/_cms/common/assets/modules/right col/Right Module',
				'placementFolderPath' => $this->dept['_cmsFolderPath'].'/shared blocks/Right Column',
				'allowSubFolderPlacement' => 1,
				'workflowMode' => 'none',
				'folderPlacementPosition' => 0
			);
			if(!$this->createAssetFactory($data))
				return false;
				
			# Create the File asset factory.
			$data = array(
				'name' => 'File', 
				'parentContainerPath' => $this->dept['assetFactoryPath'].'/'.$this->dept['name'],
				'applicableGroups' => $this->dept['name'],
				'assetType' => 'file',
				'baseAssetPath' => '',
				'placementFolderPath' => $this->dept['publicFolderPath'],
				'allowSubFolderPlacement' => 1,
				'workflowMode' => 'none',
				'folderPlacementPosition' => 0
			);
			if(!$this->createAssetFactory($data))
				return false;
				
			# Create the Folder asset factory.
			$data = array(
				'name' => 'Folder', 
				'parentContainerPath' => $this->dept['assetFactoryPath'].'/'.$this->dept['name'],
				'applicableGroups' => $this->dept['name'],
				'assetType' => 'folder',
				'baseAssetPath' => '',
				'placementFolderPath' => $this->dept['publicFolderPath'],
				'allowSubFolderPlacement' => 1,
				'workflowMode' => 'none',
				'folderPlacementPosition' => 0
			);
			if(!$this->createAssetFactory($data))
				return false;
				
			# Create the External Link asset factory.
			$data = array(
				'name' => 'External Link', 
				'parentContainerPath' => $this->dept['assetFactoryPath'].'/'.$this->dept['name'],
				'applicableGroups' => $this->dept['name'],
				'assetType' => 'symlink',
				'baseAssetPath' => 'millersville/_cms/common/assets/links/External Link',
				'placementFolderPath' => $this->dept['publicFolderPath'],
				'allowSubFolderPlacement' => 1,
				'workflowMode' => 'none',
				'folderPlacementPosition' => 0
			);
			if(!$this->createAssetFactory($data))
				return false;
				
			# Create the Quick Link asset factory.
			$data = array(
				'name' => 'Quick Link', 
				'parentContainerPath' => $this->dept['assetFactoryPath'].'/'.$this->dept['name'],
				'applicableGroups' => $this->dept['name'],
				'assetType' => 'symlink',
				'baseAssetPath' => 'millersville/_cms/common/assets/links/Quick Link',
				'placementFolderPath' => $this->dept['_cmsFolderPath'].'/quick links',
				'allowSubFolderPlacement' => 1,
				'workflowMode' => 'none',
				'folderPlacementPosition' => 0
			);
			if(!$this->createAssetFactory($data))
				return false;
				
			return true;
		}
	}
?>