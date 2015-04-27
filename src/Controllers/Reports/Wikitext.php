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
 * Wikitext export of review status.
 *
 * @author Bryan Davis <bd808@wikimedia.org>
 * @copyright © 2014 Bryan Davis, Wikimedia Foundation and contributors.
 */
class Wikitext extends Controller {

	protected $campaignsDao;


	public function setCampaignsDao( $dao ) {
		$this->campaignsDao = $dao;
	}


	protected function getQuestions( $campaign ) {
		static $questions = null;
		if ( $questions === null ) {
			$questions = $this->campaignsDao->getQuestions( $campaign );
		}
		return $questions;
	}


	protected function handleGet( $campaign ) {
		$this->form->expectString( 'th' );
		$this->form->validate( $_GET );

		$params = array(
			'theme' => $this->form->get( 'th' ),
		);
		$records = $this->dao->export(
			$campaign, $this->getQuestions( $campaign ), $params
		);

		// HACK: map questions to A, B, C, D criteria labels for use in output
		// template.
		// FIXME: find a better way to associate questions and the wikitext
		$questions = array();
		foreach( $this->getQuestions( $campaign ) as $q ) {
			if( $q['type'] === 'score' ) {
				$questions[] = "q{$q['id']}";
			}
		}
		$questions = array_combine( array( 'A', 'B', 'C', 'D' ), $questions );

		$this->view->set( 'questions', $questions );
		$this->view->setData( 'report', $records );
		$this->view->set( 'th', $this->form->get( 'th' ) );
		$this->view->set( 'campaign', $campaign );
		$this->render( 'reports/wikitext.html' );
	}
}
