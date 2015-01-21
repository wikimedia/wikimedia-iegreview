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
use Wikimedia\IEGReview\Password;

/**
 * View/edit a campaign.
 *
 * @author Niharika Kohli <nkohli@wikimedia.org>
 * @copyright © 2014 Niharika Kohli, Wikimedia Foundation and contributors.
 */
class EditCampaigns extends Controller {

	protected function handleGet() {
		$current_campaign = $this->dao->getCampaign( true );
		$this->view->set( 'campaign', $current_campaign );
		$this->render( 'admin/editcampaign.html' );
	}


	protected function handlePost() {

		//Returns empty string if no campaign in progress
		//$campid = $this->form->expectString( 'campaign_existing' );
		//$this->flash('info', $campid );
		//$campid = $this->form->get( 'campaign_existing' );

		$this->form->expectString( 'name', array( 'required' => true ) );
		// TODO: expectDate instead of expectString
		$this->form->expectString( 'start_date', array( 'required' => 'true' ) );
		$this->form->expectString( 'end_date', array( 'required' => true ) );
		$this->form->expectBool( 'checkend' );

		if ( $this->form->validate() ) {
			$params = array(
				'name' => $this->form->get( 'name' ),
				'start_date' => $this->form->get( 'start_date' ),
				'end_date' => $this->form->get( 'end_date' ),
			);
			$toEnd = $this->form->get( 'checkend' );
			if ( $toEnd == 1 ) {
				if ( $this->dao->endCampaign() ) {
					$this->flash( 'info', 'Campaign ended!' );
				} else {
					$this->flash( 'error', 'Error.' );
				}
			} else {
				if ( $this->dao->updateCampaign( $params ) ) {
					$this->flash( 'info', 'Campaign data updated.' );
				} else {
					$this->flash( 'error', 'Save failed' );
				}
			}
		}

		$this->redirect( $this->urlFor( 'admin_edit_campaign' ) );

	}

}