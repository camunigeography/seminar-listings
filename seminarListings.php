<?php

# Class to create a seminar listings system, integrating talks.cam
class seminarListings extends frontControllerApplication
{
	# Function to assign defaults additional to the general application defaults
	public function defaults ()
	{
		# Specify available arguments as defaults or as NULL (to represent a required argument)
		$defaults = array (
			'applicationName'		=> 'Seminars',
			'div'					=> strtolower (__CLASS__),
			'databaseStrictWhere'	=> true,
			'administrators'		=> 'administrators',
			'tabUlClass'			=> 'tabsflat',
			'database'				=> 'seminarlistings',
			'table'					=> 'lists',
			'disableTabs'			=> true,
			'useTemplating'			=> true,
			'useEditing'			=> true,
		);
		
		# Return the defaults
		return $defaults;
	}
	
	
	# Function to assign supported actions
	public function actions ()
	{
		# Define available tasks
		$actions = array (
			'showlist' => array (
				'description' => false,
				'url' => '',
				'tab' => 'Show list',
				'icon' => 'application_view_list',
			),
			'editing' => array (
				'description' => false,
				'url' => 'data/',
				'tab' => 'Edit lists',
				'icon' => 'pencil',
				'administrator' => true,
			),
		);
		
		# Return the actions
		return $actions;
	}
	
	
	# Database structure definition
	public function databaseStructure ()
	{
		return "
			
			-- Administrators
			CREATE TABLE IF NOT EXISTS `administrators` (
			  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Username',
			  `active` enum('','Yes','No') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Yes' COMMENT 'Currently active?',
			  `privilege` enum('Administrator','Restricted administrator') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Administrator' COMMENT 'Administrator level',
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `moniker` (`moniker`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='System administrators';
			
			-- Settings
			CREATE TABLE IF NOT EXISTS `settings` (
			  `id` int NOT NULL AUTO_INCREMENT COMMENT 'Automatic key (ignored)',
			  `masterList` VARCHAR(50) NOT NULL DEFAULT 'master' COMMENT 'Master list moniker',
			  `usersAutocomplete` VARCHAR(255) NULL COMMENT 'Users autocomplete URL',
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Settings';
			INSERT INTO settings (id) VALUES (1);
			
			-- Lists
			CREATE TABLE IF NOT EXISTS `lists` (
			  `id` int NOT NULL AUTO_INCREMENT COMMENT 'Automatic key',
			  `name` varchar(255) NOT NULL COMMENT 'List name',
			  `talksdotcamListNumber` int NOT NULL COMMENT 'Talks.cam list number (see end of URL)',
			  `moniker` varchar(50) NOT NULL COMMENT 'URL moniker',
			  `categoryId` VARCHAR(255) NULL COMMENT 'Category',
			  `editors` VARCHAR(255) NULL COMMENT 'Editors (local)',
			  `archived` tinyint DEFAULT NULL COMMENT 'Archived?',
			  `ordering` INT NOT NULL DEFAULT '5' COMMENT 'Ordering (1=first, 9=last)',
			  `talksdotcamName` VARCHAR(255) NULL COMMENT 'Name of list in talks.cam (populated automatically)',
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Lists';
			
			-- Categories
			CREATE TABLE IF NOT EXISTS `categories` (
			  `id` varchar(255) NOT NULL COMMENT 'Moniker',
			  `title` varchar(255) NOT NULL COMMENT 'Title',
			  `ordering` int NOT NULL DEFAULT '5' COMMENT 'Ordering (1=first, 9=last)',
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Categories';
			INSERT INTO `categories` VALUES ('main', 'Main seminars', NULL, 5);
		";
	}
	
	
	
	# Additional standard processing (pre-actions)
	public function mainPreActions ()
	{
		# Enable tabbing for admins
		if ($this->userIsAdministrator) {
			$this->settings['disableTabs'] = false;
		}
		
	}
	
	
	# Additional processing
	public function main ()
	{
		# Get the lists
		$this->lists = $this->getLists ();
		
		# Send key properties to the template
		$this->template['baseUrl'] = $this->baseUrl;
		$this->template['administrator'] = $this->userIsAdministrator;
		
	}
	
	
	
	# Home page
	public function home ()
	{
		# Start the HTML
		$html = '';
		
		# Remove the master list from the main listing
		$lists = $this->lists;
		if (isSet ($lists[$this->settings['masterList']])) {
			unset ($lists[$this->settings['masterList']]);
		}
		
		# Split by archive status
		$listsByGroup = application::regroup ($lists, 'archived');
		
		# Split non-archived lists by category title
		$listsByCategory = application::regroup ($listsByGroup[''], 'categoryTitle', $removeGroupField = false);
		
		# Send to the template
		$this->template['listsByCategory'] = $listsByCategory;
		$this->template['archivedLists'] = $listsByGroup[1];
		
		# Get the seminars
		$this->template['seminars'] = $this->getSeminars ($this->settings['masterList'], false, 10);
		
		# Process the template
		$html = $this->templatise ();
		
		# Show the HTML
		echo $html;
	}
	
	
	# Function to get lists
	private function getLists ()
	{
		# Get the current lists
		$query = "SELECT
			lists.*,
			categories.title AS categoryTitle
		FROM lists
		LEFT JOIN categories ON lists.categoryId = categories.id
		ORDER BY archived, categories.ordering, lists.ordering, name
		;";
		$listsById = $this->databaseConnection->getData ($query);
		
		# Reorganise by moniker
		$lists = application::reindex ($listsById, 'moniker', false);
		
		# Decorate the lists
		foreach ($lists as $moniker => $list) {
			$lists[$moniker]['link'] = $this->baseUrl . "/{$moniker}/";
			$lists[$moniker]['talksdotcamUrl'] = 'https://talks.cam.ac.uk/show/index/' . $list['talksdotcamListNumber'];
			$lists[$moniker]['talksdotcamIcal'] = 'https://talks.cam.ac.uk/show/ics/' . $list['talksdotcamListNumber'];
			$lists[$moniker]['thumbnail'] = $this->getThumbnail ($moniker);
		}
		
		# Determine if the user is an editor, for each list
		foreach ($lists as $moniker => $list) {
			$editors = (strlen ($list['editors']) ? explode (',', $list['editors']) : array ());
			$isEditor = ($this->userIsAdministrator || in_array ($this->user, $editors));
			$lists[$moniker]['isEditor'] = $isEditor;
		}
		
		# Return the list of lists
		return $lists;
	}
	
	
	# Function to get seminars in a list
	private function getSeminars ($moniker, $archived = false, $limit = false)
	{
		# Ensure the list ID exists
		if (!isSet ($this->lists[$moniker])) {return array ();}
		
		# Get the feed
		$listId = $this->lists[$moniker]['talksdotcamListNumber'];
		$list = $this->getFeed ($listId, $moniker, $archived, $limit);
		
		# Add the metadata from the upstream feed to the list metadata
		$this->lists[$moniker]['details'] = $list['details'];
		
		# Add HTML version of the details
		$this->lists[$moniker]['detailsHtml'] = ($list['details'] ? application::makeClickableLinks (application::formatTextBlock (str_replace ('@', '<span>&#64;</span>', $list['details']))) : '<p><em>(No list description yet.)</em></p>');
		
		# Convert talks to simplified structure
		$seminars = array ();
		foreach ($list['talk'] as $talk) {
			$talksdotcamUrl = 'https://www.talks.cam.ac.uk/talk/index/' . $talk['id'];
			$seminars[] = array (
				'id' => $talk['id'],
				'title' => $talk['title'],
				'speaker' => $talk['speaker'],
				'abstract' => $talk['abstract'],
				'abstractHtml' => application::makeClickableLinks (application::formatTextBlock (str_replace ('@', '<span>&#64;</span>', $talk['abstract']), 'smaller')),
				'venue' => $talk['venue'],
				'special_message' => $talk['special_message'],
				'time' => date ('g.ia, l jS F Y', strtotime (preg_replace ('/ \+([0-9]{4})$/', '', $talk['start_time']))),		// Strip trailing timezone like " +0000" to prevent the wrong time being determined
				'date' => date ('jS F Y', strtotime ($talk['start_time'])),
				'day' => date ('d', strtotime ($talk['start_time'])),
				'month' => date ('M', strtotime ($talk['start_time'])),
				'url' => $talksdotcamUrl,
				'link' => ($talk['seriesLink'] ? $talk['seriesLink'] . '#id' . $talk['id'] : $talksdotcamUrl),
			);
		}
		
		# Return the list of seminars
		return $seminars;
	}
	
	
	# Function to get a feed for a list
	private function getFeed ($listId, $moniker, $archived = false, $limit = false)
	{
		# Construct the URL
		$url = "https://talks.cam.ac.uk/show/xml/{$listId}?layout=empty";
		
		# For archived mode, add additional parameters
		if ($archived) {
			$url .= '&seconds_after_today=0&reverse_order=true';
		}
		
		# Add limit if required
		if ($limit) {
			$url .= '&limit=' . $limit;
		}
		
		# Get the data
		ini_set ('default_socket_timeout', 4);
		$xmlString = file_get_contents ($url);
		
		# Convert to XML; note that empty tags like <something></something> will become an empty array, which is fixed later below
		$xml = simplexml_load_string ($xmlString);
		$json = json_encode ($xml);
		$list = json_decode ($json, true);
		
		# If no talks, create empty list
		if (!$list || !isSet ($list['talk'])) {
			$list['talk'] = array ();
		}
		
		# If only one talk, wrap as list
		if ($list && isSet ($list['talk']) && isSet ($list['talk']['id'])) {
			$list['talk'] = array ($list['talk']);
		}
		
		# For details, if no description, convert empty array (which SimpleXML returns when <details></details>) to string
		if (is_array ($list['details']) && !empty ($list['details'])) {
			$list['details'] = '';
		}
		
		# For each talk, convert empty array (from SimpleXML treatment of <something></something>) to string
		foreach ($list['talk'] as $index => $talk) {
			foreach ($talk as $key => $value) {
				if (is_array ($value) && empty ($value)) {
					$list['talk'][$index][$key] = '';
				}
			}
		}
		
		# Decode entities arising from the original XML parser stage
		$list = application::array_html_entity_decode ($list);
		
		# Update the talks.cam name in the lists database, which is used below to emulate list IDs for talks within combined lists
		#!# Should this also update the local name, as otherwise they are out of sync - though this could mean over-long names or names that don't work well in a different context
		if ($list['name'] != $this->lists[$moniker]['talksdotcamName']) {
			$this->databaseConnection->update ($this->settings['database'], $this->settings['table'], array ('talksdotcamName' => $list['name']), array ('moniker' => $moniker));
		}
		
		# Add in the list series ID for each talk
		$list['talk'] = $this->emulateSeriesIds ($list['talk']);
		
		# Return the data
		return $list;
	}
	
	
	# Function to emulate the series ID, which is missing from the talks.cam XML feed
	private function emulateSeriesIds ($talks)
	{
		# Create a lookup of talks.cam series name to ID
		$seriesNameToId = array ();
		foreach ($this->lists as $moniker => $list) {
			$seriesName = $list['talksdotcamName'];
			$seriesNameToId[$seriesName] = $moniker;
		}
		
		# Add the missing field to the talks, where known
		foreach ($talks as $index => $talk) {
			$seriesName = $talk['series'];
			$talks[$index]['seriesMoniker'] = (isSet ($seriesNameToId[$seriesName]) ? $seriesNameToId[$seriesName] : NULL);
			$talks[$index]['seriesLink'] = (isSet ($seriesNameToId[$seriesName]) ? $this->baseUrl . '/' . $seriesNameToId[$seriesName] . '/' : NULL);
		}
		
		# Return the list
		return $talks;
	}
	
	
	# Function to get a thumbnail for a list
	private function getThumbnail ($moniker)
	{
		# Use list-specific thumbnail if present
		$thumbnail = $this->baseUrl . '/' . $moniker . '/thumbnail153.jpg';
		if (file_exists ($_SERVER['DOCUMENT_ROOT'] . $thumbnail)) {
			return $thumbnail;
		}
		
		# Otherwise return the default blank image
		return $this->baseUrl . '/images/thumbnail153.png';
	}
	
	
	# Function to show a list
	public function showlist ($moniker)
	{
		# Ensure there is a valid moniker
		if (!strlen ($moniker) || !isSet ($this->lists[$moniker])) {
			$html = $this->page404 ();
			echo $html;
			return;
		}
		
		# Create the droplist
		$this->template['droplist'] = $this->droplistHtml ($moniker);
		
		# Get the seminars
		$this->template['seminars'] = $this->getSeminars ($moniker);
		
		# Get the archived seminars
		$this->template['archived'] = $this->getSeminars ($moniker, true);
		
		# Send the list metadata to the template
		$this->template['list'] = $this->lists[$moniker];
		
		# Determine edit rights
		$this->template['isEditor'] = $this->lists[$moniker]['isEditor'];
		
		# Process the template
		$html = $this->templatise ();
		
		# Show the HTML
		echo $html;
	}
	
	
	# Function to create the droplist
	private function droplistHtml ($current)
	{
		# Create the lists
		$lists = array ();
		$lists[$this->baseUrl . '/'] = 'Home';
		foreach ($this->lists as $moniker => $list) {
			if ($moniker == $this->settings['masterList']) {continue;}		// Skip master list
			$url = $list['link'];
			$lists[$url] = $list['name'];
		}
		
		# No droplist if only one entry
		if (count ($lists) < 2) {return false;}
		
		# Truncate strings
		foreach ($lists as $url => $name) {
			$lists[$url] = application::str_truncate ($name, 25, false, false, $respectWordBoundaries = false, $htmlMode = false);
		}
		
		# Set current
		$current = $this->baseUrl . '/' . $current . '/';
		
		# Create the HTML
		$html = application::htmlJumplist ($lists, $current, '', 'jumplist', 0, 'jumplist', $introductoryText = false);
		
		# Return the HTML
		return $html;
	}
	
	
	# Admin editing section, substantially delegated to the sinenomine editing component
	public function editing ($attributes = array (), $deny = false, $sinenomineExtraSettings = array ())
	{
		# Databinding attributes
		$dataBindingAttributes = array (
			array ($this->settings['database'], $this->settings['table'], 'talksdotcamListNumber', array ('prepend' => 'www.talks.cam.ac.uk/show/index/')),
			array ($this->settings['database'], $this->settings['table'], 'talksdotcamName', array ('editable' => false)),
			array ($this->settings['database'], $this->settings['table'], 'editors', array (
				'type' => 'select',
				'multiple' => true,
				'expandable' => true,
				'separator' => ',',
				'defaultPresplit' => true,
				'autocomplete' => $this->settings['usersAutocomplete'],
				'autocompleteOptions' => array ('delay' => 0),
				'output' => array ('processing' => 'compiled'),
				'description' => 'Currently this cannot be obtained automatically from talks.cam, so has to be copied here manually if you want the edit buttons to appear. Type a surname or username to get a username.',
			), ),
		);
		
		# Define tables to deny editing for
		$deny[$this->settings['database']] = array (
			'administrators',
			'settings',
		);
		
		# Define general sinenomine settings
		$sinenomineExtraSettings = array (
				'simpleJoin' => true,
				'fieldFiltering' => false,
				'hideSearchBox' => true,
				'hideExport' => true,
				'autofocus' => true,
				'int1ToCheckbox' => true,
		);
		
		# Run the standard front controller editing integration
		echo parent::editing ($dataBindingAttributes, $deny, $sinenomineExtraSettings);
	}
}

?>
