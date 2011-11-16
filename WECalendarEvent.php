<?
	/**
	Extension of the WebEvent class, specific to creating a new pending calendar event.
	
	Changelog:
		
		- 4.20.2009: Ryan Griffith
			Added new method (cleanWordCharacters) when creating the event to clean up 
			special characters created in rich text editors, namely Word.
			
		- 6.1.2009: Ryan Griffith
			Added a check with Related Links to ensure the URL has http:// in the beginning
			
		- 10.6.2009: Ryan Griffith
			Updated the asset array to reflect changes made to the web event class (i.e. config set and metadata)
		
		- 10.7.2009: Ryan Griffith
			The createPage function now takes in a pre-formatted array for the page.
		
		- 10.16.2009: Ryan Griffith
			The pending page is now created using a Content Type, combining the metadata set, config set, and data definition. 
	*/
	require('cascadeWebEvent.php');
	
	class WECalendarEvent extends CascadeWebEvent
	{
		protected $event = array();
		
		function __construct()
		{
			# soap service
			$this->service = new SoapClient("http://cascade.millersville.edu:8080/ws/services/AssetOperationService?wsdl", array('trace' => 1));
		
			#authentication vars
			$this->auth = array('username' => '', 'password' => '');
		}
		
		/***************************************************
		*
		* Func: createEventImage
		* Desc: Create image Asset in the CMS 
		* 
		***************************************************/
		function createEventImage($path, $name, $data)
		{
			$data = base64_decode($data);
			$sysname = $this->cleanAssetName($name);
			
			$asset = array(
				'system-name' => $sysname, 
				'path'  => $path,	
				'metadata-path'   => "/Millersville/Default",  
				'publishable' => true,
				'indexable'   => true,
				'data' => $data, 
				'metadata' => array(
					'displayName' => $name, 
					'title' => $name
				)
			);
			
			if(!$this->createFile($asset))
				return false;
			
			$image = $this->readAsset('', $path.'/'.$sysname, 'file');
			
			# Return the id of the image for future reference.
			return $image->readReturn->asset->file->id;
		}

		/***************************************************
		*
		* Func: resizeImage
		* Desc: Resizes the event image if the width is too large.
		*		Default maximum width is 200px.
		* 
		***************************************************/
		function resizeImage($filepath, $max_w = 200)
		{
			# Gather the original image's dimensions.
			$size = getimagesize($filepath);
			$orig_w = $size[0];
			$orig_h = $size[1];
			
			# If the size is already less the max width, return without manipulation
			if($orig_w < $max_w)
				return true;
			
			# Gather the new dimensions.
			$ratio = $max_w/$orig_w;
			$new_w = $orig_w*$ratio;
			$new_h = $orig_h*$ratio;
			
			# Get the MIME type to determin the type of image to create.
			$img_type = $size['mime'];
			
			# Setup the new image.
			$dest_img = @imagecreatetruecolor($new_w, $new_h);
			
			# Grab the source image to copy from.
			if(preg_match('/jpg|jpeg/i',$img_type))
				$src_img = @imagecreatefromjpeg($filepath);
			else if(preg_match('/gif/i',$img_type))
				$src_img = @imagecreatefromgif($filepath);
			
			# Copy the source image into the destination image with the new dimensions.
			imagecopyresampled($dest_img, $src_img, 0, 0, 0, 0, $new_w, $new_h, $orig_w, $orig_h);
						
			# Overwrite the source image with the resized destination image.
			if(preg_match('/jpg|jpeg/i',$img_type))
				return imagejpeg($dest_img, $filepath);
			else if(preg_match('/gif/i',$img_type))
				return imagegif($dest_img, $filepath);			
		}
	
		/***************************************************
		* Func: createDepartment
		* Desc: Adds a department, and all of it's corresponding assets, to Cascade.
		***************************************************/
		function createCalendarEvent($user_data)
		{		
			# Setup the event's information.
			#	The two descriptions need the be decoded because the Spam Check script encodes the information.
			$this->event = array(
				'sysname' => substr($this->cleanAssetName($user_data['title']), 0, 32),
				'publicPath' => 'millersville/calendar/pending',
				'title' => $user_data['title'],				
				'audienceCal' => $user_data['audienceCal'],
				's_date' => $user_data['s_date'],
				'e_date' => $user_data['e_date'],
				's_time' => $user_data['s_time'],				
				'e_time' => $user_data['e_time'],
				'short_desc' => $this->cleanWordCharacters($user_data['short_desc']),
				'full_desc' => '<p>'.str_replace("\n",'<br />', $this->cleanWordCharacters($user_data['full_desc'])).'</p>',
				'image' => $user_data['image'],
				'contact_name' => $user_data['contact_name'],
				'contact_phone' => $user_data['contact_phone'],
				'contact_email' => $user_data['contact_email'],
				'ticketinfo' => $user_data['ticketinfo'],
				'location' => $user_data['location']
			);
				
			# Does the event exist?
			if($this->isAssetAlreadyCreated($this->event['publicPath'].'/'.$this->event['sysname'], 'page'))
			{
				$this->eventlog[] = 'Event "'.$this->event['title'].'" already exists.';
				return false;
			}
			else
			{
				$this->eventlog[] = 'Event does not exist, continuing...';				
			}
	
			# Get image data, if it exists, create the asset.
			if($this->resizeImage($this->event['image']['tmp_name']))
			{
				if($image = $this->getImageData($this->event['image']['tmp_name']))
					$imageId = (string) $this->createEventImage($this->event['publicPath'].'/img', $this->event['image']['name'], $image);
			}

			# Checkbox arrays have to be formated in a string.
			if(!empty($this->event['audienceCal']))
				$this->event['audienceCal'] = '::CONTENT-XML-CHECKBOX::' . implode('::CONTENT-XML-CHECKBOX::', $this->event['audienceCal']) . '::CONTENT-XML-CHECKBOX::';
			
			
			
			# Setup the page for creation.
			$data = array(
				'name' => $this->event['sysname'], 
				'parentFolderPath'	=>  $this->event['publicPath'],
				'shouldBePublished' => true,
				'shouldBeIndexed' => true,
				'metadata' => array(
					'displayName' => $this->event['title'],
					'title' => $this->event['title'],
					'keywords' => 'millersville university, calendar, event',
					'dynamicFields' => array(
						'dynamicField' => array(
							array('name' => 'start-date', 'fieldValues' => array('fieldValue' => array('value' => $this->event['s_date']))), 
							array('name' => 'end-date', 'fieldValues' => array('fieldValue' => array('value' => $this->event['e_date']))),
							array('name' => 'start-time', 'fieldValues' => array('fieldValue' => array('value' => $this->event['s_time']))), 
							array('name' => 'end-time', 'fieldValues' => array('fieldValue' => array('value' => $this->event['e_time'])))							
						)
					)
					
				),
				'contentTypeId' => '5dfd5aab7f000001000225f0dee67a98',
				'structuredData'  => array(
					'structuredDataNodes' => array(
						'structuredDataNode' => array(
							array(
								'type' => 'group',
								'identifier' => 'audience',
								'structuredDataNodes' => array(
									'structuredDataNode' => array(
										array('type' => 'text', 'identifier' => 'homepage', 'text' => ''),
										array('type' => 'text', 'identifier' => 'applicable-audiences', 'text' => $this->event['audienceCal']),
									)
								)
							), 
							array(
								'type' => 'group',
								'identifier' => 'event-descriptions',
								'structuredDataNodes' => array(
									'structuredDataNode' => array(
										array('type' => 'text', 'identifier' => 'short-description','text' => $this->event['short_desc']),
										array('type' => 'text', 'identifier' => 'full-description', 'text' => html_entity_decode(str_replace("\n",'<br />',$this->event['full_desc']))) 
									)
								)
							),
							array(
								'type' => 'group',
								'identifier' => 'event-image',
								'structuredDataNodes' => array(
									'structuredDataNode' => array(
										array('type' => 'asset', 'identifier' => 'thumb', 'assetType' => 'file', 'fileId' => $imageId),
										array('type' => 'asset', 'identifier' => 'large', 'assetType' => 'file', 'fileId' => $imageId)									
									)
								)
							), 								
	
							array(
								'type' => 'group',
								'identifier' => 'event-contact',
								'structuredDataNodes' => array(
									'structuredDataNode' => array(
										array('type' => 'text', 'identifier' => 'name' , 'text' => $this->event['contact_name']),
										array('type' => 'text', 'identifier' => 'phone', 'text' => $this->event['contact_phone']),
										array('type' => 'text', 'identifier' => 'email', 'text' => $this->event['contact_email'])  
									)
								)
							), 
							
							array('type' => 'text', 'identifier' => 'event-ticketinfo', 'text' => $this->event['ticketinfo']),
							
							array('type' => 'text', 'identifier' => 'event-location', 'text' => $this->event['location'])
						)
					)
				),
				
			);
			
			# Set up the related links array and append them.			
			$relatedLinks = array();
			for($i = 0; $i < count($user_data['links']['labels']); $i++)
			{
				if(!empty($user_data['links']['labels'][$i]) && !empty($user_data['links']['urls'][$i]) && $user_data['links']['urls'][$i] != 'http://')
				{
					$relatedLinks[] = array(
						'type' => 'group',
						'identifier' => 'related',
						'structuredDataNodes' => array(
							'structuredDataNode' => array(
								array('type' => 'text', 'identifier' => 'link-name', 'text' => $user_data['links']['labels'][$i]),
								array('type' => 'text', 'identifier' => 'link-url' , 'text' => $user_data['links']['urls'][$i])  
							)
						)
					);
				}
			}
			
			for($i = 0; $i < count($relatedLinks); $i++)
				$data['structuredData']['structuredDataNodes']['structuredDataNode'][] = $relatedLinks[$i];				
			
			if(!$this->createPage($data))
			{
				$this->eventlog[] = 'Page creation for event, "'.$this->event['title'].'", failed.';
				return false;
			}
							
			unset($data);
			
			return true;
		}
		
	}
?>