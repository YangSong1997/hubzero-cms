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
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

$canDo = StorefrontHelperPermissions::getActions('product');

$text = ($this->task == 'edit' ? JText::_('COM_STOREFRONT_EDIT') : JText::_('COM_STOREFRONT_NEW'));

JToolBarHelper::title(JText::_('COM_STOREFRONT') . ': ' . JText::_('COM_STOREFRONT_OPTION') . ': ' . $text, 'kb.png');
if ($canDo->get('core.edit'))
{
	JToolBarHelper::apply();
	JToolBarHelper::save();
}
JToolBarHelper::cancel();
JToolBarHelper::spacer();
JToolBarHelper::help('category');

?>
<script type="text/javascript">
function submitbutton(pressbutton)
{
	if (pressbutton == 'cancel') {
		submitform(pressbutton);
		return;
	}

	// do field validation
	if (document.getElementById('field-title').value == ''){
		alert("<?php echo 'Title cannot be empty' ?>");
	} else {
		submitform(pressbutton);
	}
}
</script>

<form action="index.php" method="post" name="adminForm" id="item-form">
	<div class="col width-60 fltlft">
		<fieldset class="adminform">
			<legend><span><?php echo JText::_('COM_STOREFRONT_DETAILS'); ?></span></legend>

			<div class="input-wrap">
				<label for="field-title"><?php echo JText::_('COM_STOREFRONT_TITLE'); ?>: <span class="required"><?php echo JText::_('JOPTION_REQUIRED'); ?></span></label><br />
				<input type="text" name="fields[oName]" id="field-title" size="30" maxlength="100" value="<?php echo $this->escape(stripslashes($this->row->getName())); ?>" />
			</div>

		</fieldset>

		<?php
		if (!empty($this->allOptions))
		{
		?>
		<fieldset class="adminform">
			<legend><span><?php echo 'Product options'; ?></span></legend>

			<?php
			foreach ($this->allOptions as $optionGroup)
			{
			?>
				<div class="input-wrap">
					<label for="field-options-<?php echo $optionGroup->ogId; ?>"><?php echo $optionGroup->ogName; ?>:<span class="required"><?php echo JText::_('JOPTION_REQUIRED'); ?></span></label><br />

					<select name="fields[options][]" id="field-options-<?php echo $optionGroup->ogId; ?>">
						<option value="">-- please select an option --</option>
					<?php
					foreach ($optionGroup->options as $option)
					{
					?>
						<option value="<?php echo $option->oId; ?>"<?php if (in_array($option->oId, $this->options)) { echo ' selected="selected"'; } ?>><?php echo $option->oName ?></option>
					<?php
					}
					?>
					</select>
				</div>
			<?php
			}
			?>
		</fieldset>
		<?php
		}
		?>

	</div>
	<div class="col width-40 fltrt">
		<table class="meta">
			<tbody>
				<tr>
					<th class="key"><?php echo JText::_('COM_STOREFRONT_ID'); ?>:</th>
					<td>
						<?php echo $this->row->getId(); ?>
						<input type="hidden" name="fields[oId]" id="field-oid" value="<?php echo $this->escape($this->row->getId()); ?>" />
					</td>
				</tr>
				<tr>
					<th class="key"><?php echo JText::_('COM_STOREFRONT_OPTION_GROUP'); ?>:</th>
					<td>
						<?php echo $this->ogInfo->ogName; ?>
						<input type="hidden" name="fields[ogId]" id="field-ogid" value="<?php echo $this->escape($this->ogInfo->ogId); ?>" />
					</td>
				</tr>
			</tbody>
		</table>

		<fieldset class="adminform">
			<legend><span><?php echo JText::_('COM_STOREFRONT_PARAMETERS'); ?></span></legend>

			<div class="input-wrap">
				<label for="field-state"><?php echo JText::_('COM_STOREFRONT_PUBLISH'); ?>:</label>
				<select name="fields[state]" id="field-state">
					<option value="0"<?php if ($this->row->getActiveStatus() == 0) { echo ' selected="selected"'; } ?>><?php echo JText::_('JUNPUBLISHED'); ?></option>
					<option value="1"<?php if ($this->row->getActiveStatus() == 1) { echo ' selected="selected"'; } ?>><?php echo JText::_('JPUBLISHED'); ?></option>
					<option value="2"<?php if ($this->row->getActiveStatus() == 2) { echo ' selected="selected"'; } ?>><?php echo JText::_('JTRASHED'); ?></option>
				</select>
			</div>

		</fieldset>

	</div>
	<div class="clr"></div>

	<?php /*
		<?php if ($canDo->get('core.admin')): ?>
			<div class="col width-100 fltlft">
				<fieldset class="panelform">
					<?php echo $this->form->getLabel('rules'); ?>
					<?php echo $this->form->getInput('rules'); ?>
				</fieldset>
			</div>
			<div class="clr"></div>
		<?php endif; ?>
	*/ ?>

	<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
	<input type="hidden" name="controller" value="<?php echo $this->controller; ?>" />
	<input type="hidden" name="task" value="save" />

	<?php echo JHTML::_('form.token'); ?>
</form>