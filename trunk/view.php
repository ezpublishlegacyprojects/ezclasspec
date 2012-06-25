<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>eZ Publish Content Class spec</title>
	<link type="text/css" href="lib/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="all"/>
	<link type="text/css" href="css/main.css" rel="stylesheet" media="all"/>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
<?php

include_once('classes/contentclass.php');

$classObject = new contentClass;

// retrieve data
$data = $classObject->retrieveData();

echo '<h1>' . $data['site'] . '</h1>';

echo "<p><em>" . date("d.m.Y H:i") . "</em></p>";

// sort classes alphabetically
asort($data['class_list']);

foreach($data['class_list'] as $class)
{
	echo "<h2>$class[class_name]"; 

	if($class['class_identifier'] != '')
	{
		echo " ($class[class_identifier])";
	}

	echo " [$class[class_group]] </h2>";
	
	if(count($class['attribute_list']) > 0)
	{
		echo "<table border='1' cellspacing='0' class='attribute-list table' width='100%' bordercolor='black'><tr bgcolor=#000>
		<th class='attribute' width='45%'>
			<font color='white'><strong>Attribute</strong></font>
		</th>
		<th class='identifier' width='20%'>
			<font color='white'><strong>Identifier</strong></font>
		</th>
		<th class='datatype' width='20%'>
			<font color='white'><strong>Datatype</strong></font>
		</th>
		<th class='required' width='15%'>
			<font color='white'><strong>Required</strong></font>
		</th>
		</tr>";
	}
	
	foreach($class['attribute_list'] as $attribute)
	{
		echo "<tr><td class='name'>$attribute[name]";

		if($attribute['desc'] != '')
		{
			echo "<br /><small>$attribute[desc]</small>";
		}

		echo "</td><td>$attribute[identifier]</td><td>";
		
		echo $classObject->dataType($attribute['datatype']);
		
		echo "</td><td>";
		
		if(isset($attribute['required']))
		{
			echo "Yes";
		}
		else
		{
			echo "No";
		}
		
		echo "</td></tr>";
	}
	
	if(count($class['attribute_list']) > 0)
	{
		echo "</table><br />";
	}
}

?>
</body>
</html>