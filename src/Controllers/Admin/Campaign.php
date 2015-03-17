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
 * @copyright © 2015 Niharika Kohli, Wikimedia Foundation and contributors.
 */

namespace Wikimedia\IEGReview\Controllers\Admin;

use Wikimedia\IEGReview\Controller;
use Wikimedia\IEGReview\Arrays;

/**
 * Add a new campaign.
 *
 * @author Niharika Kohli <nkohli@wikimedia.org>
 * @copyright © 2014 Niharika Kohli, Wikimedia Foundation and contributors.
 */
class Campaign extends Controller {

	protected function handleGet( $id ) {
		$reviewers = $this->dao->getReviewers();
		if ( $id === 'new' ) {
			$campaign = array(
				'name' => '',
				'start_date' => date( 'Y-m-d H:i:s' ),
				'end_date' => date( 'Y-m-d H:i:s', strtotime( '+30 days' ) ),
			);
			$questions = array();
			for ( $idx = 0; $idx < 5; $idx ++ ) {
				$questions[$idx] = array(
					'id' => $idx,
					'question_title' => '',
					'question_body' => '',
					'question_footer' => '',
				);
			}

			if ( is_numeric( $this->activeCampaign ) ) {
				$this->flashNow( 'error',
					$this->i18nContext->message( 'admin-new-campaign-in-progress' )
				);
			}
		} else {
			$campaign = $this->dao->getCampaign( $id );
			$currentReviewers = $this->dao->getReviewers( $id );
			foreach ( $reviewers as $key => $row ) {
				$reviewers[$key]['val'] = '0';
				foreach ( $currentReviewers as $rCurrent ) {
					if ( $row['id'] == $rCurrent['id'] ) {
						$reviewers[$key]['val'] = '1';
					}
				}
			}
			$questions = $this->dao->getQuestions( $id );
		}
		$this->view->set( 'id', $id );
		$this->view->set( 'campaign', $campaign );
		$this->view->set( 'rev', $reviewers );
		$this->view->set( 'ques', $questions );
		$this->render( 'admin/campaign.html' );
	}


	protected function handlePost() {
		$id = $this->request->post( 'id' );

		$this->form->requireString( 'name' );
		// TODO: requireDate instead of requireString
		$this->form->requireString( 'start_date' );
		$this->form->requireString( 'end_date' );

		$this->form->expectIntArray( 'reviewer' );
		$this->form->requireStringArray( 'questions' );
		$this->form->requireStringArray( 'qtitles' );
		$this->form->requireStringArray( 'qfooters' );

		if ( $this->form->validate() ) {
			$params = array(
				'name' => $this->form->get( 'name' ),
				'start_date' => $this->form->get( 'start_date' ),
				'end_date' => $this->form->get( 'end_date' ),
			);

			$questions = $this->form->get( 'questions' );
			$questionTitles = $this->form->get( 'qtitles' );
			$questionFooters = $this->form->get( 'qfooters' );

			if ( $id == 'new' && $this->dao->activeCampaign() ) {
				$this->flash( 'error',
					$this->i18nContext->message( 'admin-new-campaign-in-progress' )
				);
			} elseif ( $id == 'new' ) {
				// This is a temporary fix to make the *just started* campaign
				// active and bypass the actual start and end date to be fixed
				// in a subsequent patch when actual logic for using start and
				// end dates is implemented
				$params['status'] = 1;

				$newCampaign = $this->dao->addCampaign( $params );
				if ( $newCampaign !== false ) {
					$this->flash( 'info',
						$this->i18nContext->message( 'admin-campaign-create-success' )
					);
					$id = $newCampaign;
					$reviewers = $this->form->get( 'reviewer' );
					if ( $reviewers !== null ) {
						$diff = Arrays::difference( array(), $reviewers );
						$this->dao->updateReviewers( $id, $diff );
					}

					if ( $questions !== null ) {
						$questionTypes = array(
							'score', 'score', 'score', 'score', 'recommend'
						);
						$this->dao->insertQuestions(
							$id, $questions, $questionTitles, $questionFooters, $questionTypes
						);
					}
				} else {
					$this->flash( 'error',
						$this->i18nContext->message('admin-campaign-create-fail' )
					);
				}

			} else {

				$newReviewers = $this->form->get( 'reviewer' );
				if ( $newReviewers == null ) {
					$newReviewers = array();
				}
				$currentReviewers = $this->dao->getReviewers( $id );
				//Convert the query result set to a simple array
				$oldReviewers = array_map( function( $r ) {
					return $r['id'];
				}, $currentReviewers );
				$diff = Arrays::difference( $oldReviewers, $newReviewers );
				// TODO: Check return value and add error message
				$this->dao->updateReviewers( $id, $diff );
				$this->dao->updateQuestions( $id, $questions, $questionTitles, $questionFooters );

				if ( $this->dao->updateCampaign( $params, $id ) ) {
					$this->flash( 'info',
					$this->i18nContext->message('admin-campaign-update-success' )
					);
				} else {
					$this->flash( 'error',
						$this->i18nContext->message('admin-campaign-update-fail' )
					);
				}
			}

		} else {
			$this->flash( 'error', 'Invalid submission.' );
			$this->flash( 'form_errors', $this->form->getErrors() );

			$id = $this->request->post( 'id' );
			$questions = $this->form->get( 'questions' );
			$questionTitles = $this->form->get( 'qtitles' );
			$questionFooters = $this->form->get( 'qfooters' );
			$quesDefaults = array();
			if ( $id == 'new' ) {
				for ( $idx = 0; $idx < 5; $idx ++ ) {
					$quesDefaults[$idx] = array(
						'id' => $idx,
						'question_title' =>
							isset( $questionTitles[$idx] ) ? $questionTitles[$idx] : '',
						'question_body' =>
							isset( $questions[$idx] ) ? $questions[$idx] : '',
						'question_footer' =>
							isset( $questionFooters[$idx] ) ? $questionFooters[$idx] : '',
					);
				}
			} else {
				$prevQuestions = $this->dao->getQuestions( $id );
				foreach ( $prevQuestions as $q ) {
					$idx = $q['id'];
					$quesDefaults[$idx] = array(
						'id' => $idx,
						'question_title' =>
							isset( $questionTitles[$idx] ) ?
								$questionTitles[$idx] : $q['question_title'],
						'question_body' =>
							isset( $questions[$idx] ) ? $questions[$idx] : $q['question_body'],
						'question_footer' =>
							isset( $questionFooters[$idx] ) ?
								$questionFooters[$idx] : $q['question_footer'],
						);
				}
			}
			$this->flash( 'form_defaults', $quesDefaults );
		}

		$this->redirect( $this->urlFor( 'admin_campaign', array( 'id' => $id ) ) );

	}

}

