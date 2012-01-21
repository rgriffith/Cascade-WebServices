#INCLUDED CLASS/SCRIPT FILES:

## CascadeWebService.php
Abstract class that creates the SOAP request to Cascade's Web Services and provides wrapper functions to create, read, edit	assets within Cascade.

## ContentAudit.php
Sub-class that extends the abstract class above and performs an audit on selected asset types within specified directory and date range (default 1 year). Within this sub-class is the user authentication, ideally this should be a site manager or admin to avoid complications within nested assets , or, an user that has AT LEAST read access to the specified directory.
	
## content-audit.php
Front-end page that initialized the ContentAudir sub-class and performs an audit on a directory (hard coded at the moment, but could easily be changed to a web form) and date range (default 1 year). Once the audit finishes, the results are listed in tabular format.

#Sample Usage of other Web Event Functions

## Read a Page by ID
`$assetArgs = array('type' => 'page', 'id' => 'b65ddc567f000001000225f076446430');
if ($response = $audit->readAsset($assetArgs)) {
	echo '<pre>';
	print_r($response);
	echo '</pre>';
} else {
	echo '<pre>';
	print_r($audit->getEventLog());
	echo '</pre>';
}
die();`

## Edit a Page (assuming it has a Default Data Definition with a single text field)
`$pageData = array(
	'id' => 'b65ddc567f000001000225f076446430',
	'name' => 'test-webservice-edit',
	'shouldBePublished' => false,
	'shouldBeIndexed' => false,				
	'metadata' => array(
		'displayName' => 'Display Name - Testing Web Service Edit',
		'title' => 'Title - Testing Web Service Edit',
		'keywords' => 'test, testedit, web, webservices'
	),
	'structuredData' => array(
		'structuredDataNodes' => array(
			'structuredDataNode' => array(
				array(
					'type' => 'group',
					'identifier' => 'left_col',
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

if ($response = $audit->editAsset($pageData, 'page')) {
	echo '<pre>';
	print_r($response);
	echo '</pre>';
} else {
	echo '<pre>';
	print_r($audit->getEventLog());
	echo '</pre>';
}
die();`