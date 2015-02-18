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
				'end_date' => date( 'Y-m-d H:i:s' ),
			);
		} else {
			$campaign = $this->dao->getCampaign( $id );
			$reviewers_current = $this->dao->getReviewers( $id );
			foreach ( $reviewers as $key=>$row ) {
				$reviewers[$key]['val'] = '0';
				foreach ( $reviewers_current as $r_current ) {
					if ( $row['id'] == $r_current['id'] ) {
						$reviewers[$key]['val'] = '1';
					}
				}
			}
		}
		$this->view->set( 'id', $id );
		$this->view->set( 'campaign', $campaign );
		$this->view->set( 'rev', $reviewers );
		$this->render( 'admin/campaign.html' );
	}


	protected function handlePost() {
		$id = $this->request->post( 'id' );

		$this->form->expectString( 'name', array( 'required' => true ) );
		// TODO: expectDate instead of expectString
		$this->form->expectString( 'start_date', array( 'required' => 'true' ) );
		$this->form->expectString( 'end_date', array( 'required' => true ) );

		// Create expectArray method in Form.php
		//Filed as T90387
		$this->form->expectAnything( 'reviewer' );

		if ( $this->form->validate() ) {
			$params = array(
				'name' => $this->form->get( 'name' ),
				'start_date' => $this->form->get( 'start_date' ),
				'end_date' => $this->form->get( 'end_date' ),
			);

			if ( $id == 'new' ) {
				// This is a temporary fix to make the *just started* campaign active
				// and bypass the actual start and end date
				// to be fixed in a subsequent patch when actual logic for using
				// start and end dates is implemented
				$params['status'] = 1;
				$newCampaign = $this->dao->addCampaign( $params );

				if ( $newCampaign !== false ) {
					$this->flash( 'info',
						$this->i18nContext->message( 'admin-campaign-create-success' )
					);
					$id = $newCampaign;
					$reviewers = $this->request->post( 'reviewer' );
					$this->dao->addReviewers( $id, $reviewers );
				} else {
					$this->flash( 'error',
						$this->i18nContext->message('admin-campaign-create-fail' )
					);
				}

			} else {

				$new_reviewers = $this->request->post( 'reviewer' );
				$current_reviewers = $this->dao->getReviewers( $id );
				//Convert the query result set to a simple array
				$old_reviewers = array();
				foreach( $current_reviewers as $r ) {
					array_push( $old_reviewers, $r['id'] );
				}
				$diff = Arrays::difference( $old_reviewers, $new_reviewers );
				$this->dao->updateReviewers( $id, $diff );

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

		$this->redirect( $this->urlFor( 'admin_campaign', array( 'id' => $id ) ) );
	}

}

}

