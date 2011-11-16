<? 
session_start(); 
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Millersville University - Department Creation Script</title>
	
	<style type="text/css">
		body  { color: #212121; font: normal 12px Verdana, Arial, sans-serif; padding: 15px 40px; }
		
		h3 { color: #5a90cf; }
		p { font-size: 11px; }
		
		form { border: 1px dashed #ddd; padding: 10px 20px; }
		
		label { display: block; font-size: 11px; font-weight: bold; line-height: 20px; position: relative; width: 450px; }
			input[type=text], select { position: absolute; left: 200px; }
			
		.note { color: #777; display: block; margin: -10px 0 0 20px; padding-bottom: 15px; width: 350px; }
		.half { float: left; margin-bottom: 20px; width: 50%; }
		
		#legend { list-style: none; padding: 0; }
		
			#legend li { display: block; clear: both; line-height: 20px; padding: 1px 0; margin: 0 0 0 10px; }
			
			#legend li {
				color: #fff;
				cursor: pointer;
				display: block;
				float: left;
				font-weight: bold;
				line-height: 24px;
				padding: 0 0 0 10px;
				margin: 0 5px 1px 0;
				width: 250px;
			}
			
				#legend li.simple { background-color: #807f7f; }	
				#legend li.president { background-color: #9c600e; }	
				#legend li.academicaffairs { background-color: #41c4d2; }	
				#legend li.educ { background-color: #be9914; }	
				#legend li.hmss { background-color: #2d0e4a; }	
				#legend li.scma { background-color: #973523; }	
				#legend li.nonschool { background-color: #466b83; }	
				#legend li.finadmin { background-color: #4c834c; }	
				#legend li.infotech { background-color: #1c61b2; }	
				#legend li.stuaffairs { background-color: #e06400; }	
				#legend li.advancement { background-color: #bdaa65; }	
				#legend li.other { background-color: #466b83; }	
				
				#legend li.simple:hover { background-color: #000; }	
				#legend li.president:hover { background-color: #6a410a; }	
				#legend li.academicaffairs:hover { background-color: #32828e; }	
				#legend li.educ:hover { background-color: #8a7009; }	
				#legend li.hmss:hover { background-color: #200a34; }	
				#legend li.scma:hover { background-color: #662418; }	
				#legend li.nonschool:hover { background-color: #2a4052; }	
				#legend li.finadmin:hover { background-color: #2d4d2d; }	
				#legend li.infotech:hover { background-color: #113b6c; }	
				#legend li.stuaffairs:hover { background-color: #9d4600; }	
				#legend li.advancement:hover { background-color: #90824d; }	
				#legend li.other:hover { background-color: #2a4052; }

	</style>
</head>
<body>
	<h1>Department Creation Form</h1>

<?	if(isset($_SESSION['errors'])): ?>
		<div style="background-color: #fff6cf; border: 1px solid #ffcfcf; margin: 1em; padding: 1em;";>
			<ul>
		<?
			foreach($_SESSION['errors'] as $error)
				echo '<li>' . $error . '</li>';
		?>
			</ul>
		</div>
	
<?	elseif(isset($_SESSION['success'])): ?>
	
		<div style="background-color: #fff6cf; border: 1px solid #ffcfcf; margin: 1em; padding: 1em;";>
		<?
			echo $_SESSION['success'];
		?>
		</div>
		
<?	endif;	?>

	<form action="process.php" method="post">
		<div class="half">
			<h3>Name &amp; Location</h3>
			<p><label>Department Name: <input type="text" name="name" value="" size="25" /></label><br />
				<span class="note"><strong>Notice:</strong> The department name will also be the name for the group that is created for the department.</span></p>
			<p><label>Public Path: <input type="text" name="publicpath" value="millersville" size="25" /></label><br />
				<span class="note"><strong>Notice:</strong> No trailing forward slash.</span></p>
			<p><label>Config Set Path: <input type="text" name="configsetpath" value="millersville" size="25" /></label><br />
				<span class="note"><strong>Notice:</strong> No trailing forward slash.</span></p>
			<p><label>Content Type Path: <input type="text" name="contenttypepath" value="millersville" size="25" /></label><br />
				<span class="note"><strong>Notice:</strong> No trailing forward slash.</span></p>
			<p><label>Asset Factory Path: <input type="text" name="assetpath" value="" size="25" /></label><br />
				<span class="note"><strong>Notice:</strong> No trailing forward slash.  Leave blank if there are no sub-folders.</span></p>
				
			<h3>Faculty/Staff</h3>
			<p>
				<label><input type="radio" name="facOrStaff" value="faculty" /> Faculty</label><br/>
				<label><input type="radio" name="facOrStaff" value="staff" /> Staff</label>
			</p>

			<h3>Announcements &amp; Events</h3>
			<p>
				<label><input type="checkbox" name="hasNews" value="1" /> Include News</label><br/>
				<label><input type="checkbox" name="hasEvents" value="1" /> Include Events</label>
			</p>
		</div>
		
		<div class="half">
			<h3>Metadata</h3>
			<p><label>Keyword(s): <input type="text" name="keywords" value="millersville university" size="25" /></label><br />
				<span class="note"><strong>Note:</strong> Separate with commas.</span>
			</p>
			
			<h3>Navigation</h3>
			<p>
				<label><input type="checkbox" name="reverseMainNav" value="1" /> Show Reverse Main Navigation</label><br />
				<span class="note"><strong>Notice:</strong> This will display the 'blacked out' audience navigation.</span>
			</p>
			<p><label>Side Navigation Color: 
						<select name="type">
							<option value="simple">Simple</option>
							<option value="president">Office of the President</option>
							<option value="academicaffairs">Academic Affairs</option>
							<option value="educ">School of Education</option>
							<option value="hmss">School of Humanities &amp; Social Sciences</option>
							<option value="scma">School of Science and Mathematics</option>
							<option value="nonschool">Non-School Departments</option>
							<option value="finadmin">Finance &amp; Administration</option>
							<option value="infotech">Information Technology</option>	
							<option value="stuaffairs">Student Affairs</option>
							<option value="advancement">University Advancement</option>
							<option value="other">Other Organizations</option>
						</select>
					</label><br />
				<span class="note"><strong>Color Legend w/ hovers:</strong>
					<ul id="legend">
						<li class="simple">Simple</li>
						<li class="president">Office of the President</li>
						<li class="academicaffairs">Academic Affairs</li>
						<li class="educ">School of Education</li>
						<li class="hmss">School of Humanities &amp; Social Sciences</li>
						<li class="scma">School of Science and Mathematics</li>
						<li class="nonschool">Non-School Departments</li>
						<li class="finadmin">Finance &amp; Administration</li>
						<li class="infotech">Information Technology</li>	
						<li class="stuaffairs">Student Affairs</li>
						<li class="advancement">University Advancement</li>
						<li class="other">Other Organizations</li>	
					</ul>
				</span>
			</p>
		</div>
		
		
		<p style="clear: both;"><input type="submit" value="Submit Department" /></p>
	</form>

</body>
</html>

<? session_destroy(); ?>