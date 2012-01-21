<?php	
	ini_set('memory_limit', '64M');
	
	require_once('ContentAudit.php');
	
	$auditArgs = array(
		'rootPath' => 'millersville/about',
		'ageTrigger' => 31556926
	);
	
	$audit = new ContentAudit($auditArgs);
	
	$audit->performContentAudit();
	
	/* Remove comment tags below for debug.
	echo '<pre>';
	print_r($audit->flaggedAssets);
	echo '</pre>';
	die();
	*/	
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"/>
	<title>Cascade Web Services - Content Audit</title>
	
	<style>
		#hor-zebra
		{
			font-family: "Lucida Sans Unicode", "Lucida Grande", Sans-Serif;
			font-size: 12px;
			margin: 45px;
			text-align: left;
			border-collapse: collapse;
		}
		#hor-zebra th
		{
			font-size: 14px;
			font-weight: normal;
			padding: 10px 8px;
			color: #039;
		}
		#hor-zebra td
		{
			padding: 8px;
			color: #669;
		}
		#hor-zebra .odd
		{
			background: #e8edff; 
		}
	</style>
</head>

<body>
	<p>The following is a listing of assets <strong>over 1 year old</strong> within the <strong><?=$auditArgs['rootPath'];?></strong> folder.</p>
	<table id="hor-zebra" summary="Assets over 1 year old">
	    <thead>
	    	<tr>
	    		<th scope="col">Asset Type</th>
	        	<th scope="col">Name</th>
	            <th scope="col">Path</th>
	            <th scope="col">Last Modified</th>
	            <th scope="col">Last Modified By</th>
	        </tr>
	    </thead>
	    <tbody>
	    <? $i = 0; ?>
	    <? foreach ($audit->flaggedAssets as $asset) { ?>
	    	<tr<?=$i%2==0?' class="odd"':'';?>>
		    	<td><?=$asset->entityType->name;?></td>
	    		<td><?=$asset->name;?></td>
	    	    <td><?=$asset->path;?></td>
	    	    <td><?=$asset->lastModifiedDate;?></td>
	    	    <td><?=$asset->lastModifiedBy;?></td>
	    	</tr>
	    	
	    	<? $i++; ?>	    	
	    <? } ?>
	    </tbody>
	</table>



</body>

</html>