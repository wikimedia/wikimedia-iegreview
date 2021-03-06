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

use Wikimedia\Slimapp\Controller;

/**
 * Aggregated scores.
 *
 * @author Bryan Davis <bd808@wikimedia.org>
 * @copyright © 2014 Bryan Davis, Wikimedia Foundation and contributors.
 */
class Aggregated extends AbstractReport {

	protected $campaignsDao;

	public function setCampaignsDao( $dao ) {
		$this->campaignsDao = $dao;
	}

	/**
	 * @return array Column descriptions
	 */
	protected function describeColumns( $campaign ) {
		$columns = array(
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
		);

		foreach ( $this->getQuestions( $campaign ) as $question ) {
			if ( $question['type'] === 'score' ) {
				$columns["q{$question['id']}"] = array(
					'header' => $question['report_head'],
					'column' => "q{$question['id']}",
					'sortcolumn' => "q{$question['id']}",
					'format' => 'number',
					'precision' => 2,
					'sortable' => true,
				);
			} else {
				$columns['report-aggregated-recommend'] = array(
					'format' => 'message',
					'message' => 'report-format-recommend',
					'columns' => array(
						'recommend', 'conditional', 'rcnt', 'pcnt',
					),
					'sortable' => true,
					'sortcolumn' => 'pcnt',
				);
			}
		}

		return $columns;
	}

	protected function defaultSortColumn() {
		return 'pcnt';
	}

	protected function defaultSortOrder() {
		return 'desc';
	}

	protected function getQuestions( $campaign ) {
		static $questions = null;
		if ( $questions === null ) {
			$questions = $this->campaignsDao->getQuestions( $campaign );
		}
		return $questions;
	}

	/**
	 * @return stdClass Results
	 */
	protected function runReport( $campaign ) {
		$params = array(
			'sort' => $this->form->get( 's' ),
			'order' => $this->form->get( 'o' ),
			'items' => $this->form->get( 'items' ),
			'page' => $this->form->get( 'p' ),
		);

		$searchResults = $this->dao->aggregatedScores(
			$campaign, $this->getQuestions( $campaign ), $params
		);
		$this->view->set( 'records', $searchResults->rows );
		$this->view->set( 'found', $searchResults->found );

		// pagination information
		list( $pageCount, $first, $last ) = $this->pagination(
			$searchResults->found, $this->form->get( 'p' ), $this->form->get( 'items' )
		);
		$this->view->set( 'pages', $pageCount );
		$this->view->set( 'left', $first );
		$this->view->set( 'right', $last );

		return $searchResults;
	}

}

