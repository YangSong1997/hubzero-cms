<?php
/**
 * @package		HUBzero CMS
 * @author		Alissa Nedossekina <alisa@purdue.edu>
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

if ($this->project->private)
{
	$privacy = '<span class="private">' . ucfirst(JText::_('COM_PROJECTS_PRIVATE')) . '</span>';
}
else
{
	$privacy = '<a href="' . JRoute::_('index.php?option=' . $this->option . '&alias=' . $this->project->alias . '&preview=1') .'" title="' . JText::_('COM_PROJECTS_PREVIEW_PUBLIC_PROFILE') . '">' . ucfirst(JText::_('COM_PROJECTS_PUBLIC')) . '</a>';
}

$start = ($this->publicView == false && $this->project->owner) ? '<span class="h-privacy">' . $privacy . '</span> ' . strtolower(JText::_('COM_PROJECTS_PROJECT')) : ucfirst(JText::_('COM_PROJECTS_PROJECT'));

?>
<div id="project-header" class="project-header">
	<div class="grid">
		<div class="col span10">
			<div class="pimage-container">
			<?php
			// Draw image
			$this->view('_image', 'projects')
			     ->set('project', $this->project)
			     ->set('option', $this->option)
			     ->display();
			?>
			</div>
			<div class="ptitle-container">
				<h2><a href="<?php echo JRoute::_('index.php?option=' . $this->option . a . 'alias=' . $this->project->alias); ?>"><?php echo \Hubzero\Utility\String::truncate($this->project->title, 50); ?> <span>(<?php echo $this->project->alias; ?>)</span></a></h2>
				<p>
				<?php echo $start .' '.JText::_('COM_PROJECTS_BY').' ';
				if ($this->project->owned_by_group)
				{
					$group = \Hubzero\User\Group::getInstance( $this->project->owned_by_group );
					if ($group)
					{
						echo ' '.JText::_('COM_PROJECTS_GROUP').' <a href="/groups/'.$group->get('cn').'">'.$group->get('cn').'</a>';
					}
					else
					{
						echo JText::_('COM_PROJECTS_UNKNOWN').' '.JText::_('COM_PROJECTS_GROUP');
					}
				}
				else
				{
					echo '<a href="/members/'.$this->project->owned_by_user.'">'.$this->project->fullname.'</a>';
				}
				?>
				</p>
			</div>
		</div>
		<div class="col span2 omega">
			<?php
			// Member options
			if ($this->publicView == false)
			{
				$this->view('_options', 'projects')
				     ->set('project', $this->project)
				     ->set('option', $this->option)
				     ->display();
			}
 			?>
		</div>
		<div class="clear"></div>
	</div>
</div>
