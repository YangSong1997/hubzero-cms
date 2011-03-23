<?php
/**
 * @package		HUBzero CMS
 * @author		Shawn Rice <zooley@purdue.edu>
 * @copyright	Copyright 2005-2009 by Purdue Research Foundation, West Lafayette, IN 47906
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 *
 * Copyright 2005-2009 by Purdue Research Foundation, West Lafayette, IN 47906.
 * All rights reserved.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License,
 * version 2 as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

//-----------

jimport( 'joomla.plugin.plugin' );
JPlugin::loadLanguage( 'plg_groups_wiki' );

//-----------

class plgGroupsWiki extends JPlugin
{
	public function plgGroupsWiki(&$subject, $config)
	{
		parent::__construct($subject, $config);

		// Load plugin parameters
		$this->_plugin = JPluginHelper::getPlugin( 'groups', 'wiki' );
		$this->_params = new JParameter( $this->_plugin->params );
	}
	
	//-----------
	
	public function &onGroupAreas()
	{
		$area = array(
			'name' => 'wiki',
			'title' => JText::_('PLG_GROUPS_WIKI'),
			'default_access' => 'members'
		);
		
		return $area;
	}

	//-----------

	public function onGroup( $group, $option, $authorized, $limit=0, $limitstart=0, $action='', $access, $areas=null )
	{
		$return = 'html';
		$active = 'wiki';
		
		// The output array we're returning
		$arr = array(
			'html'=>''
		);
		
		//get this area details
		$this_area = $this->onGroupAreas();
		
		// Check if our area is in the array of areas we want to return results for
		if (is_array( $areas ) && $limit) {
			if(!in_array($this_area['name'],$areas)) {
				return;
			}
		}
		
		//set group members plugin access level
		$group_plugin_acl = $access[$active];

		//if set to nobody make sure cant access
		if($group_plugin_acl == 'nobody') {
			$arr['html'] = "<p class=\"info\">".JText::sprintf('GROUPS_PLUGIN_OFF',ucfirst($active))."</p>";
			return $arr;
		}
		
		// Determine if we need to return any HTML (meaning this is the active plugin)
		if ($return == 'html') {
			// Set some variables for the wiki
			$_REQUEST['task'] = $action;
			$scope = trim(JRequest::getVar( 'scope', '' ));
			if (!$scope) {
				$_REQUEST['scope'] = $group->get('cn').DS.$active;
			}
			
			// Initiate the wiki code
			//$arr['html'] = $this->wiki( $group );
			global $mainframe;

			ximport('Hubzero_Document');
			Hubzero_Document::addPluginStylesheet('groups', 'wiki');

			// Import some needed libraries
			ximport('Hubzero_User_Helper');

			include_once(JPATH_ROOT.DS.'components'.DS.'com_wiki'.DS.'tables'.DS.'attachment.php');
			include_once(JPATH_ROOT.DS.'components'.DS.'com_wiki'.DS.'tables'.DS.'author.php');
			include_once(JPATH_ROOT.DS.'components'.DS.'com_wiki'.DS.'tables'.DS.'comment.php');
			include_once(JPATH_ROOT.DS.'components'.DS.'com_wiki'.DS.'tables'.DS.'log.php');
			include_once(JPATH_ROOT.DS.'components'.DS.'com_wiki'.DS.'tables'.DS.'page.php');
			include_once(JPATH_ROOT.DS.'components'.DS.'com_wiki'.DS.'tables'.DS.'revision.php');
			
			include_once(JPATH_ROOT.DS.'components'.DS.'com_wiki'.DS.'helpers'.DS.'config.php');
			include_once(JPATH_ROOT.DS.'components'.DS.'com_wiki'.DS.'helpers'.DS.'differenceengine.php');
			include_once(JPATH_ROOT.DS.'components'.DS.'com_wiki'.DS.'helpers'.DS.'html.php');
			//include_once(JPATH_ROOT.DS.'components'.DS.'com_wiki'.DS.'helpers'.DS.'macro.php');
			//include_once(JPATH_ROOT.DS.'components'.DS.'com_wiki'.DS.'helpers'.DS.'math.php');
			//include_once(JPATH_ROOT.DS.'components'.DS.'com_wiki'.DS.'helpers'.DS.'parser.php');
			include_once(JPATH_ROOT.DS.'components'.DS.'com_wiki'.DS.'helpers'.DS.'sanitizer.php');
			include_once(JPATH_ROOT.DS.'components'.DS.'com_wiki'.DS.'helpers'.DS.'setup.php');
			include_once(JPATH_ROOT.DS.'components'.DS.'com_wiki'.DS.'helpers'.DS.'stringutils.php');
			include_once(JPATH_ROOT.DS.'components'.DS.'com_wiki'.DS.'helpers'.DS.'tags.php');
			include_once(JPATH_ROOT.DS.'components'.DS.'com_wiki'.DS.'helpers'.DS.'utfnormalutil.php');
			
			include_once(JPATH_ROOT.DS.'components'.DS.'com_wiki'.DS.'controller.php');

			// Instantiate controller
			$controller = new WikiController( array('name'=>'groups','sub'=>'wiki','group'=>$group->get('cn')) );
			$controller->mainframe = $mainframe;

			// Catch any echoed content with ob
			ob_start();
			$controller->execute();
			$controller->redirect();
			$content = ob_get_contents();
			ob_end_clean();

			// Return the content
			$arr['html'] = $content;
		} else {
			// Get a count of the number of pages
			$database =& JFactory::getDBO();
			
			$access = " AND w.access!=1";
			if ($authorized) {
				$access = "";
			}

			$query = "SELECT v.pageid, w.title, w.pagename, w.scope, w.group, w.access, v.version, v.created_by, v.created
						FROM #__wiki_page AS w, #__wiki_version AS v
						WHERE w.id=v.pageid AND v.approved=1 AND w.group='".$group->get('cn')."' AND w.scope='".$group->get('cn').DS.$active."' $access
						ORDER BY created DESC LIMIT $limitstart,$limit";
			$database->setQuery( $query );
			$rows = $database->loadObjectList();

			$database->setQuery( "SELECT COUNT(*) FROM #__wiki_page AS w WHERE w.scope='".$group->get('cn').DS.'wiki'."' AND w.group='".$group->get('cn')."' $access" );
			$num = $database->loadResult();

			// Build the HTML meant for the "profile" tab's metadata overview
			$arr['metadata'] = '<a href="'.JRoute::_('index.php?option='.$option.'&gid='.$group->get('cn').'&active=wiki').'">'.JText::sprintf('PLG_GROUPS_WIKI_NUMBER_PAGES',$num).'</a>'."\n";
			//$arr['dashboard'] = $this->dashboard( $group, $rows, $authorized, $option );
			
			// Instantiate a vew
			ximport('Hubzero_Plugin_View');
			$view = new Hubzero_Plugin_View(
				array(
					'folder'=>'groups',
					'element'=>'wiki',
					'name'=>'dashboard'
				)
			);

			// Pass the view some info
			$view->option = $option;
			$view->group = $group;
			$view->authorized = $authorized;
			$view->rows = $rows;
			if ($this->getError()) {
				$view->setError( $this->getError() );
			}
			$arr['dashboard'] = $view->loadTemplate();
		}
		
		// Return the output
		return $arr;
	}
	
	//-----------
	
	public function onGroupDelete( $group ) 
	{
		// Get all the IDs for pages associated with this group
		$ids = $this->getPageIDs( $group->get('cn') );

		// Import needed libraries
		include_once(JPATH_ROOT.DS.'components'.DS.'com_wiki'.DS.'tables'.DS.'page.php');
		
		// Instantiate a WikiPage object
		$database =& JFactory::getDBO();
		$wp = new WikiPage( $database );

		// Start the log text
		$log = JText::_('PLG_GROUPS_WIKI_LOG').': ';
		
		if (count($ids) > 0) {
			// Loop through all the IDs for pages associated with this group
			foreach ($ids as $id)
			{
				// Delete all items linked to this page
				$wp->deleteBits( $id->id );
				
				// Delete the wiki page last in case somehting goes wrong
				$wp->delete( $id->id );
				
				// Add the page ID to the log
				$log .= $id->id.' '."\n";
			}
		} else {
			$log .= JText::_('PLG_GROUPS_WIKI_NO_RESULTS_FOUND')."\n";
		}
		
		// Return the log
		return $log;
	}
	
	//-----------
	
	public function onGroupDeleteCount( $group ) 
	{
		return JText::_('PLG_GROUPS_WIKI_LOG').': '.count( $this->getPageIDs( $group->get('cn') ));
	}
	
	//-----------
	
	public function getPageIDs( $gid=NULL )
	{
		if (!$gid) {
			return array();
		}
		$database =& JFactory::getDBO();
		$database->setQuery( "SELECT id FROM #__wiki_page AS p WHERE p.group='".$gid."'" );
		return $database->loadObjectList();
	}
}