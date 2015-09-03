<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2011 Purdue University. All rights reserved.
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
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

// add styles & scripts
$this->css()
	 ->js()
     ->css('jquery.fancyselect.css', 'system')
     ->js('jquery.fancyselect', 'system')
     ->css('jquery.colpick.css', 'system')
     ->js('jquery.colpick', 'system');

// define base link
$base_link = 'index.php?option=com_groups&cn='.$this->group->get('cn').'&task=pages';

// define return link
$return      = JRequest::getVar('return', '');
$return_link = $base_link;
if ($return != '')
{
	if (filter_var(base64_decode($return), FILTER_VALIDATE_URL))
	{
		$return_link = base64_decode($return);
	}
}

// default group page vars
$id        = $this->page->get('id', '');
$gidNumber = $this->page->get('gidNumber', '');
$category  = $this->page->get('category', '');
$alias     = $this->page->get('alias', '');
$title     = $this->page->get('title', '');
$content   = $this->version->get('content', '');
$version   = $this->version->get('version', 0);
$ordering  = $this->page->get('ordering', null);
$state     = $this->page->get('state', 1);
$privacy   = $this->page->get('privacy', 'default');
$home      = $this->page->get('home', 0);

// default some form vars
$pageHeading = JText::_("COM_GROUPS_PAGES_ADD_PAGE");

// if we are in edit mode
if ($this->page->get('id'))
{
	$pageHeading = JText::sprintf("COM_GROUPS_PAGES_EDIT_PAGE", $title);
}
?>
<header id="content-header">
	<h2><?php echo $pageHeading; ?></h2>

	<div id="content-header-extra">
		<ul id="useroptions">
			<li><a class="icon-prev prev btn" href="<?php echo JRoute::_($base_link); ?>">
				<?php echo JText::_('COM_GROUPS_ACTION_BACK_TO_MANAGE_PAGES'); ?></a></li>
		</ul>
	</div>
</header>

<section class="main section edit-group-page">
	<?php foreach ($this->notifications as $notification) { ?>
		<p class="<?php echo $notification['type']; ?>"><?php echo $notification['message']; ?></p>
	<?php } ?>

	<form action="<?php echo JRoute::_('index.php?option=com_groups&cn='.$this->group->get('cn').'&controller=pages&task=save'); ?>" method="POST" id="hubForm" class="full">

		<div class="grid">
			<div class="col span9">
				<fieldset>
					<legend><?php echo JText::_('COM_GROUPS_PAGES_PAGE_DETAILS'); ?></legend>
					<label for="field-title">
						<strong><?php echo JText::_('COM_GROUPS_PAGES_PAGE_TITLE'); ?>:</strong> <span class="required"><?php echo JText::_('COM_GROUPS_FIELD_REQUIRED'); ?></span>
						<input type="text" name="page[title]" id="field-title" value="<?php echo $this->escape(stripslashes($title)); ?>" />
					</label>
					<label for="field-url">
						<strong><?php echo JText::_('COM_GROUPS_PAGES_PAGE_URL'); ?>:</strong> <span class="optional"><?php echo JText::_('COM_GROUPS_FIELD_OPTIONAL'); ?></span>
						<input type="text" name="page[alias]" id="field-url" value="<?php echo $this->escape($alias); ?>" />
						<span class="hint">Page URL's can only contain lowercase alphanumeric characters and underscores. Spaces will be removed.</span>
					</label>
					<label for="pagecontent">
						<strong><?php echo JText::_('COM_GROUPS_PAGES_PAGE_CONTENT'); ?>:</strong> <span class="required"><?php echo JText::_('COM_GROUPS_FIELD_REQUIRED'); ?></span>
						<?php
							$allowPhp      = true;
							$allowScripts  = true;
							$startupMode   = 'wysiwyg';
							$showSourceBtn = true;

							// only allow super groups to use php & scrips
							// strip out php and scripts if somehow it made it through
							if (!$this->group->isSuperGroup())
							{
								$allowPhp     = false;
								$allowScripts = false;
								$content      = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $content);
								$content      = preg_replace('/<\?[\s\S]*?\?>/', '', $content);
							}

							// open in source mode if contains php or scripts
							if (strstr(stripslashes($content), '<script>') ||
								strstr(stripslashes($content), '<?php'))
							{
								$startupMode  = 'source';
								//$showSourceBtn = false;
							}

							//build config
							$config = array(
								'startupMode'                 => $startupMode,
								'sourceViewButton'            => $showSourceBtn,
								'contentCss'                  => $this->stylesheets,
								//'autoGrowMinHeight'           => 500,
								'height'                      => '500px',
								'fileBrowserWindowWidth'      => 1200,
								'fileBrowserBrowseUrl'        => JRoute::_('index.php?option=com_groups&cn='.$this->group->get('cn').'&controller=media&task=filebrowser&tmpl=component'),
								'fileBrowserImageBrowseUrl'   => JRoute::_('index.php?option=com_groups&cn='.$this->group->get('cn').'&controller=media&task=filebrowser&tmpl=component'),
								'fileBrowserUploadUrl'        => JRoute::_('index.php?option=com_groups&cn='.$this->group->get('cn').'&controller=media&task=ckeditorupload&tmpl=component'),
								'allowPhpTags'                => $allowPhp,
								'allowScriptTags'             => $allowScripts
							);

							// if super group add to templates
							if ($this->group->isSuperGroup())
							{
								$config['templates_replace'] = false;
								$config['templates_files']   = array('pagelayouts' => '/site/groups/' . $this->group->get('gidNumber') . '/template/assets/js/pagelayouts.js');
							}

							// display with ckeditor
							jimport( 'joomla.html.editor' );
							$editor = new JEditor( 'ckeditor' );
							echo $editor->display('pageversion[content]', stripslashes($content), '100%', '400', 0, 0, false, 'pagecontent', null, null, $config);
						?>

					</label>
				</fieldset>
			</div>
			<div class="col span3 omega">
				<fieldset>
					<legend><?php echo JText::_('COM_GROUPS_PAGES_PAGE_PUBLISH'); ?></legend>
					<label>
						<strong><?php echo JText::_('COM_GROUPS_PAGES_PAGE_STATUS'); ?>:</strong> <span class="required"><?php echo JText::_('COM_GROUPS_FIELD_REQUIRED'); ?></span>
						<select name="page[state]" class="fancy-select">
							<option value="1" <?php if ($state == 1) { echo "selected"; } ?>><?php echo JText::_('COM_GROUPS_PAGES_PAGE_STATUS_PUBLISHED'); ?></option>
							<option value="0" <?php if ($state == 0) { echo "selected"; } ?>><?php echo JText::_('COM_GROUPS_PAGES_PAGE_STATUS_UNPUBLISHED'); ?></option>
						</select>
					</label>

					<?php if ($this->page->get('id')) : ?>
						<label>
							<strong><?php echo JText::_('COM_GROUPS_PAGES_PAGE_VERSIONS'); ?>:</strong> <br />
							<a class="btn icon-history" href="<?php echo JRoute::_('index.php?option=com_groups&cn='.$this->group->get('cn').'&controller=pages&task=versions&pageid=' . $this->page->get('id')); ?>">
								<?php echo JText::sprintf('COM_GROUPS_PAGES_PAGE_VERSIONS_BROWSE', $this->page->versions()->count()); ?>
							</a>
						</label>
					<?php endif; ?>

					<label>
						<strong><?php echo JText::_('COM_GROUPS_PAGES_PAGE_PRIVACY'); ?>:</strong> <span class="required"><?php echo JText::_('COM_GROUPS_FIELD_REQUIRED'); ?></span>
						<?php
							$access = \Hubzero\User\Group\Helper::getPluginAccess($this->group, 'overview');
							switch ($access)
							{
								case 'anyone':		$name = JText::_('COM_GROUPS_PLUGIN_ANYONE');		break;
								case 'registered':	$name = JText::_('COM_GROUPS_PLUGIN_REGISTERED');	break;
								case 'members':		$name = JText::_('COM_GROUPS_PLUGIN_MEMBERS');	    break;
							}
						?>
						<select name="page[privacy]" class="fancy-select">
							<option value="default" <?php if ($privacy == "default") { echo 'selected="selected"'; } ?>><?php echo JText::sprintf('COM_GROUPS_PAGES_PAGE_PRIVACY_INHERIT', $name); ?></option>
							<option value="members" <?php if ($privacy == "members") { echo 'selected="selected"'; } ?>><?php echo JText::_('COM_GROUPS_PAGES_PAGE_PRIVACY_PRIVATE'); ?></option>
						</select>
					</label>
				</fieldset>

				<div class="form-controls cf">
					<a href="<?php echo JRoute::_($return_link); ?>" class="cancel"><?php echo JText::_('COM_GROUPS_PAGES_CANCEL'); ?></a>
					<button type="submit" class="btn btn-info opposite save icon-save"><?php echo JText::_('COM_GROUPS_PAGES_SAVE_PAGE'); ?></button>
				</div>

				<fieldset>
					<legend><?php echo JText::_('COM_GROUPS_PAGES_PAGE_SETTINGS'); ?></legend>
					<?php if ($this->page->get('id')) : ?>
						<label for="page-ordering">
							<strong><?php echo JText::_('COM_GROUPS_PAGES_PAGE_ORDER'); ?>:</strong> <span class="optional"><?php echo JText::_('COM_GROUPS_FIELD_OPTIONAL'); ?></span>
							<select name="page[ordering]" class="fancy-select">
								<?php foreach ($this->order as $order) : ?>
									<?php $sel = ($order->get('title') == $title) ? 'selected="selected"' : ''; ?>
									<option <?php echo $sel; ?> value="<?php echo $order->get('ordering'); ?>">
										<?php echo ($order->get('ordering') + 0) . '. '; ?><?php echo $order->get('title'); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</label>
					<?php endif; ?>

					<label for="page-category" class="page-category-label">
						<strong><?php echo JText::_('COM_GROUPS_PAGES_PAGE_CATEGORY'); ?>:</strong> <span class="optional"><?php echo JText::_('COM_GROUPS_FIELD_OPTIONAL'); ?></span>
						<select name="page[category]" class="page-category" data-url="<?php echo JRoute::_('index.php?option=com_groups&cn='. $this->group->get('gidNumber').'&controller=categories&task=add&no_html=1'); ?>">
							<option value=""><?php echo JText::_('COM_GROUPS_PAGES_PAGE_CATEGORY_OPTION_NULL'); ?></option>
							<?php foreach ($this->categories as $pageCategory) : ?>
								<?php $sel = ($category == $pageCategory->get('id')) ? 'selected="selected"' : ''; ?>
								<option <?php echo $sel; ?> data-color="#<?php echo $pageCategory->get('color'); ?>" value="<?php echo $pageCategory->get('id'); ?>"><?php echo $pageCategory->get('title'); ?></option>
							<?php endforeach; ?>
							<option value="other"><?php echo JText::_('COM_GROUPS_PAGES_PAGE_CATEGORY_OPTION_OTHER'); ?></a>
						</select>
						<span class="hint"><?php echo JText::_('COM_GROUPS_PAGES_PAGE_CATEGORY_HINT'); ?></span>
					</label>

					<?php if ($this->group->isSuperGroup() && count($this->pageTemplates) > 0) : ?>
						<label for="page-template">
							<strong><?php echo JText::_('COM_GROUPS_PAGES_PAGE_TEMPLATE'); ?>:</strong> <span class="optional"><?php echo JText::_('COM_GROUPS_FIELD_OPTIONAL'); ?></span>
							<select name="page[template]" class="fancy-select">
								<option value=""><?php echo JText::_('COM_GROUPS_PAGES_PAGE_TEMPLATE_OPTION_NULL'); ?></option>
								<?php foreach ($this->pageTemplates as $name => $file) : ?>
									<?php
										$tmpl = str_replace('.php', '', $file);
										$sel  = ($this->page->get('template') == $tmpl) ? 'selected="selected"' : ''; ?>
									<option <?php echo $sel; ?> value="<?php echo $tmpl; ?>"><?php echo $name; ?></option>
								<?php endforeach;?>
							</select>
						</label>
					<?php endif; ?>

					<label>
						<strong><?php echo JText::_('COM_GROUPS_PAGES_PAGE_HOME'); ?>:</strong> <span class="optional"><?php echo JText::_('COM_GROUPS_FIELD_OPTIONAL'); ?></span>
						<select name="page[home]" class="fancy-select">
							<option value="0" <?php if ($home == 0) { echo "selected"; } ?>><?php echo JText::_('COM_GROUPS_PAGES_PAGE_HOME_USE_CURRENT'); ?></option>
							<option value="1" <?php if ($home == 1) { echo "selected"; } ?>><?php echo JText::_('COM_GROUPS_PAGES_PAGE_HOME_SET'); ?></option>
						</select>
						<span class="hint"><?php echo JText::_('COM_GROUPS_PAGES_PAGE_HOME_HINT'); ?></span>
					</label>
				</fieldset>

			</div>
		</div>

		<input type="hidden" name="page[id]" value="<?php echo $id; ?>" />
		<input type="hidden" name="option" value="com_groups" />
		<input type="hidden" name="controller" value="pages" />
		<input type="hidden" name="return" value="<?php echo JRequest::getVar('return', '','get'); ?>" />
		<input type="hidden" name="task" value="save" />
	</form>
</section>