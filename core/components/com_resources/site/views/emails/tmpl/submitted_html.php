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
 * @author    Shawn Rice <zooley@purdue.edu>
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

// No direct access.
defined('_HZEXEC_') or die();

$db = \App::get('db');

$creator = User::getInstance($this->resource->created_by);

$type = new \Components\Resources\Tables\Type($db);
$type->load($this->resource->type);

$link = rtrim(Request::base(), '/') . '/' . ltrim(Route::url('index.php?option=com_resources&id=' . $this->resource->id), '/');
?>
	<!-- Start Header -->
	<table class="tbl-header" width="100%" cellpadding="0" cellspacing="0" border="0">
		<tbody>
			<tr>
				<td width="10%" align="left" valign="bottom" nowrap="nowrap" class="sitename">
					<?php echo Config::get('sitename'); ?>
				</td>
				<td width="80%" align="left" valign="bottom" class="tagline mobilehide">
					<span class="home">
						<a href="<?php echo Request::base(); ?>"><?php echo Request::base(); ?></a>
					</span>
					<br />
					<span class="description"><?php echo Config::get('MetaDesc'); ?></span>
				</td>
				<td width="10%" align="right" valign="bottom" nowrap="nowrap" class="component">
					<?php echo Lang::txt('COM_RESOURCES'); ?>
				</td>
			</tr>
		</tbody>
	</table>
	<!-- End Header -->

	<!-- Start Spacer -->
	<table class="tbl-spacer" width="100%" cellpadding="0" cellspacing="0" border="0">
		<tbody>
			<tr>
				<td height="30"></td>
			</tr>
		</tbody>
	</table>
	<!-- End Spacer -->

	<!-- Start Message -->
	<table width="100%" cellpadding="0" cellspacing="0" border="0">
		<tbody>
			<tr>
				<td align="left" valign="bottom" style="border-collapse: collapse; color: #666; line-height: 1; padding: 5px; text-align: center;">
					A resource has been submitted by <?php echo $this->escape(stripslashes($creator->get('name'))); ?>.
				</td>
			</tr>
		</tbody>
	</table>
	<!-- End Message -->

	<!-- Start Spacer -->
	<table class="tbl-spacer" width="100%" cellpadding="0" cellspacing="0" border="0">
		<tbody>
			<tr>
				<td height="30"></td>
			</tr>
		</tbody>
	</table>
	<!-- End Spacer -->

	<table id="resource-info" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse; line-height: 1.6em;">
		<tbody>
			<tr>
				<td class="mobilehide" style="font-size: 2.5em; font-weight: bold; text-align: center; padding: 0 30px 8px 0; vertical-align: top;" align="center" valing="top">
					<p style="display: block; border: 1px solid #c8e3c2; background: #eafbe6; margin:0; padding: 1em;"><?php echo Lang::txt('COM_RESOURCES_NEW'); ?></p>
				</td>
				<td width="100%" style="padding: 18px 8px 8px 8px; border-top: 2px solid #e9e9e9;">
					<table width="100%" style="border-collapse: collapse; font-size: 0.9em;" cellpadding="0" cellspacing="0" border="0">
						<tbody>
							<tr>
								<th style="text-align: right; padding: 0 0.5em; font-weight: bold; white-space: nowrap;" align="right"><?php echo Lang::txt('COM_RESOURCES_ID'); ?>:</th>
								<td style="text-align: left; padding: 0 0.5em;" width="100%" align="left"># <?php echo $this->resource->id; ?></td>
							</tr>
							<tr>
								<th style="text-align: right; padding: 0 0.5em; font-weight: bold; white-space: nowrap;" align="right"><?php echo Lang::txt('COM_RESOURCES_CREATED'); ?>:</th>
								<td style="text-align: left; padding: 0 0.5em;" width="100%" align="left">@ <?php echo $this->resource->created; ?></td>
							</tr>
							<tr>
								<th style="text-align: right; padding: 0 0.5em; font-weight: bold; white-space: nowrap;" align="right"><?php echo Lang::txt('COM_RESOURCES_CREATOR'); ?>:</th>
								<td style="text-align: left; padding: 0 0.5em;" width="100%" align="left"><?php echo $this->escape(stripslashes($creator->get('name'))); ?></td>
							</tr>
							<tr>
								<th style="text-align: right; padding: 0 0.5em; font-weight: bold; white-space: nowrap;" align="right"><?php echo Lang::txt('COM_RESOURCES_TYPE'); ?>:</th>
								<td style="text-align: left; padding: 0 0.5em;" width="100%" align="left"><?php echo $this->escape($type->type); ?></td>
							</tr>
							<tr>
								<th style="text-align: right; padding: 0 0.5em; font-weight: bold; white-space: nowrap;" align="right"><?php echo Lang::txt('COM_RESOURCES_LINK'); ?>:</th>
								<td style="text-align: left; padding: 0 0.5em;" width="100%" align="left"><a href="<?php echo $link; ?>"><?php echo $link; ?></a></td>
							</tr>
						</tbody>
					</table>

					<table width="100%" style="margin: 18px 0 0 0; border-top: 2px solid #e9e9e9; border-collapse: collapse; font-size: 1em;">
						<tbody>
							<tr>
								<td style="text-align: left; padding: 0 0.5em;" cellpadding="0" cellspacing="0" border="0">
									<div style="line-height: 1.6em; margin: 1em 0; padding: 0; text-align: left;"><b><?php echo $this->resource->title; ?></b></div>
								</td>
							</tr>
							<tr>
								<td style="text-align: left; padding: 0 0.5em;" cellpadding="0" cellspacing="0" border="0">
									<div style="line-height: 1.6em; margin: 1em 0; padding: 0; text-align: left;"><?php echo $this->resource->introtext; ?></div>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</tbody>
	</table>

	<!-- Start Spacer -->
	<table class="tbl-spacer" width="100%" cellpadding="0" cellspacing="0" border="0">
		<tbody>
			<tr>
				<td height="30"></td>
			</tr>
		</tbody>
	</table>
	<!-- End Spacer -->

	<!-- Start Footer -->
	<table class="tbl-footer" width="100%" cellpadding="0" cellspacing="0" border="0">
		<tbody>
			<tr>
				<td align="left" valign="bottom">
					<span><?php echo Config::get('sitename'); ?> sent this email because you were added to the list of recipients. Visit our <a href="<?php echo Request::base(); ?>/legal/privacy">Privacy Policy</a> and <a href="<?php echo Request::base(); ?>/support">Support Center</a> if you have any questions.</span>
				</td>
			</tr>
		</tbody>
	</table>
	<!-- End Footer -->