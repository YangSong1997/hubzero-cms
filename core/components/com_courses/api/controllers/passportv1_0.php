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
 * @author    Sam Wilson <samwilson@purdue.edu>
 * @copyright Copyright 2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

namespace Components\Courses\Api\Controllers;

use Components\Courses\Models\Course;
use Components\Courses\Models\Member;
use Request;
use App;
use Date;

require_once __DIR__ . DS . 'base.php';
require_once dirname(dirname(__DIR__)) . DS . 'models' . DS . 'section' . DS . 'badge.php';
require_once dirname(dirname(__DIR__)) . DS . 'models' . DS . 'member.php';
require_once dirname(dirname(__DIR__)) . DS . 'models' . DS . 'memberBadge.php';

/**
 * API controller for the time component
 */
class Passportv1_0 extends base
{
	/**
	 * Passport badges. Placeholder for now.
	 *
	 * @apiMethod POST
	 * @apiUri    /courses/passport/badge
	 * @apiParameter {
	 * 		"name":        "action",
	 * 		"description": "Badge action",
	 * 		"type":        "string",
	 * 		"required":    true,
	 * 		"default":     null
	 * }
	 * @apiParameter {
	 * 		"name":        "badge_id",
	 * 		"description": "Passport badge ID",
	 * 		"type":        "integer",
	 * 		"required":    true,
	 * 		"default":     null
	 * }
	 * @apiParameter {
	 * 		"name":        "user_email",
	 * 		"description": "Email address to which the badge was asserted",
	 * 		"type":        "string",
	 * 		"required":    true,
	 * 		"default":     null
	 * }
	 * @return    void
	 */
	public function badgeTask()
	{
		// Require authentication and authorization
		$this->authorizeOrFail();

		$action     = Request::getVar('action', '');
		$badge_id   = Request::getVar('badge_id', '');
		$user_email = Request::getVar('user_email', '');

		if (empty($action))
		{
			App::abort(400, 'Please provide action');
		}
		if ($action != 'accept' && $action != 'deny')
		{
			App::abort(400, 'Bad action. Must be either accept or deny');
		}
		if (empty($badge_id))
		{
			App::abort(400, 'Please provide badge ID');
		}
		if (empty($user_email))
		{
			App::abort(400, 'Please provide user email');
		}

		// Find user by email
		$user_email = \Hubzero\User\Profile\Helper::find_by_email($user_email);

		if (empty($user_email[0]))
		{
			App::abort(404, 'User was not found');
		}
		$user = \Hubzero\User\Profile::getInstance($user_email[0]);
		if ($user === false)
		{
			App::abort(404, 'User was not found');
		}

		$user_id = $user->get('uidNumber');

		// Get section from provider badge id
		$section_badge = \Components\Courses\Models\Section\Badge::loadByProviderBadgeId($badge_id);

		// Check if there is a match
		if (!$section_id = $section_badge->get('section_id'))
		{
			App::abort(400, 'No matching badge found');
		}

		// Get member id via user id and section id
		$member = \Components\Courses\Models\Member::getInstance($user_id, 0, 0, $section_id);

		// Check if there is a match
		if (!$member->get('id'))
		{
			App::abort(400, 'Matching course member not found');
		}

		// Now actually load the badge
		$member_badge = \Components\Courses\Models\MemberBadge::loadByMemberId($member->get('id'));

		// Check if there is a match
		if (!$member_badge->get('id'))
		{
			App::abort(400, 'This member does not have a matching badge entry');
		}

		$now = Date::toSql();

		$member_badge->set('action', $action);
		$member_badge->set('action_on', $now);
		$member_badge->store();

		// Return message
		$this->send('Passport data saved.');
	}

	/**
	 * Helper function to check whether or not someone is using oauth and authorized to use this call
	 *
	 * @return bool
	 */
	private function authorize_call()
	{
		$consumerKey = Request::getVar('oauth_consumer_key', null, 'post');

		//get the userid and attempt to load user profile
		$userid = App::get('authn')['user_id'];
		$user = \Hubzero\User\Profile::getInstance($userid);
		//make sure we have a user
		if ($user === false)
		{
			/*
			$this->errorMessage(401, 'You don\'t have permission to do this');
			return;
			*/
		}

		// Get the requested path
		$path = Request::path();

		// Do access check
		// @NOTE: The following assumption is made: the code check only permissions for the closest parent. Parent's parent permissions are not inherited.

		$db = App::get('db');

		// First find the closest matching permission (closest parent, longest path).
		$sql = 'SELECT `path` FROM `#__api_permissions`
				WHERE INSTR(' . $db->quote($path) . ', `path`) = 1
				GROUP BY LENGTH(`path`)
				ORDER BY LENGTH(`path`) DESC
				LIMIT 1';

		$db->setQuery($sql);
		$db->query();

		// Check if there is a match, if no match, no permissions set, good to go
		if (!$db->getNumRows())
		{
			return true;
		}

		$permissions_path = $db->loadResult();

		// Get all groups the current user is a member of
		$user_groups = array();
		if (!empty($user))
		{
			$user_groups = $user->getGroups('members');
		}

		// Next see if the user is allowed to make this call
		$sql = 'SELECT `user_id`, `group_id` FROM `#__api_permissions` WHERE `path` = ' . $db->quote($permissions_path) . ' AND
				(`user_id` = ' . $db->quote($userid) . ' OR `consumer_key` = ' . $db->quote($consumerKey) . ' OR 0';

		foreach ($user_groups as $group)
		{
			$sql .= ' OR `group_id` = ' . $db->quote($group->gidNumber);
		}

		$sql .= ')';
		$db->setQuery($sql);
		$db->query();

		// There is a match, permission granted
		if ($db->getNumRows())
		{
			return true;
		}

		// No match, too bad. Unauthorized
		App::abort(401, 'You don\'t have permission to make this call');
	}
}