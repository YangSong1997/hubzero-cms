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
defined('_JEXEC') or die('Restricted access');

$jconfig = JFactory::getConfig();
$juser = JFactory::getUser();

$page = WikiPage::getInstance(JRequest::getVar('page'));
$revision = $page->getRevision(JRequest::getInt('version', 0));

$yFormat = '%Y';
$apaFormat = '%Y, %B %d';
$apaFormatRetrieved = '%I:%M, %b %d, %Y';
$mlaFormat = '%d %b. %Y';
$mlaFormatRetrieved = '%d %b. %Y';
$mhraFormat = '%d %B %Y';
$mhraFormatRetrieved = '%d %B %Y %I:%M';
$cbeFormat = '%Y %b %d';
$bluebookFormat = '%b. %d, %Y';
$amaFormat = '%B %d, %Y, %H:%I';
$amaFormatRetrieved = '%B %d, %Y';
$tz = 0;
if (version_compare(JVERSION, '1.6', 'ge'))
{
	$yFormat = 'Y';
	$apaFormat = 'Y, M d';
	$apaFormatRetrieved = 'H:i, M d, Y';
	$mlaFormat = 'd M. Y';
	$mlaFormatRetrieved = 'd M. Y';
	$mhraFormat = 'd M Y H:i';
	$mhraFormatRetrieved = 'd M Y';
	$cbeFormat = 'Y M d';
	$bluebookFormat = 'M. d, Y';
	$amaFormat = 'M d, Y, H:i';
	$amaFormatRetrieved = 'M d, Y';
	$tz = true;
}

$now = JFactory::getDate();

$juri =& JURI::getInstance();
$permalink = rtrim($juri->base(), DS) . DS . ltrim(JRoute::_('index.php?option='.$this->option.'&scope='.$page->scope.'&pagename=' . $page->pagename . '&version=' . $revision->version), DS);
?>
<div class="admon-note">
<p>
	<strong>IMPORTANT NOTE</strong>: Most educators and professionals do not consider it appropriate to use tertiary sources such as encyclopedias as a sole source for any information—citing an encyclopedia as an important reference in footnotes or bibliographies may result in censure or a failing grade. Wiki articles should be used for background information, as a reference for correct terminology and search terms, and as a starting point for further research.
</p>
<p>
	As with any community-built reference, there is a possibility for error in content&ndash;please check your facts against multiple sources and read our disclaimers for more information.
</p>
</div>
<div class="wiki-box highlight-box">
	<h3>Bibliographic details for "<?php echo $this->escape(stripslashes($page->title)); ?>"</h3>
	<ul>
		<li>
			Page name: <?php echo $this->escape(stripslashes($page->pagename)); ?>
		</li>
		<li>
			Author: <?php echo $this->escape($jconfig->getValue('config.sitename')); ?> contributors
		</li>
		<li>
			Publisher: <i><?php echo $this->escape($jconfig->getValue('config.sitename')); ?></i>
		</li>
		<li>
			Date of last revision: <?php echo $this->escape(stripslashes($revision->created)); ?>
		</li>
		<li>
			Date retrieved: <?php echo $now; ?>
		</li>
		<li>
			Permanent link: <a href="<?php echo $permalink; ?>"><?php echo $permalink; ?></a>
		</li>
		<li>
			Primary contributors: <a href="<?php echo JRoute::_('index.php?option='.$this->option.'&scope='.$page->scope.'&pagename=' . $page->pagename . '&task=history'); ?>">Revision history</a>
		</li>
		<li>
			Page version ID: <?php echo $this->escape($revision->id); ?>
		</li>
	</ul>
	<p>
		Please remember to check your manual of style, standards guide or instructor's guidelines for the exact syntax to suit your needs.
	</p>
</div>

<div class="wiki-box">
	<h3>Citation styles for "<?php echo $this->escape(stripslashes($page->title)); ?>"</h3>
	
	<h4>APA style</h4>
	<p>
		<?php echo $this->escape(stripslashes($page->title)); ?>. (<?php echo JHTML::_('date', $revision->created, $apaFormat, $tz); ?>). In <i><?php echo $this->escape($jconfig->getValue('config.sitename')); ?></i>. Retrieved <?php echo JHTML::_('date', $now, $apaFormatRetrieved, $tz); ?>, from <a href="<?php echo $permalink; ?>"><?php echo $permalink; ?></a>
	</p>
	
	<h4>MLA style</h4>
	<p>
		<?php echo $this->escape($jconfig->getValue('config.sitename')); ?> contributors. "<?php echo $this->escape(stripslashes($page->title)); ?>." <i><?php echo $this->escape($jconfig->getValue('config.sitename')); ?></i>. <?php echo $this->escape($jconfig->getValue('config.sitename')); ?>, <?php echo JHTML::_('date', $revision->created, $apaFormat, $tz); ?>. Web. <?php echo JHTML::_('date', $now, $mlaFormatRetrieved, $tz); ?>
	</p>
	
	<h4>MHRA style</h4>
	<p>
		<?php echo $this->escape($jconfig->getValue('config.sitename')); ?> contributors, '<?php echo $this->escape(stripslashes($page->title)); ?>,' <i><?php echo $this->escape($jconfig->getValue('config.sitename')); ?></i>, <?php echo JHTML::_('date', $revision->created, $mhraFormat, $tz); ?>, &lt;<a href="<?php echo $permalink; ?>"><?php echo $permalink; ?></a>&gt; [accessed <?php echo JHTML::_('date', $now, $mhraFormatRetrieved, $tz); ?>]
	</p>
	
	<h4>Chicago style</h4>
	<p>
		<?php echo $this->escape($jconfig->getValue('config.sitename')); ?> contributors, "<?php echo $this->escape(stripslashes($page->title)); ?>," <i><?php echo $this->escape($jconfig->getValue('config.sitename')); ?></i>, <a href="<?php echo $permalink; ?>"><?php echo $permalink; ?></a> (accessed <?php echo JHTML::_('date', $now, $mhraFormatRetrieved, $tz); ?>).
	</p>
	
	<h4>CBE/CSE style</h4>
	<p>
		<?php echo $this->escape($jconfig->getValue('config.sitename')); ?> contributors. <?php echo $this->escape(stripslashes($page->title)); ?> [Internet]. <?php echo $this->escape($jconfig->getValue('config.sitename')); ?>; <?php echo JHTML::_('date', $revision->created, $bluebookFormat, $tz); ?> [cited <?php echo JHTML::_('date', $now, $cbeFormat, $tz); ?>]. Available from: <a href="<?php echo $permalink; ?>"><?php echo $permalink; ?></a>.
	</p>
	
	<h4>Bluebook style</h4>
	<p>
		<?php echo $this->escape(stripslashes($page->title)); ?>, <a href="<?php echo $permalink; ?>"><?php echo $permalink; ?></a> (last visited <?php echo JHTML::_('date', $now, $bluebookFormat, $tz); ?>).
	</p>
	
	<h4>Bluebook: Harvard JOLT style</h4>
	<p>
		<?php echo $this->escape($jconfig->getValue('config.sitename')); ?>, <i><?php echo $this->escape(stripslashes($page->title)); ?></i>, <a href="<?php echo $permalink; ?>"><?php echo $permalink; ?></a> (optional description here) (as of <?php echo JHTML::_('date', $now, $bluebookFormat, $tz); ?>).
	</p>
	
	<h4>AMA style</h4>
	<p>
		<?php echo $this->escape($jconfig->getValue('config.sitename')); ?> contributors. <?php echo $this->escape(stripslashes($page->title)); ?>. <?php echo $this->escape($jconfig->getValue('config.sitename')); ?>. <?php echo JHTML::_('date', $revision->created, $amaFormat, $tz); ?>. Available at <a href="<?php echo $permalink; ?>"><?php echo $permalink; ?></a>. Accessed <?php echo JHTML::_('date', $now, $amaFormatRetrieved, $tz); ?>.
	</p>
	
	<h4>BibTeX entry</h4>
<pre>
@misc{ wiki:xxx,
    author = "<?php echo $this->escape($jconfig->getValue('config.sitename')); ?>",
    title = "<?php echo $this->escape(stripslashes($page->title)); ?> --- <?php echo $this->escape($jconfig->getValue('config.sitename')); ?>",
    year = "<?php echo JHTML::_('date', $revision->created, $yFormat, $tz); ?>",
    url = "<?php echo $permalink; ?>",
    note = "[Online; accessed 1-October-2012]"
}
</pre>