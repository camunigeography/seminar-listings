<?php

# Class to create a seminar listings system, integrating talks.cam
require_once ('frontControllerApplication.php');
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
			  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Username' PRIMARY KEY,
			  `active` enum('','Yes','No') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Yes' COMMENT 'Currently active?',
			  `privilege` enum('Administrator','Restricted administrator') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Administrator' COMMENT 'Administrator level'
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='System administrators';
			
			-- Settings
			CREATE TABLE IF NOT EXISTS `settings` (
			  `id` int NOT NULL AUTO_INCREMENT COMMENT 'Automatic key (ignored)' PRIMARY KEY,
			  `masterList` VARCHAR(50) NOT NULL DEFAULT 'master' COMMENT 'Master list moniker'
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Settings';
			INSERT INTO settings (id) VALUES (1);
			
			-- Lists
			CREATE TABLE IF NOT EXISTS `lists` (
			  `id` int NOT NULL AUTO_INCREMENT COMMENT 'Automatic key',
			  `name` varchar(255) NOT NULL COMMENT 'List name',
			  `talksdotcamListNumber` int NOT NULL COMMENT 'Talks.cam list number (see end of URL)',
			  `moniker` varchar(50) NOT NULL COMMENT 'URL moniker',
			  `categoryId` VARCHAR(255) NULL COMMENT 'Category',
			  `archived` tinyint DEFAULT NULL COMMENT 'Archived?',
			  `ordering` INT NOT NULL DEFAULT '5' COMMENT 'Ordering (1=first, 9=last)',
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
		$this->template['seminars'] = $this->getSeminars ($this->settings['masterList']);

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
			$lists[$moniker]['thumbnail'] = $this->getThumbnail ($moniker);
		}
		
		# Return the list of lists
		return $lists;
	}
	
	
	# Function to get seminars in a list
	private function getSeminars ($moniker, $archived = false)
	{
		# Ensure the list ID exists
		if (!isSet ($this->lists[$moniker])) {return array ();}
		
		# Get the feed
		$listId = $this->lists[$moniker]['talksdotcamListNumber'];
		$list = $this->getFeed ($listId, $archived);
		
		# Add the metadata from the upstream feed to the list metadata
		$this->lists[$moniker]['details'] = $list['details'];
		
		# Add HTML version of the details
		$this->lists[$moniker]['detailsHtml'] = application::formatTextBlock ($list['details']);
		
		# Convert talks to simplified structure
		$seminars = array ();
		foreach ($list['talk'] as $talk) {
			$seminars[] = array (
				'id' => $talk['id'],
				'title' => $talk['title'],
				'speaker' => $talk['speaker'],
				'abstract' => $talk['abstract'],
				'venue' => $talk['venue'],
				'date' => date ('jS F Y', strtotime ($talk['start_time'])),
				'url' => $this->baseUrl . '//#' . $talk['id'],
			);
		}
		
		# Return the list of seminars
		return $seminars;
	}
	
	
	# Function to get a feed for a list
	private function getFeed ($listId, $archived = false)
	{
		# Construct the URL
		$url = "https://talks.cam.ac.uk/show/xml/{$listId}?layout=empty";
		
		# For archived mode, add additional parameters
		if ($archived) {
			$url .= '&seconds_after_today=0&reverse_order=true';
		}
		
		# Get the data
		ini_set ('default_socket_timeout', 4);
		$xmlString = file_get_contents ($url);
		
		# Convert to XML
		$xml = simplexml_load_string ($xmlString);
		$json = json_encode ($xml);
		$list = json_decode ($json, true);
		
		# End if none
		if (!$list || !isSet ($list['talk'])) {return false;}
		
		# Return the data
		return $list;
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
		
		# Get the seminars
		$this->template['seminars'] = $this->getSeminars ($moniker);
		
		# Get the archived seminars
		$this->template['archived'] = $this->getSeminars ($moniker, true);
		
		# Send the list metadata to the template
		$this->template['list'] = $this->lists[$moniker];
		
		# Process the template
		$html = $this->templatise ();
		
		# Show the HTML
		echo $html;
	}
	
	
	# Admin editing section, substantially delegated to the sinenomine editing component
	public function editing ($attributes = array (), $deny = false, $sinenomineExtraSettings = array ())
	{
		# Databinding attributes
		$dataBindingAttributes = array (
			array ($this->settings['database'], $this->settings['table'], 'talksdotcamListNumber', array ('prepend' => 'www.talks.cam.ac.uk/show/index/')),
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
