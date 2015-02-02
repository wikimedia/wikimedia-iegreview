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

namespace Wikimedia\IEGReview\Controllers\Admin;

use Wikimedia\IEGReview\Controller;
use Wikimedia\IEGReview\Password;

/**
 * View/edit a user.
 *
 * @author Bryan Davis <bd808@wikimedia.org>
 * @copyright © 2014 Bryan Davis, Wikimedia Foundation and contributors.
 */
class Campaigns extends Controller {

	protected function handleGet() {
		$current_campaign = $this->dao->getCampaign( true );
		$this->view->set( 'campaign', $current_campaign );
		$this->render( 'admin/managecampaign.html' );
	}


	protected function handlePost() {
		$id = $this->request->post( 'id' );

		//Returns empty string if no campaign in progress
		//$campid = $this->form->expectString( 'campaign_existing' );
		//$this->flash('info', $campid );
		$campid = $this->request->post( 'campaign_existing' );
		$this->flash( 'info', $campid );

		if ( $this->form->expectString( 'campaign_existing' ) ) {
			$campid = $this->form->get( 'campaign_existing' );
			$this->flash( 'info', '' );
			$ret = $this->dao->endCampaign( $id );
			//$this->flash( 'info', $id );
			// TODO: Check ret and i18n the message
			if ( $ret ) {
				$this->flash('success', 'Campaign successfully ended!' );
			} else {
				$this->flash('success', 'Campaign successfully not ended!' );
			}
		}
		else {
			$this->form->expectString( 'camp_name',
				array( 'required' => true )
			);
			if ( $this->form->validate() ) {
				$campname = $this->form->get( 'camp_name' );
			}
			$ret = $this->dao->startCampaign( $campname );
			// TODO: Check ret and i18n the message
			$this->flash('success', 'Campaign successfully started!' );
		}

		$this->redirect( $this->urlFor( 'admin_manage_campaign' ) );

	}

}
