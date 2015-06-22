<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2015 Purdue University. All rights reserved.
 *
 * This file is part of: The HUBzero(R) Platform for Scientific Collaboration
 *
 * The HUBzero(R) Platform for Scientific Collaboration (HUBzero) is free
 * software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * HUBzero is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   hubzero-cms
 * @author    Shawn Rice <zooley@purdue.edu>
 * @copyright Copyright 2005-2015 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

namespace Components\Config\Models;

use JModelForm;
use JAccessRules;
use JAccess;
use JFactory;
use JConfig;
use JPath;
use Lang;
use JTable;
use Hubzero\Config\Registry;
use JClientHelper;
use JFilterOutput;

jimport('joomla.application.component.modelform');

/**
 * Model class for Application config
 */
class Application extends JModelForm
{
	/**
	 * Method to get a form object.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 * @return  mixed    A JForm object on success, false on failure
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_config.application', 'application', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the configuration data.
	 *
	 * This method will load the global configuration data straight from
	 * JConfig. If configuration data has been saved in the session, that
	 * data will be merged into the original data, overwriting it.
	 *
	 * @return  array  An array containg all global config data.
	 * @since   1.6
	 */
	public function getData()
	{
		// Get the config data.
		$config = new JConfig();
		$data = \Hubzero\Utility\Arr::fromObject($config);

		// Prime the asset_id for the rules.
		$data['asset_id'] = 1;

		// Get the text filter data
		$params = \Component::params('com_config');
		$data['filters'] = \Hubzero\Utility\Arr::fromObject($params->get('filters'));

		// If no filter data found, get from com_content (update of 1.6/1.7 site)
		if (empty($data['filters']))
		{
			$contentParams = \Component::params('com_content');
			$data['filters'] = \Hubzero\Utility\Arr::fromObject($contentParams->get('filters'));
		}

		// Check for data in the session.
		$temp = User::getState('com_config.config.global.data');

		// Merge in the session data.
		if (!empty($temp))
		{
			$data = array_merge($data, $temp);
		}

		return $data;
	}

	/**
	 * Method to save the configuration data.
	 *
	 * @param   array  An array containing all global config data.
	 * @return  bool   True on success, false on failure.
	 * @since   1.6
	 */
	public function save($data)
	{
		// Save the rules
		if (isset($data['rules']))
		{
			$rules = new JAccessRules($data['rules']);

			// Check that we aren't removing our Super User permission
			// Need to get groups from database, since they might have changed
			$myGroups = JAccess::getGroupsByUser(\User::get('id'));
			$myRules = $rules->getData();
			$hasSuperAdmin = $myRules['core.admin']->allow($myGroups);
			if (!$hasSuperAdmin) {
				$this->setError(Lang::txt('COM_CONFIG_ERROR_REMOVING_SUPER_ADMIN'));
				return false;
			}

			$asset = JTable::getInstance('asset');
			if ($asset->loadByName('root.1'))
			{
				$asset->rules = (string) $rules;

				if (!$asset->check() || !$asset->store())
				{
					Notify::error('SOME_ERROR_CODE', $asset->getError());
				}
			}
			else
			{
				$this->setError(Lang::txt('COM_CONFIG_ERROR_ROOT_ASSET_NOT_FOUND'));
				return false;
			}
			unset($data['rules']);
		}

		// Save the text filters
		if (isset($data['filters']))
		{
			$registry = new Registry(array('filters' => $data['filters']));

			$extension = JTable::getInstance('extension');

			// Get extension_id
			$extension_id = $extension->find(array('name' => 'com_config'));

			if ($extension->load((int) $extension_id))
			{
				$extension->params = (string) $registry;
				if (!$extension->check() || !$extension->store())
				{
					Notify::error('SOME_ERROR_CODE', $extension->getError());
				}
			}
			else
			{
				$this->setError(Lang::txt('COM_CONFIG_ERROR_CONFIG_EXTENSION_NOT_FOUND'));
				return false;
			}
			unset($data['filters']);
		}

		// Get the previous configuration.
		$prev = new JConfig();
		$prev = \Hubzero\Utility\Arr::fromObject($prev);

		// Merge the new data in. We do this to preserve values that were not in the form.
		$data = array_merge($prev, $data);

		// Perform miscellaneous options based on configuration settings/changes.
		// Escape the offline message if present.
		if (isset($data['offline_message']))
		{
			$data['offline_message'] = \Hubzero\Utility\String::ampReplace($data['offline_message']);
		}

		// Purge the database session table if we are changing to the database handler.
		if ($prev['session_handler'] != 'database' && $data['session_handler'] == 'database')
		{
			$table = JTable::getInstance('session');
			$table->purge(-1);
		}

		if (empty($data['cache_handler']))
		{
			$data['caching'] = 0;
		}

		// Clean the cache if disabled but previously enabled.
		if (!$data['caching'] && $prev['caching'])
		{
			\Cache::clean();
		}

		// Create the new configuration object.
		$config = new Registry($data);

		// Overwrite the old FTP credentials with the new ones.
		$temp = \Config::getRoot();
		$temp->set('ftp_enable', $data['ftp_enable']);
		$temp->set('ftp_host', $data['ftp_host']);
		$temp->set('ftp_port', $data['ftp_port']);
		$temp->set('ftp_user', $data['ftp_user']);
		$temp->set('ftp_pass', $data['ftp_pass']);
		$temp->set('ftp_root', $data['ftp_root']);

		// Clear cache of com_config component.
		$this->cleanCache('_system');

		// Write the configuration file.
		return $this->writeConfigFile($config);
	}

	/**
	 * Method to unset the root_user value from configuration data.
	 *
	 * This method will load the global configuration data straight from
	 * JConfig and remove the root_user value for security, then save the configuration.
	 *
	 * @return  boolean
	 * @since   1.6
	 */
	public function removeroot()
	{
		// Get the previous configuration.
		$prev = new JConfig();
		$prev = \Hubzero\Utility\Arr::fromObject($prev);

		// Create the new configuration object, and unset the root_user property
		unset($prev['root_user']);

		$config = new Registry($prev);

		// Write the configuration file.
		return $this->writeConfigFile($config);
	}

	/**
	 * Method to write the configuration to a file.
	 *
	 * @param   object  $config  A Registry object containing all global config data.
	 * @return  bool    True on success, false on failure.
	 * @since   2.5.4
	 */
	private function writeConfigFile(Registry $config)
	{
		// Set the configuration file path.
		$file = JPATH_CONFIGURATION . '/configuration.php';

		// Get the new FTP credentials.
		$ftp = JClientHelper::getCredentials('ftp', true);

		// Attempt to make the file writeable if using FTP.
		if (!$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0640'))
		{
			Notify::error('SOME_ERROR_CODE', Lang::txt('COM_CONFIG_ERROR_CONFIGURATION_PHP_NOTWRITABLE'));
		}

		// Attempt to write the configuration file as a PHP class named JConfig.
		$configuration = $config->toString('PHP', array('class' => 'JConfig', 'closingtag' => false));
		if (!Filesystem::write($file, $configuration))
		{
			$this->setError(Lang::txt('COM_CONFIG_ERROR_WRITE_FAILED'));
			return false;
		}

		// Attempt to make the file unwriteable if using FTP.
		if (!$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0440'))
		{
			Notify::error('SOME_ERROR_CODE', Lang::txt('COM_CONFIG_ERROR_CONFIGURATION_PHP_NOTUNWRITABLE'));
		}

		return true;
	}
}