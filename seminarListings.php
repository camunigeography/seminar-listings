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
			'someaction' => array (
				'description' => 'Do some action',
				'url' => 'someaction/',
				'tab' => 'Some action',
				'icon' => 'add',
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
			  `id` int(11) NOT NULL COMMENT 'Automatic key (ignored)' PRIMARY KEY,
			  `somesetting` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Some setting',
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
		//
		
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
	
	
	# Function to do some action
	public function someaction ()
	{
		//
		
		# Show the HTML
		echo $html;
	}
}

?>
