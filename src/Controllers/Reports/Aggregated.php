<?php
/**
 * @section LICENSE
 * This file is part of Wikimedia IEG Grant Review application.
 *
 * Wikimedia IEG Grant Review application is free software: you can
 * redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * Wikimedia IEG Grant Review application is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with Wikimedia IEG Grant Review application.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * @file
 * @copyright © 2014 Bryan Davis, Wikimedia Foundation and contributors.
 */

namespace Wikimedia\IEGReview\Controllers\Reports;

use Wikimedia\IEGReview\Controller;

/**
 * Aggregated scores.
 *
 * @author Bryan Davis <bd808@wikimedia.org>
 * @copyright © 2014 Bryan Davis, Wikimedia Foundation and contributors.
 */
class Aggregated extends Controller {

	protected function handleGet() {
		$this->view->setData( 'columns', array(
			'report-aggregated-proposal' => array(
				'column' => 'id',
				'format' => 'proposal',
				'text' => 'title',
			),
			'report-aggregated-theme' => array(
				'column' => 'theme',
			),
			'report-aggregated-amount' => array(
				'column' => 'amount',
				'format' => 'number',
				'precision' => 0,
			),
			'report-aggregated-impact' => array(
				'column' => 'impact',
				'format' => 'number',
				'precision' => 2,
			),
			'report-aggregated-innovation' => array(
				'column' => 'innovation',
				'format' => 'number',
				'precision' => 2,
			),
			'report-aggregated-ability' => array(
				'column' => 'ability',
				'format' => 'number',
				'precision' => 2,
			),
			'report-aggregated-engagement' => array(
				'column' => 'engagement',
				'format' => 'number',
				'precision' => 2,
			),
			'report-aggregated-recommend' => array(
				'format' => 'message',
				'message' => 'report-format-recommend',
				'columns' => array( 'recommend', 'rcnt', 'pcnt' ),
			),
		) );
		$this->view->setData( 'report', $this->dao->aggregatedScores() );
		$this->render( 'reports/report.html' );
	}

}
