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

JPluginHelper::importPlugin( 'hubzero' );
$dispatcher =& JDispatcher::getInstance();
$tf = $dispatcher->trigger( 'onGetMultiEntry', array(array('tags', 'tags', 'actags','',$this->tags)) );
?>
<?php if ($this->getError()) { ?>
	<p class="error"><?php echo $this->getError(); ?></p>
<?php } ?>
	
	<form action="<?php echo JRoute::_('index.php?option='.$this->option.'&gid='.$this->group->get('cn').'&active=blog'); ?>" method="post" id="hubForm" class="full">
		<div class="explaination">
			<table class="wiki-reference" summary="Wiki Syntax Reference">
				<caption>Wiki Syntax Reference</caption>
				<tbody>
					<tr>
						<td>'''bold'''</td>
						<td><b>bold</b></td>
					</tr>
					<tr>
						<td>''italic''</td>
						<td><i>italic</i></td>
					</tr>
					<tr>
						<td>__underline__</td>
						<td><span style="text-decoration:underline;">underline</span></td>
					</tr>
					<tr>
						<td>{{{monospace}}}</td>
						<td><code>monospace</code></td>
					</tr>
					<tr>
						<td>~~strike-through~~</td>
						<td><del>strike-through</del></td>
					</tr>
					<tr>
						<td>^superscript^</td>
						<td><sup>superscript</sup></td>
					</tr>
					<tr>
						<td>,,subscript,,</td>
						<td><sub>subscript</sub></td>
					</tr>
					<tr>
						<td colspan="2"><a href="<?php echo JRoute::_('index.php?option=com_topics&scope=&pagename=Help:WikiMacros#image'); ?>" class="popup 400x500">[[Image(filename.jpg)]]</a> includes an image</td>
					</tr>
					<tr>
						<td colspan="2"><a href="<?php echo JRoute::_('index.php?option=com_topics&scope=&pagename=Help:WikiMacros#file'); ?>" class="popup 400x500">[[File(filename.pdf)]]</a> includes a file</td>
					</tr>
				</tbody>
			</table>
			<h4 id="files-header"><?php echo JText::_('Uploaded files'); ?></h4>
			<iframe width="100%" height="370" name="filer" id="filer" src="<?php echo JRoute::_('index.php?option=com_blog&id='.$this->group->get('gidNumber').'&scope=group&task=media&no_html=1'); ?>"></iframe>
		</div>
		<fieldset>
			<h3><?php echo JText::_('PLG_GROUPS_BLOG_EDIT_DETAILS'); ?></h3>

			<label<?php if ($this->task == 'save' && !$this->entry->title) { echo ' class="fieldWithErrors"'; } ?>>
				<?php echo JText::_('PLG_GROUPS_BLOG_TITLE'); ?>
				<input type="text" name="entry[title]" size="35" value="<?php echo htmlentities(stripslashes($this->entry->title),ENT_COMPAT,'UTF-8'); ?>" />
			</label>
<?php if ($this->task == 'save' && !$this->entry->title) { ?>
			<p class="error"><?php echo JText::_('PLG_GROUPS_BLOG_ERROR_PROVIDE_TITLE'); ?></p>
<?php } ?>

			<label>
				<?php echo JText::_('PLG_GROUPS_BLOG_FIELD_CONTENT'); ?>
				<?php
				ximport('Hubzero_Wiki_Editor');
				$editor =& Hubzero_Wiki_Editor::getInstance();
				echo $editor->display('entry[content]', 'entrycontent', stripslashes($this->entry->content), '', '50', '40');
				?>
				<span class="hint"><a href="<?php echo JRoute::_('index.php?option=com_topics&scope=&pagename=Help:WikiFormatting'); ?>" class="popup 400x500">Wiki formatting</a> is allowed.</span>
			</label>
<?php if ($this->task == 'save' && !$this->entry->content) { ?>
			<p class="error"><?php echo JText::_('PLG_GROUPS_BLOG_ERROR_PROVIDE_CONTENT'); ?></p>
<?php } ?>			

			<label>
				<?php echo JText::_('PLG_GROUPS_BLOG_FIELD_TAGS'); ?>
<?php if (count($tf) > 0) {
	echo $tf[0];
} else { ?>
				<input type="text" name="tags" value="<?php echo $this->tags; ?>" />
<?php } ?>
				<span class="hint"><?php echo JText::_('PLG_GROUPS_BLOG_FIELD_TAGS_HINT'); ?></span>
			</label>
			
			<div class="group">
				<label>
					<input type="checkbox" class="option" name="entry[allow_comments]" value="1"<?php if ($this->entry->allow_comments == 1) { echo ' checked="checked"'; } ?> /> 
					<?php echo JText::_('PLG_GROUPS_BLOG_FIELD_ALLOW_COMMENTS'); ?>
				</label>

				<label>
					<?php echo JText::_('PLG_GROUPS_BLOG_FIELD_PRIVACY'); ?>
					<select name="entry[state]">
						<option value="1"<?php if ($this->entry->state == 1) { echo ' selected="selected"'; } ?>><?php echo JText::_('Public (anyone can see)'); ?></option>
						<option value="2"<?php if ($this->entry->state == 2) { echo ' selected="selected"'; } ?>><?php echo JText::_('Registered (any logged-in site member can see)'); ?></option>
						<option value="0"<?php if ($this->entry->state == 0) { echo ' selected="selected"'; } ?>><?php echo JText::_('Private (only group members can see)'); ?></option>
					</select>
				</label>
			</div>
		</fieldset>
		<div class="clear"></div>

		<input type="hidden" name="gid" value="<?php echo $this->group->cn; ?>" />
		<input type="hidden" name="entry[id]" value="<?php echo $this->entry->id; ?>" />
		<input type="hidden" name="entry[alias]" value="<?php echo $this->entry->alias; ?>" />
		<input type="hidden" name="entry[created]" value="<?php echo $this->entry->created; ?>" />
		<input type="hidden" name="entry[created_by]" value="<?php echo $this->entry->created_by; ?>" />
		<input type="hidden" name="entry[scope]" value="group" />
		<input type="hidden" name="entry[group_id]" value="<?php echo $this->group->gidNumber; ?>" />
		<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
		<input type="hidden" name="active" value="blog" />
		<input type="hidden" name="task" value="save" />
		
		<p class="submit">
			<input type="submit" value="<?php echo JText::_('PLG_GROUPS_BLOG_SAVE'); ?>" />
<?php if ($this->entry->id) { ?>
			<a href="<?php echo JRoute::_('index.php?option='.$this->option.'&gid='.$this->group->cn.'&active=blog&scope='.JHTML::_('date',$this->entry->publish_up, '%Y', 0).'/'.JHTML::_('date',$this->entry->publish_up, '%m', 0).'/'.$this->entry->alias); ?>">Cancel</a>
<?php } ?>
		</p>
	</form>
</div><!-- / .section -->