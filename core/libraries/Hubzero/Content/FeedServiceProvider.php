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

namespace Hubzero\Content;

use Hubzero\Base\ServiceProvider;

/**
 * Feed Reader service provider
 */
class FeedServiceProvider extends ServiceProvider
{
	/**
	 * Register the service provider.
	 *
	 * @return  void
	 */
	public function register()
	{
		$this->app['feed.parser'] = function($app)
		{
			$cache  = PATH_APP . DS . 'app' . DS . 'cache';
			$cache .= DS . (isset($app['client']->alias) ? $app['client']->alias : $app['client']->name);

			include_once(PATH_CORE . DS . 'libraries' . DS . 'simplepie' . DS . 'simplepie.php');

			$reader = new \SimplePie(null, $cache, $app['config']->get('cachetime', 15));
			$reader->enable_cache(false);
			$reader->force_feed(true);

			return $reader;
		};
	}
}