<?php 

include_once('classes/contentclass.php');

$class = new contentClass;

// handle file import
$class->handleImport();

// if data has been posted
if(isset($_POST['data']))
{
	// if the user wants to clear all data
	if(isset($_POST['Clear']))
	{
		$data = array();
		$class->storeData($data);
	}
	else
	{
		$data = $_POST['data'];		
	}
}

// if no data has been posted
else
{
	// retrieve data
	$data = $class->retrieveData();
}

// handle new data
$data = $class->handleNewData($data);

// handle remove
$data = $class->handleRemove($_POST, $data);

// if we want identifiers created
if(isset($_POST['CreateIdentifiers']))
{
	// create identifiers
	$data = $class->createIdentifiers($data);
}

// update attribute order list
$data = $class->updateAttributeOrder($data);

// store data
$class->storeData($data);

// if the data should be exported
if(isset($_POST['Export']))
{
	$filename = date("YmdHi") . ' ' . $data['site'] . '.ezclasses';
	
	header('Content-type: text/html');
	header("Content-Disposition: attachment; filename=\"$filename\"");
	echo json_encode($data);
	exit();
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>eZ Publish Content Class spec</title>
	<link type="text/css" href="css/main.css" rel="stylesheet" media="all"/>
	<link type="text/css" href="css/nyroModal.css" rel="stylesheet" media="all"/>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>

<div id="toc">
<?php 

echo $class->outputTOC($data);

?>
</div>
<div id="topnav">
<form action="" method="post">

<div id="buttons">
	<input type="submit" name="Save" value="Save" />
	<input type="submit" name="CreateIdentifiers" value="Create identifiers" />
	<input type="submit" name="RemovedSelected" value="Remove selected" />
	<input type="submit" name="Clear" value="Clear all" />
	<input type="submit" name="Export" value="Export" />
	<a href="parts/import.php" class="nyroModal">Import</a>
</div>

<label><strong>Site name</strong></label>
<input type="text" name="data[site]" tabindex="<?php echo $class->getTabIndex(); ?>" value="<?php if(isset($data['site'])) {echo $data['site'];} ?>" /></div>

<div id="content">

<?php 

// for each existing class
if(isset($data['class_list']) and count($data['class_list']) > 0)
{
	foreach($data['class_list'] as $key => $data)
	{
		echo $class->formOutputClass($key, $data);
	}
}

// output a blank line to add new classes
echo $class->formOutputClass();

?>

</form>

<script type="text/javascript" src="js/jquery-1.4.3.min.js"></script>
<script type="text/javascript" src="js/jquery.tablednd_0_5.js"></script>
<script type="text/javascript" src="js/jquery.nyroModal-1.6.2.min.js"></script>
<script type="text/javascript">
  $(document).ready(function() {

	// set focus on the new attribute of the class for which we most recenly added an attribute
	$('#attribute-<?php echo $class->getFocusClass(); ?>-new .attribute_name').focus();

	// initialise drag-and-drop functionality for the table
	$('table').tableDnD({
        onDrop: function(table, row) {
            // alert($.tableDnD.serialize());
            $('form').submit();
        },
        dragHandle: "dragHandle"
    });

	
  });
</script>
</div>