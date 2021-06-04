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
			'applicationName'		=> 'Seminar listings',
			'div'					=> strtolower (__CLASS__),
			'databaseStrictWhere'	=> true,
			'administrators'		=> 'administrators',
			'tabUlClass'			=> 'tabsflat',
			'database'				=> 'seminarlistings',
			'table'					=> 'lists',
			'disableTabs'			=> true,
			'useTemplating'			=> true,
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
				'url' => '%1/',
				'tab' => 'Show list',
				'icon' => 'application_view_list',
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
			  `talksdotcamId` int NOT NULL COMMENT 'Talks.cam list number (see end of URL)',
			  `moniker` varchar(50) NOT NULL COMMENT 'URL moniker',
			  `archived` tinyint DEFAULT NULL COMMENT 'Archived?',
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Lists';
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
		
		# Send to the template
		$this->template['lists'] = $listsByGroup[''];
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
		$listsById = $this->databaseConnection->select ($this->settings['database'], $this->settings['table'], array (), array (), true, $orderBy = 'name');
		
		# Reorganise by moniker
		$lists = application::reindex ($listsById, 'moniker', false);
		
		# Decorate the lists
		foreach ($lists as $moniker => $list) {
			$lists[$moniker]['link'] = $this->baseUrl . "/{$moniker}/";
			$lists[$moniker]['talksdotcamUrl'] = 'https://talks.cam.ac.uk/show/index/' . $list['talksdotcamId'];
		}
		
		# Return the list of lists
		return $lists;
	}
	
	
	# Function to get seminars in a list
	private function getSeminars ($moniker)
	{
		# Ensure the list ID exists
		if (!isSet ($this->lists[$moniker])) {return array ();}
		
		# Get the feed
		$listId = $this->lists[$moniker]['talksdotcamId'];
		$list = $this->getFeed ($listId);
		
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
	private function getFeed ($listId)
	{
		# Construct the URL
		$url = "https://talks.cam.ac.uk/show/xml/{$listId}?layout=empty";
		
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
		
		# Send the list metadata to the template
		$this->template['list'] = $this->lists[$moniker];
		
		# Process the template
		$html = $this->templatise ();
		
		# Show the HTML
		echo $html;
	}
	
	
	# Function to do some action
	public function someaction ()
	{
		//
		
		# Show the HTML
		echo $html;
	}
}

?>
