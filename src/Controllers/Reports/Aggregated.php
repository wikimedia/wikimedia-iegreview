<?php
/**
 * @section LICENSE
 * This file is part of Wikimedia Grants Review application.
 *
 * Wikimedia Grants Review application is free software: you can
 * redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * Wikimedia Grants Review application is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with Wikimedia Grants Review application.  If not, see
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
class Aggregated extends AbstractReport {

	/**
	 * @return array Column descriptions
	 */
	protected function describeColumns() {
		return array(
			'report-aggregated-proposal' => array(
				'column' => 'id',
				'format' => 'proposal',
				'text' => 'title',
				'sortable' => true,
				'sortcolumn' => 'title',
			),
			'report-aggregated-theme' => array(
				'column' => 'theme',
				'sortable' => true,
				'sortcolumn' => 'theme',
			),
			'report-aggregated-amount' => array(
				'column' => 'amount',
				'format' => 'usd',
				'precision' => 0,
				'sortable' => true,
				'sortcolumn' => 'amount',
			),
			'report-aggregated-q1' => array(
				'column' => 'question1',
				'format' => 'number',
				'precision' => 2,
				'sortable' => false,
				'sortcolumn' => 'question1',
			),
			'report-aggregated-q2' => array(
				'column' => 'question2',
				'format' => 'number',
				'precision' => 2,
				'sortable' => false,
				'sortcolumn' => 'question2',
			),
			'report-aggregated-q3' => array(
				'column' => 'question3',
				'format' => 'number',
				'precision' => 2,
				'sortable' => false,
				'sortcolumn' => 'question3',
			),
			'report-aggregated-q4' => array(
				'column' => 'question4',
				'format' => 'number',
				'precision' => 2,
				'sortable' => false,
				'sortcolumn' => 'question4',
			),
			'report-aggregated-recommend' => array(
				'format' => 'message',
				'columns' => array( 'rcnt', 'total', 'rpercentage' ),
				'message' => 'report-format-recommend',
				'sortable' => true,
				'sortcolumn' => 'rcnt',
			),
			'report-aggregated-conditional' => array(
				'format' => 'message',
				'columns' => array( 'ccnt', 'total', 'cpercentage' ),
				'message' => 'report-format-recommend',
				'sortable' => true,
				'sortcolumn' => 'ccnt',
			),
		);
	}

	protected function defaultSortColumn() {
		return 'pcnt';
	}

	protected function defaultSortOrder() {
		return 'desc';
	}

	protected function getTemplate() {
		return 'reports/report.html';
	}

	/**
	 * @return stdClass Results
	 */
	protected function runReport() {
		$params = array(
			'sort' => $this->form->get( 's' ),
			'order' => $this->form->get( 'o' ),
			'items' => $this->form->get( 'items' ),
			'page' => $this->form->get( 'p' ),
		);
		$rows = $this->dao->aggregatedScores( $this->activeCampaign, $params );
		$proposals = array();
		$scorequestions = array();
		$recommendquestions = array();
		$result = array();
		$row = json_decode( json_encode ( $rows ) );

		foreach ( $rows->rows as $r ) {
			if ( is_array( $r ) ) {
				if ( !in_array( $r['proposal'], $proposals ) ) {
					array_push( $proposals, $r['proposal'] );
				}
				if ( !in_array( $r['question'], $scorequestions ) && $r['type'] == 'score' ) {
					array_push( $scorequestions, $r['question'] );
				}
				if ( !in_array( $r['question'], $recommendquestions ) && $r['type'] == 'recommend' ) {
					array_push( $recommendquestions, $r['question'] );
				}
			}
		}

		sort( $proposals );
		sort( $scorequestions );
		sort( $recommendquestions );

		foreach ( $proposals as $p ) {
			foreach ( $rows->rows as $r ) {
				if ( $r['proposal'] == $p ) {
					$result['rows'][$p]['title'] = $r['title'];
					$result['rows'][$p]['theme'] = $r['theme'];
					$result['rows'][$p]['amount'] = $r['amount'];
					break;
				}
			}
			$i = 0;
			foreach ( $scorequestions as $sq ) {
				$i++;
				foreach ( $rows->rows as $r ) {
					if ( $r['question'] == $sq && $r['proposal'] == $p ) {
						$result['rows'][$p]['question'.$i] = $r['avg'];
					}
				}
			}
		}

		foreach ( $proposals as $p ) {
			foreach ( $recommendquestions as $rq ) {
				foreach ( $rows->rows as $r ) {
					if ( $r['question'] == $rq && $r['proposal'] == $p ) {
						$result['rows'][$p]['rcnt'] = $r['recommend'];
						$result['rows'][$p]['ccnt'] = $r['conditional'];
						$result['rows'][$p]['rpercentage'] =
							( $r['recommend'] / $r['cnt'] ) * 100;
						$result['rows'][$p]['cpercentage'] =
							( $r['conditional'] / $r['cnt'] ) * 100;
						$result['rows'][$p]['total'] = $r['cnt'];
					}
				}
			}
		}

		return $result;
	}


}

