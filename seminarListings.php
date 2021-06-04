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
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='System administrators';
			
			-- Settings
			CREATE TABLE IF NOT EXISTS `settings` (
			  `id` int(11) NOT NULL COMMENT 'Automatic key (ignored)' PRIMARY KEY,
			  `somesetting` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Some setting',
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Settings';
			INSERT INTO settings (id) VALUES (1);
			
			-- My table
			CREATE TABLE IF NOT EXISTS `mytable` (
			  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Automatic key',
			  ...
			  `updatedAt` datetime NOT NULL COMMENT 'Updated at',
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='My table';
		";
	}
	
	
	
	# Additional processing
	public function main ()
	{
		
	}
	
	
	
	# Home page
	public function home ()
	{
		//
		
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
