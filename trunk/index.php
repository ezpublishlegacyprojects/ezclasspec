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
	<link type="text/css" href="lib/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="all"/>
	<link type="text/css" href="css/main.css" rel="stylesheet" media="all"/>
	<link type="text/css" href="css/nyroModal.css" rel="stylesheet" media="all"/>
	<script type="text/javascript" src="lib/bootstrap/js/bootstrap.min.js"></script>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
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
</head>
<body>

<form action="" method="post" class="form-inline">

<div class="container-fluid">
  <div class="row-fluid">
	<div class="span9">
		<div class="well">
			<input type="submit" class="btn btn-primary" name="Save" value="Save" />
			<input type="submit" class="btn" name="CreateIdentifiers" value="Create identifiers" />
			<input type="submit" class="btn" name="RemovedSelected" value="Remove selected" />
			<input type="submit" class="btn" name="Export" value="Export" />
			<a href="parts/import.php" class="nyroModal btn">Import</a>
			<a href="view.php" class="btn">Print</a>
			<input type="submit" class="btn btn-danger" name="Clear" value="Clear all" />
		</div>
	</div>
	<div class="span3">
		<div class="well">
			<fieldset>
				<input type="text" name="data[site]" placeholder="Site name" tabindex="<?php echo $class->getTabIndex(); ?>" value="<?php if(isset($data['site'])) {echo $data['site'];} ?>" />
			</fieldset>
		</div>
	</div>
  </div>
  <div class="row-fluid">
    <div class="span9">
    	
	<?php 

	// for each existing class
	if(isset($data['class_list']) and count($data['class_list']) > 0)
	{
		foreach($data['class_list'] as $key => $dataSingle)
		{
			echo $class->formOutputClass($key, $dataSingle);
		}
	}

	// output a blank line to add new classes
	echo $class->formOutputClass();

	?>
 	</div>
 	<div class="span3">
      <div class="well sidebar-nav">
      	<?php
		echo $class->outputTOC($data);
		?>
      </div><!--/.well -->
    </div><!--/span-->
 </div>
</div>

</form>
</body>
</html>