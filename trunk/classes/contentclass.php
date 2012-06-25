<?php 

class contentClass
{
	var $tabIndex;
	var $focusClass;
	var $cookieName;
	var $classLanguage;
	var $translateURL;
	var $storageFile;
	
	function __construct()
	{
		$this->tabIndex = 0;
		$this->focusClass = false;
		$this->cookieName = 'ezp-class-data';
		$this->classLanguage = 'norwegian';
		$this->translateGoogleURL = 'https://www.googleapis.com/language/translate/v2?key=AIzaSyD_M-6sDcNu3ZPhzToY_W_smtaG2SIY_Vs';
		$this->translateBingURL = 'http://api.microsofttranslator.com/V2/Ajax.svc/Translate?appId=44A936365D6ACB29F7B0B3AFC53642FB59CCE539';
		$this->storageFile	= 'var/storage.db';
	}
	
	function getClassLanguage()
	{
		return $this->classLanguage;
	}
	
	function handleImport()
	{
		if(isset($_FILES['uploadedfile']))
		{
			$content = file_get_contents($_FILES['uploadedfile']['tmp_name']);
			$this->storeData(json_decode($content, true));
		}				
	}
	
	function updateAttributeOrder($data)
	{
		// for each class
		if(isset($data['class_list']) and count($data['class_list']) > 0)
		{
			foreach($data['class_list'] as $key => $class)
			{
				// update attribute list
				$data['class_list'][$key]['attribute_list'] = array_values($data['class_list'][$key]['attribute_list']);
			}
		}
		
		return $data;
	}
	
	function createIdentifiers($data)
	{
		$translateStrings = array();
		
		// build the list of strings which should be translated
		foreach($data['class_list'] as $classKey => $class)
		{
			// if the class identifier is empty
			if($class['class_identifier'] == '' and $class['class_name'] != '')
			{
				// add to translation list
				$className 						= $class['class_name'];
				$translateStrings[$classKey] 	= $className;
			}
			
			// for each attribute
			foreach($class['attribute_list'] as $attrKey => $attr)
			{
				// if the attribute identifier is empty
				if($attr['identifier'] == '' and $attr['name'] != '')
				{
					// add to translation list
					$attrName 	= $attr['name'];
					$key 		= $classKey . '|' . $attrKey;
					$translateStrings[$key] = $attrName;
				}
			}
		}
		
		// translate strings
		$translationList = $this->translate($translateStrings);
		
		// apply identifiers
		$data = $this->applyIdentifiers($data, $translationList);
		
		return $data;
	}
	
	function applyIdentifiers($data, $identifierList)
	{
		foreach($identifierList as $placement => $identifier)
		{
			$parts = explode('|', $placement);
			
			// if it's an attribute
			if(count($parts) > 1)
			{
				$classKey = $parts[0];
				$attrKey = $parts[1];
				$data['class_list'][$classKey]['attribute_list'][$attrKey]['identifier'] = $this->convertToIdentifier($identifier);
			}
			else
			{
				$classKey = $parts[0];
				$data['class_list'][$classKey]['class_identifier'] = $this->convertToIdentifier($identifier);
			}
		}
		
		return $data;
	}

	function translate($stringList)
	{
		$url = $this->translateBingURL . '&from=no&to=en&text=';		
		
		$result = array();
		foreach ($stringList as $placement => $string)
		{
			$stringURL = $url.urlencode($string);
			$get = file_get_contents($stringURL);
			// remove non-ascii characters
			$clean = preg_replace('/[^(\x20-\x7F)]*/','', $get);
			$result[$placement] = $clean;
			unset($stringURL);
		}
		return $result;
	}

	function translateOld($stringList)
	{

		$url = $this->translateGoogleURL . '&source=no&target=en';
		
		$placementsForString = array();
		foreach($stringList as $placement => $string)
		{
			if(!isset($placementsForString[$string]))
			{
				$url .= '&q=' . urlencode($string);	
			}
			
			$placementsForString[$string][] = $placement;
		}
		
		$urlResult = json_decode(file_get_contents($url), true);
		
		$result = array();
		
		foreach($urlResult['data']['translations'] as $k => $translation)
		{
			$part = array_slice($placementsForString, $k, 1);
			
			$placements = array_pop($part);
			
			foreach($placements as $placement)
			{
				$result[$placement] = $translation['translatedText'];
			}
		}
		
		return $result;
	}
	
	function convertToIdentifier($string)
	{
		$string = preg_replace('/[^a-zA-Z\s]/i', '', $string);
		return preg_replace('/\s/i', '_', strtolower($string));
	}
	
	function storeData($data)
	{
		$encodedData = json_encode($data);
		
		file_put_contents($this->storageFile, $encodedData);
	}
	
	function retrieveData()
	{
		if(file_exists($this->storageFile))
		{
			return json_decode(file_get_contents($this->storageFile), true);
		}
		else
		{
			return array();
		}		
	}
	
	function setFocusClass($class)
	{
		$this->focusClass = $class;
	}
	
	function getFocusClass()
	{
		return $this->focusClass;
	}
	
	function getTabIndex()
	{
		$this->tabIndex++;
		return $this->tabIndex;
	}
	
	function handleNewData($data)
	{
		// if a new class has been added
		if(isset($data['class_list']['new']['class_name']) and $data['class_list']['new']['class_name'] != '')
		{
			// add it to the end of the data list
			$newClass = $data['class_list']['new'];
			array_push($data['class_list'], $newClass);
		}
		
		// unset the old-new class
		unset($data['class_list']['new']);
		
		// for each class
		$classList = array();
		

		// if a class list exists
		if(isset($data['class_list']))
		{
			// for each class
			foreach($data['class_list'] as $key => $class)
			{
				if(!isset($class['class_group']))
				{
					$class['class_group'] = 'Content';
				}

				// if a new attribute has been added
				if(isset($class['attribute_list']['new']['name']) and $class['attribute_list']['new']['name'] != '')
				{
					// add it to the end of the attribute list
					$newAttribute = $class['attribute_list']['new'];
					array_push($class['attribute_list'], $newAttribute);
					
					// set the current class as the focus class
					$this->setFocusClass($key);
				}
				
				// unset the old-new attribute
				unset($class['attribute_list']['new']);
	
				$classList[] = $class;
			}
			
			$data['class_list'] = $classList;

		}


		return $data;
	}
	
	function outputTOC($data)
	{
		$output = '';
		
		if(isset($data['site']))
		{
			$output = '<ul class="nav nav-list"><li class="nav-header">Class list</li>';
		}
		
		if(isset($data['class_list']))
		{
			// sort classes alphabetically
			asort($data['class_list']);
			
			foreach($data['class_list'] as $key => $class)
			{
				$output .= '<li><a href="#class-' . $key . '">' . $class['class_name'] . '</a></li>';
			}
			
			$output .= '</ul>';
		}

		return $output;
	}
	
	function formOutputClass($key = 'new', $data = false)
	{
		$tabIndex = $this->getTabIndex();
		
		$output =<<<EOF
<a name="class-$key"></a>
<div class="well" id="class-$key">
<fieldset>
	<legend>Class</legend>
EOF;

		if($key !== 'new')
		{
			$output .= '<input type="checkbox" name="class_delete[' . $key . ']" value="1" />';
		}

		$groupList = $this->classGroupList();
		
		$output .=<<<EOF
	<input type="text" tabindex="$tabIndex" placeholder="Name" name="data[class_list][$key][class_name]" class="class_name" value="$data[class_name]" />
	<input type="text" placeholder="Identifier" name="data[class_list][$key][class_identifier]" class="class_identifier input-small" value="$data[class_identifier]" />
	<select name="data[class_list][$key][class_group]">
EOF;
	foreach($this->classGroupList() as $index => $value)
	{
		$selected = (isset($data['class_group']) && $value == $data['class_group']) ? ' selected' : '';
		$output .= '<option value="'.$value.'"'.$selected.'>'.$value.'</option>';
	}
	$output .=<<<EOF
</select>
</fieldset>
<fieldset>
	<legend>Attributes</legend>
	
	<table class="table-condensed">
		<tr class="nodrag nodrop">
			<th>
			&nbsp;
			</th>
			<th>
			Name
			</th>
			<th>
			Identifier
			</th>
			<th>
			Datatype
			</th>
			<th>
			Description
			</th>
			<th>
			Req.
			</th>
			<th>
			&nbsp;
			</th>
		</tr>
EOF;

		// if attributes are set
		if(isset($data['attribute_list']) and count($data['attribute_list']) > 0)
		{
			// for each attribute
			foreach($data['attribute_list'] as $attrKey => $attrData)
			{
				// output attribute
				$output .= $this->formOutputAttribute($key, $attrKey, $attrData);
			}	
		}

		// output a new line for inserting new attributes
		$output .= $this->formOutputAttribute($key);
		
		$output .= '</table></fieldset></div>';

		return $output;
	}
	
	function handleRemove($all, $data)
	{
		// if we are to delete on or more attributes
		if(isset($all['RemovedSelected']))
		{
			// for each attribute
			if(isset($all['attribute_delete']))
			{
				foreach($all['attribute_delete'] as $classKey => $class)
				{
					foreach($class as $attrKey => $ignore)
					{
						unset($data['class_list'][$classKey]['attribute_list'][$attrKey]);
					}
					
					// reset array to keep from having gaps in the array indexes
					$data['class_list'][$classKey]['attribute_list'] = array_values($data['class_list'][$classKey]['attribute_list']);
				}
			}
			
			if(isset($all['class_delete']))
			{
				// for each class
				foreach($all['class_delete'] as $classKey => $class)
				{
					unset($data['class_list'][$classKey]);
				}
				
				// reset array to keep from having gaps in the array indexes
				$data['class_list'] = array_values($data['class_list']);	
			}
		}
		
		return $data;
	}
	
	function dataType($identifier)
	{
		$list = $this->dataTypeList();
		return $list[$identifier];
	}
	
	function dataTypeList()
	{
		$datatypes = array(	'ezboolean' 	=> 'Check box', 
							'ezimage'		=> 'Image', 
							'ezuser'		=> 'User account', 
							'ezdate'		=> 'Date', 
							'ezdatetime' 	=> 'Date and time', 
							'ezemail'		=> 'E-mail', 
							'ezenum'		=> 'Enum', 
							'ezbinaryfile'	=> 'File', 
							'ezmultioption'	=> 'Multi option', 
							'ezmultioption2' => 'Multi option 2', 
							'ezfloat'		=> 'Float number', 
							'ezauthor'		=> 'Authors', 
							'ezinteger'		=> 'Integer', 
							'ezinisetting' 	=> 'INI setting', 
							'ezisbn'		=> 'ISBN', 
							'ezidentifier'	=> 'Identifier', 
							'ezcountry'		=> 'Country', 
							'ezmatrix'		=> 'Matrix', 
							'ezmedia'		=> 'Media', 
							'ezmultiprice'	=> 'Multi price', 
							'ezkeyword'		=> 'Keyword', 
							'ezobjectrelation' => 'Object relation', 
							'ezobjectrelationlist' => 'Object relation list', 
							'ezpackage'		=> 'Package', 
							'ezprice'		=> 'Price', 
							'ezproductcategory'	=> 'Product category', 
							'ezsubtreesubscription' => 'Subtree subscription', 
							'eztext'		=> 'Text block', 
							'ezstring'		=> 'Text line', 
							'eztime'		=> 'Tid', 
							'ezurl'			=> 'URL', 
							'ezselection'	=> 'Selection', 
							'ezoption'		=> 'Option', 
							'ezrangeoption'	=> 'Range option', 
							'ezxmltext'		=> 'XML block');
		
		include('settings/datatypes.php');
		
		// add user specified datatypes
		$datatypes = array_merge($datatypes, $additionalDatatypes);
		
		asort($datatypes);
		
		return $datatypes;
	}

	function classGroupList()
	{
		$groups = array('1' => 'Content',
						'2' => 'Users',
						'3' => 'Media',
						'4' => 'Setup');

		include('settings/datatypes.php');

		$datatypes = array_merge($groups, $additionalClassGroups);

		return $datatypes;
	}
	
	function formOutputAttribute($classKey = 'new', $attrKey = 'new', $data = false)
	{
		$output =<<<EOF
		<tr id="attribute-$classKey-$attrKey"
EOF;

		if($attrKey === 'new')
		{
			$output .= ' class="nodrag nodrop" ';
		}
		
		$output .= '><td>';


		if($attrKey !== 'new')
		{
			$output .= '<input type="checkbox" name="attribute_delete[' . $classKey . '][' . $attrKey . ']" value="1" />';
		}
		
		$tabIndex = $this->getTabIndex();
		
		$output .=<<<EOF
			</td>
			<td>
			<input type="text" tabindex="$tabIndex" name="data[class_list][$classKey][attribute_list][$attrKey][name]" placeholder="Name" class="attribute_name" value="$data[name]" />
			</td>
			<td>
			<input type="text" name="data[class_list][$classKey][attribute_list][$attrKey][identifier]" placeholder="Identifier" value="$data[identifier]" />
			</td>
			<td>
			<select name="data[class_list][$classKey][attribute_list][$attrKey][datatype]" class="input-small">
EOF;

			foreach($this->dataTypeList() as $key => $value)
			{
				$output .= '<option value="' .  $key . '"'; 
				
				if(isset($data['datatype']) and $data['datatype'] != '')
				{
					if($key == $data['datatype'])
					{
						$output .= ' selected ';
					}	
				}
				else
				{
					if($key == 'ezstring')
					{
						$output .= ' selected ';
					}
				}
				
				$output .= '>' . $value . '</option>';
			}
		
			$output .= '</select></td>';
			
			$output .=<<<EOF
			<td><input type="text" name="data[class_list][$classKey][attribute_list][$attrKey][desc]" value="$data[desc]" placeholder="Description" /></td>	
			<td><input type="checkbox" name="data[class_list][$classKey][attribute_list][$attrKey][required]" value="1"	
EOF;

			if(isset($data['required']) and $data['required'] == 1)
			{
				$output .= ' checked="checked" ';
			}
			
			$output .= "/></td>";

			// if the attribute is new
			if($attrKey === 'new')
			{
				$output .= '<td><input type="submit" class="btn" name="AddAttribute" value="Add" />';
			}
			else
			{
				$output .= '<td class="drag-icon dragHandle">&nbsp;';
			}
			
		$output .= '</td></tr>';

		return $output;
	}
}

?>