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
 * @author    Sam Wilson <samwilson@purdue.edu>
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

$this->course->offering()->gradebook()->refresh($this->member->get('id'));
$grades   = $this->course->offering()->gradebook()->grades(null, $this->member->get('id'));
$progress = $this->course->offering()->gradebook()->progress($this->member->get('id'));
$passing  = $this->course->offering()->gradebook()->passing(true, $this->member->get('id'));
$passing  = (isset($passing[$this->member->get('id')])) ? $passing[$this->member->get('id')] : null;

// See if the student has qualified for the badge
$this->course->offering()->gradebook()->hasEarnedBadge($this->member->get('id'));
$student = $this->member;

$gradePolicy = new CoursesModelGradePolicies($this->course->offering()->section()->get('grade_policy_id'), $this->course->offering()->section()->get('id'));

$details = array();
$details['quizzes_total']       = 0;
$details['homeworks_total']     = 0;
$details['exams_total']         = 0;
$details['quizzes_taken']       = 0;
$details['homeworks_submitted'] = 0;
$details['exams_taken']         = 0;
$details['forms']               = array();

// Get the assets
$asset  = new CoursesTableAsset(JFactory::getDBO());
$assets = $asset->find(
	array(
		'w' => array(
			'course_id'   => $this->course->get('id'),
			'section_id'  => $this->course->offering()->section()->get('id'),
			'offering_id' => $this->course->offering()->get('id'),
			'graded'      => true,
			'state'       => 1,
			'asset_scope' => 'asset_group'
		)
	)
);

// Get gradebook auxiliary assets
$auxiliary = $asset->findByScope(
	'offering',
	$this->course->offering()->get('id'),
	array(
		'asset_type'    => 'gradebook',
		'asset_subtype' => 'auxiliary',
		'graded'        => true,
		'state'         => 1
	)
);

$assets = array_merge($assets, $auxiliary);

foreach ($assets as $asset)
{
	$increment_count_taken = false;
	$crumb                 = false;
	$isValidForm           = true;

	// Check for result for given student on form
	$crumb = $asset->url;
	$title = $asset->title;
	$url   = JRoute::_($this->base . '&asset=' . $asset->id);
	$unit  = (isset($asset->unit_id)) ? $this->course->offering()->unit($asset->unit_id) : null;

	if (!$crumb || strlen($crumb) != 20)
	{
		$score = (isset($grades[$this->member->get('id')]['assets'][$asset->id]['score']))
			? $grades[$this->member->get('id')]['assets'][$asset->id]['score']
			: '--';

		if (is_numeric($score))
		{
			$increment_count_taken = true;
		}
		else
		{
			$score = '--';
		}

		// Get the date the grade was entered
		if (isset($grades[$this->member->get('id')]['assets'][$asset->id]) && !is_null($grades[$this->member->get('id')]['assets'][$asset->id]['date']))
		{
			$date = JHTML::_('date', $grades[$this->member->get('id')]['assets'][$asset->id]['date'], 'r');
		}
		else
		{
			$date = "N/A";
		}

		if (isset($asset->unit_id) && $asset->unit_id)
		{
			$details['forms'][$unit->get('id')][] = array('title'=>$title, 'score'=>$score, 'date'=>$date, 'url'=>$url);
		}
		else
		{
			$details['aux'][] = array('title'=>$asset->title, 'score'=>$score, 'date'=>$date);
		}

		$isValidForm = false;
	}

	if ($isValidForm)
	{
		$dep = PdfFormDeployment::fromCrumb($crumb, $this->course->offering()->section()->get('id'));

		switch ($dep->getState())
		{
			// Form isn't available yet
			case 'pending':
				$details['forms'][$unit->get('id')][] = array('title'=>$title, 'score'=>'Not yet open', 'date'=>'N/A', 'url'=>$url);
			break;

			// Form availability has expired
			case 'expired':
				// Get whether or not we should show scores at this point
				$results_closed = $dep->getResultsClosed();

				// Form is still active and they are allowed to see their score
				if ($results_closed == 'score' || $results_closed == 'details')
				{
					$score = $grades[$this->member->get('id')]['assets'][$asset->id]['score'];
				}
				else
				{
					// Score has been withheld by form creator
					$score = 'Withheld';
				}

				// Get the date of the completion
				if (!is_null($grades[$this->member->get('id')]['assets'][$asset->id]['date']))
				{
					$date = JHTML::_('date', $grades[$this->member->get('id')]['assets'][$asset->id]['date'], 'r');
				}
				else
				{
					$date = "N/A";
				}

				// They have completed this form, therefore set increment_count_taken equal to true
				$increment_count_taken = true;

				$details['forms'][$unit->get('id')][] = array('title'=>$title, 'score'=>$score, 'date'=>$date, 'url'=>$url);
			break;

			// Form is still active
			case 'active':
				// Get the date of the completion
				if (!is_null($grades[$this->member->get('id')]['assets'][$asset->id]['date']))
				{
					// Get whether or not we should show scores at this point
					$results_open = $dep->getResultsOpen();

					// Form is still active and they are allowed to see their score
					if ($results_open == 'score' || $results_open == 'details')
					{
						$score = $grades[$this->member->get('id')]['assets'][$asset->id]['score'];
					}
					else
					{
						// Score is not yet available at this point
						$score = 'Not yet available';
					}

					// Get the date of the completion
					$date = JHTML::_('date', $grades[$this->member->get('id')]['assets'][$asset->id]['date'], 'r');

					// They have completed this form, therefor set increment_count_taken equal to true
					$increment_count_taken = true;
				}
				// Form is active and they haven't finished it yet!
				else
				{
					$score = 'Not taken';
					$date  = 'N/A';

					// For sanities sake - they have NOT completed the form yet!
					$increment_count_taken = false;

					// If there's an override in the gradebook, go ahead and use that, whether or not they've even taken the form yet
					if ($grades[$this->member->get('id')]['assets'][$asset->id]['override']
						&& !is_null($grades[$this->member->get('id')]['assets'][$asset->id]['score']))
					{
						$score = $grades[$this->member->get('id')]['assets'][$asset->id]['score'];
						$increment_count_taken = true;
					}
				}

				$details['forms'][$unit->get('id')][] = array('title'=>$title, 'score'=>$score, 'date'=>$date, 'url'=>$url);
			break;
		}
	}

	// Increment total count for this type
	if ($asset->grade_weight == 'quiz')
	{
		++$details['quizzes_total'];

		// If increment is set (i.e. they completed the from), increment the taken number as well
		if ($increment_count_taken)
		{
			++$details['quizzes_taken'];
		}
	}
	elseif ($asset->grade_weight == 'homework')
	{
		++$details['homeworks_total'];

		// If increment is set (i.e. they completed the from), increment the taken number as well
		if ($increment_count_taken)
		{
			++$details['homeworks_submitted'];
		}
	}
	elseif ($asset->grade_weight == 'exam')
	{
		++$details['exams_total'];

		// If increment is set (i.e. they completed the from), increment the taken number as well
		if ($increment_count_taken)
		{
			++$details['exams_taken'];
		}
	}
}

// Get the status of the course (e.x. not started, in progress, completed, etc...)
$section = $this->course->offering()->section();
if (!$section->isAvailable() && !$section->ended())
{
	$h3 = JText::_('Course begins ') . date('M jS, Y', strtotime($section->get('start_date')));
}
elseif ($section->isAvailable())
{
	$h3 = JText::_('Course currently in progress');
}
else
{
	$h3 = JText::_('Course ended ') . date('M jS, Y', strtotime($section->get('end_date')));
}

// Get the number of units in the course and figure out which is the current one
$units     = $this->course->offering()->units();
$num_units = $units->total();
$index     = 1;
$current_i = 0;
$finished  = $this->course->offering()->gradebook()->isEligibleForRecognition($this->member->get('id')) ? ' finished' : '';

// Build the progress timeline bar
$progress_timeline  = "<div class=\"progress-timeline length_{$num_units}\">";
$progress_timeline .= '<div class="start"><div class="person"></div><div class="start-inner"></div></div>';
if (count($units) > 0)
{
	foreach ($units as $unit)
	{
		$first    = ($index == 1) ? ' first' : '';
		$last     = ($index == $num_units) ? ' last' : '';
		$complete = isset($progress[$this->member->get('id')][$unit->get('id')]['percentage_complete'])
					? $progress[$this->member->get('id')][$unit->get('id')]['percentage_complete']
					: 0;
		$past     = ((!is_null($unit->get('publish_up')) && $unit->get('publish_up') != '0000-00-00 00:00:00' && $unit->started()) || $complete > 0) ? ' past' : '';
		$margin   = 100 - $complete;
		$done     = ($complete == 100) ? ' complete' : '';
		$current  = '';

		if ((!is_null($unit->get('publish_up')) && $unit->get('publish_up') != '0000-00-00 00:00:00' && $unit->isAvailable()) || $complete > 0)
		{
			$current   = ' current';
			// Set the index for the currently available unit (this will result in the latter of the available units if multiple are available)
			$current_i = $index;
		}

		$progress_timeline .= "<div class=\"unit unit_{$index}{$current}\">";
		$progress_timeline .= "<div class=\"person\"></div>";
		$progress_timeline .= "<div class=\"unit-inner{$first}{$last}{$past}\">";
		$progress_timeline .= "<div class=\"unit-fill\">";
		$progress_timeline .= "<div class=\"unit-fill-inner{$done}\" style=\"height:{$complete}%;margin-top:{$margin}%;\"></div>";
		$progress_timeline .= "</div>";
		$progress_timeline .= "Unit {$index}";
		$progress_timeline .= "</div></div>";

		++$index;
	}
}
else
{
	$progress_timeline .= "<div class=\"unit unit-empty\"><div class=\"unit-empty-inner\"></div></div>";
}
$progress_timeline .= '<div class="end'.$finished.'"><div class="person"></div><div class="end-inner"></div></div>';
$progress_timeline .= '</div>';

?>

<div class="progress">
	<?php if ($this->course->access('manage')) : ?>
		<div class="extra">
			<a href="<?php echo JRoute::_($this->base . '&active=progress') ?>" class="back btn icon-back"><?php echo JText::_('Back to all students') ?></a>
		</div>
	<?php endif; ?>

	<h3>
		<?php echo (JRequest::getInt('id', false)) ? JFactory::getUser($this->member->get('user_id'))->get('name') . ':' : '' ?>
		<?php echo $h3 ?>
	</h3>
	<h4><?php echo JText::sprintf('Unit %d of %d', $current_i, $num_units) ?></h4>

	<?php echo $progress_timeline ?>

	<div class="clear"></div>

<?php if ($this->course->offering()->section()->badge()->isAvailable() && $student->badge()->hasEarned()) : ?>
	<div class="recognition badge earned">
		<img src="<?php echo $this->course->offering()->section()->badge()->get('img_url') ?>" width="125" />
		<?php if ($student->badge()->get('action') == 'accept') : ?>
			<h3>Congratulations! You've got the badge!</h3>
			<p>
				Thanks for working hard and claiming your badge. We hope you have the chance to earn another one soon!
			</p>
			<p>
				<a target="_blank" class="claim-item" href="<?php echo $this->course->offering()->section()->badge()->getBadgesUrl() ?>">View your badges!</a>
			</p>
		<?php elseif ($student->badge()->get('action') == 'deny') : ?>
			<h3>Congratulations! You earned the badge!</h3>
			<p>
				You chose to deny the badge. That's not a problem. If you change your mind, you can always go back and claim it!
			</p>
			<p>
				<a target="_blank" class="claim-item" href="<?php echo $this->course->offering()->section()->badge()->getDeniedUrl() ?>">View denied badges</a>
			</p>
		<?php else : ?>
			<h3>Congratulations! You've earned the badge...and you deserve it!</h3>
			<p>
				You've completed all of the requirements of <?php echo $this->course->get('title') ?>, qualifying you to receive
				a special badge.
			</p>
			<?php if ($this->course->offering()->section()->badge()->getClaimUrl()) : ?>
				<p>
					<a target="_blank" class="claim-item" href="<?php echo $this->course->offering()->section()->badge()->getClaimUrl() ?>">Claim your badge!</a>
				</p>
			<?php else : ?>
				<p>
					Watch your email in the next few days for details on how to claim your badge.
				</p>
			<?php endif; ?>
		<?php endif; ?>
	</div>
<?php endif; ?>

<?php if ($this->course->certificate()->exists()  // The course has a certificate
		&& $this->course->offering()->section()->params('certificate')  // The section is allowing certificates
		&& $this->course->offering()->gradebook()->isEligibleForRecognition($this->member->get('id'))) :  // The user is eligible for a certificate ?>
	<div class="recognition certificate earned">
		<h3>Congratulations!</h3>
		<p>
			You've completed all of the requirements of <?php echo $this->escape(stripslashes($this->course->get('title'))); ?>, qualifying you to receive
			a certificate of completion.
		</p>
		<p>
			<a class="claim-item" href="<?php echo JRoute::_($this->course->offering()->link() . '&controller=certificate'); ?>">
				<?php echo JText::_('Download your certificate!'); ?>
			</a>
		</p>
	</div>
<?php endif; ?>

	<div class="grades">
		<div class="current-score">
			<div class="current-score-inner">
				<p class="grading-policy"><?php echo JText::_('grading policy') ?></p>
				<p class="title"><?php echo JText::_('Your current score') ?></p>
				<?php
					$cls = '';
					if ($passing === 1)
					{
						$cls = ' passing';
					}
					elseif ($passing === 0)
					{
						$cls = ' failing';
					}
				?>
				<p class="score<?php echo $cls ?>">
					<?php
						echo (isset($grades[$this->member->get('id')]['course'][$this->course->get('id')]))
							? $grades[$this->member->get('id')]['course'][$this->course->get('id')] . '%'
							: '--'
					?>
				</p>
			</div>
		</div>

		<div class="quizzes">
			<div class="quizzes-inner">
				<p class="title"><?php echo JText::_('Quizzes taken') ?></p>
				<p class="score"><?php echo $details['quizzes_taken'] ?></p>
				<p><?php echo JText::sprintf('out of %d', $details['quizzes_total']) ?></p>
			</div>
		</div>

		<div class="homeworks">
			<div class="homeworks-inner">
				<p class="title"><?php echo JText::_('Homeworks submitted') ?></p>
				<p class="score"><?php echo $details['homeworks_submitted'] ?></p>
				<p><?php echo JText::sprintf('out of %d', $details['homeworks_total']) ?></p>
			</div>
		</div>

		<div class="exams">
			<div class="exams-inner">
				<p class="title"><?php echo JText::_('Exams taken') ?></p>
				<p class="score"><?php echo $details['exams_taken'] ?></p>
				<p><?php echo JText::sprintf('out of %d', $details['exams_total']) ?></p>
			</div>
		</div>
	</div>

	<div class="clear"></div>

	<p class="info grading-policy-explanation">
		<?php echo $gradePolicy->get('description') ?>
	</p>

	<div class="units">
	<?php foreach ($this->course->offering()->units() as $unit) : ?>

		<div class="unit-entry">
			<div class="unit-overview">
				<div class="unit-title"><?php echo $unit->get('title') ?></div>
				<div class="unit-score">
					<?php
						echo (isset($grades[$this->member->get('id')]['units'][$unit->get('id')]))
							? $grades[$this->member->get('id')]['units'][$unit->get('id')] . '%'
							: '--'
					?>
				</div>
			</div>
			<div class="unit-details">
				<table>
					<thead>
						<tr>
							<td class="grade-details-title"><?php echo JText::_('Assignment') ?></td>
							<td class="grade-details-score"><?php echo JText::_('Score') ?></td>
							<td class="grade-details-date"><?php echo JText::_('Date taken') ?></td>
						</tr>
					</thead>
					<tbody>
						<?php if (isset($details['forms'][$unit->get('id')])) :
								usort($details['forms'][$unit->get('id')], function ($a, $b) {
									return strcmp($a['title'], $b['title']);
								});
						?>
							<?php foreach ($details['forms'][$unit->get('id')] as $form) : ?>
								<?php
									if (is_numeric($form['score']) && $form['score'] < 60)
									{
										$class = 'stop';
									}
									elseif (is_numeric($form['score']) && $form['score'] >= 60 && $form['score'] < 70)
									{
										$class = 'yield';
									}
									elseif (is_numeric($form['score']) && $form['score'] >= 70)
									{
										$class = 'go';
									}
									else
									{
										$class = 'neutral';
									}
								?>
								<tr class="<?php echo $class ?>">
									<td class="grade-details-title"><a href="<?php echo $form['url'] ?>"><?php echo $form['title'] ?></a></td>
									<td class="grade-details-score"><?php echo $form['score'] . (is_numeric($form['score']) ? '%' : '') ?></td>
									<td class="grade-details-date"><?php echo $form['date'] ?></td>
								</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr class="unit-no-details">
								<td colspan="3">There are currently no results to show for this unit.</td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>

	<?php endforeach; ?>

	<?php if (!empty($details['aux'])) : ?>
		<div class="unit-entry">
			<div class="unit-overview">
				<div class="unit-title">Other Grades</div>
				<div class="unit-score">--</div>
			</div>
			<div class="unit-details">
				<table>
					<thead>
						<tr>
							<td class="grade-details-title"><?php echo JText::_('Assignment') ?></td>
							<td class="grade-details-score"><?php echo JText::_('Score') ?></td>
							<td class="grade-details-date"><?php echo JText::_('Date recorded') ?></td>
						</tr>
					</thead>
					<tbody>
						<?php
							usort($details['aux'], function ($a, $b) {
								return strcmp($a['title'], $b['title']);
							});
						?>
						<?php foreach ($details['aux'] as $aux) : ?>
							<?php
								if (is_numeric($aux['score']) && $aux['score'] < 60)
								{
									$class = 'stop';
								}
								elseif (is_numeric($aux['score']) && $aux['score'] >= 60 && $aux['score'] < 70)
								{
									$class = 'yield';
								}
								elseif (is_numeric($aux['score']) && $aux['score'] >= 70)
								{
									$class = 'go';
								}
								else
								{
									$class = 'neutral';
								}
							?>
							<tr class="<?php echo $class ?>">
								<td class="grade-details-title"><?php echo $aux['title'] ?></td>
								<td class="grade-details-score"><?php echo $aux['score'] . (is_numeric($aux['score']) ? '%' : '') ?></td>
								<td class="grade-details-date"><?php echo $aux['date'] ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	<?php endif; ?>

	</div>

<?php if ($this->course->offering()->section()->badge()->isAvailable() && !$student->badge()->hasEarned()) : ?>
	<div class="recognition badge">
		<img src="<?php echo $this->course->offering()->section()->badge()->get('img_url') ?>" width="125" />
		<h3>Work hard. Earn a badge.</h3>
		<p>
			Upon successful completion of this course, you will be awarded a special <?php echo $this->course->get('title') ?> badge.
			This badge can be saved to your Purdue Passport Badges Backpack, and subsequently, your Mozilla Open Badges Backpack.
			To learn more about Purdue's Passport initiative, please visit the
			<a href="https://www.openpassport.org/Login" target="_blank">Open Passport website</a>.
		</p>
	</div>
<?php endif; ?>
</div>