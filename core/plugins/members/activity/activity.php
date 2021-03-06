<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2015 HUBzero Foundation, LLC.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   hubzero-cms
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

/**
 * Members Plugin class for activity
 */
class plgMembersActivity extends \Hubzero\Plugin\Plugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var  boolean
	 */
	protected $_autoloadLanguage = true;

	/**
	 * Event call to determine if this plugin should return data
	 *
	 * @param   object  $user    Current user
	 * @param   object  $member  Member profile
	 * @return  array   Plugin   name
	 */
	public function &onMembersAreas($user, $member)
	{
		$areas = array();

		if ($user->get('id') == $member->get('id'))
		{
			$areas['activity'] = Lang::txt('PLG_MEMBERS_ACTIVITY');
			$areas['icon']     = 'f056';
		}

		return $areas;
	}

	/**
	 * Event call to return data for a specific member
	 *
	 * @param   object  $user    User
	 * @param   object  $member  MembersProfile
	 * @param   string  $option  Component name
	 * @param   string  $areas   Plugins to return data
	 * @return  array
	 */
	public function onMembers($user, $member, $option, $areas)
	{
		$returnhtml = true;

		$arr = array(
			'html'     => '',
			'metadata' => ''
		);

		// Check if our area is in the array of areas we want to return results for
		if (is_array($areas))
		{
			if (!array_intersect($areas, $this->onMembersAreas($user, $member))
			 && !array_intersect($areas, array_keys($this->onMembersAreas($user, $member))))
			{
				$returnhtml = false;
			}
		}

		// Are we returning HTML?
		if ($returnhtml)
		{
			$this->member = $member;

			$action = Request::getCmd('action', 'feed');

			if (!$this->params->get('email_digests'))
			{
				$action = 'feed';
			}

			switch ($action)
			{
				case 'settings': $arr['html'] = $this->settingsAction(); break;
				case 'savesettings': $arr['html'] = $this->savesettingsAction(); break;
				case 'feed':
				default:         $arr['html'] = $this->feedAction();     break;
			}
		}

		$arr['metadata'] = array();

		// Get the number of unread messages
		$unread = Hubzero\Activity\Recipient::all()
			->whereEquals('scope', 'user')
			->whereEquals('scope_id', $member->get('id'))
			->whereEquals('state', 1)
			->whereEquals('viewed', '0000-00-00 00:00:00')
			->total();

		// Return total message count
		$arr['metadata']['count'] = $unread;

		// Return data
		return $arr;
	}

	/**
	 * Show a feed
	 *
	 * @return  string
	 */
	protected function feedAction()
	{
		$entries = Hubzero\Activity\Recipient::all()
			->including('log')
			->whereEquals('scope', 'user')
			->whereEquals('scope_id', $this->member->get('id'))
			->whereEquals('state', 1)
			->ordered()
			->paginated()
			->rows();

		/* @TODO  Add lists of scopes and actions to filter by
		$categories = \Hubzero\Activity\Log::all()
			->select('action')
			->whereIn('id', $ids)
			->ordered()
			->paginated();
		*/

		$digests = $this->params->get('email_digests');

		$view = $this->view('default', 'activity')
			->set('digests', $digests)
			->set('member', $this->member)
			->set('categories', null)
			->set('rows', $entries);

		return $view->loadTemplate();
	}

	/**
	 * Show settings form
	 *
	 * @return  string
	 */
	protected function settingsAction()
	{
		if (User::isGuest())
		{
			return $this->loginAction();
		}

		if (User::get('id') != $this->member->get('id'))
		{
			App::abort(403, Lang::txt('PLG_MEMBERS_ACTIVITY_NOTAUTH'));
		}

		if (!$this->params->get('email_digests'))
		{
			return $this->feedAction();
		}

		$settings = Hubzero\Activity\Digest::oneByScope(
			$this->member->get('id'),
			'user'
		);

		$view = $this->view('settings', 'activity')
			->set('member', $this->member)
			->set('settings', $settings);

		return $view->loadTemplate();
	}

	/**
	 * Save settings
	 *
	 * @return  mixed
	 */
	protected function savesettingsAction()
	{
		if (User::isGuest())
		{
			return $this->loginAction();
		}

		if (User::get('id') != $this->member->get('id'))
		{
			App::abort(403, Lang::txt('PLG_MEMBERS_ACTIVITY_NOTAUTH'));
		}

		if (!$this->params->get('email_digests'))
		{
			die('here?');
			return $this->feedAction();
		}

		// Check for request forgeries
		Request::checkToken();

		// Incoming
		$settings = Request::getVar('settings', array(), 'post');
		$settings['scope']    = 'user';
		$settings['scope_id'] = $this->member->get('id');

		$row = Hubzero\Activity\Digest::blank()->set($settings);

		// Store new content
		if (!$row->save())
		{
			$this->setError($row->getError());
			return $this->settingsAction();
		}

		// Log the activity
		Event::trigger('system.logActivity', [
			'activity' => [
				'action'      => 'updated',
				'scope'       => 'activity.settings',
				'scope_id'    => $row->get('id'),
				'description' => Lang::txt('PLG_MEMBERS_ACTIVITY_SETTINGS_UPDATED')
			],
			'recipients' => [
				$this->member->get('id')
			]
		]);

		// Redirect
		App::redirect(
			Route::url($this->member->link() . '&active=activity', false),
			Lang::txt('PLG_MEMBERS_ACTIVITY_SETTINGS_SAVED')
		);
	}

	/**
	 * Redirect to the login page
	 *
	 * @return  void
	 */
	protected function loginAction()
	{
		$return = base64_encode(Route::url($this->member->link() . '&active=' . $this->_name, false, true));

		App::redirect(
			Route::url('index.php?option=com_users&view=login&return=' . $return, false),
			Lang::txt('MEMBERS_LOGIN_NOTICE')
		);
	}
}
