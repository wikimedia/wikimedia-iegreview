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
 * Add a new campaign.
 *
 * @author Niharika Kohli <nkohli@wikimedia.org>
 * @copyright © 2014 Niharika Kohli, Wikimedia Foundation and contributors.
 */
class AddCampaign extends Controller {

	protected function handleGet() {
		$current_campaign = $this->dao->getCampaign( true );
		$this->view->set( 'campaign', $current_campaign );
		$this->render( 'admin/addcampaign.html' );
	}


	protected function handlePost() {
		$this->form->expectString( 'name', array( 'required' => true ) );
		// TODO: expectDate instead of expectAnything
		$this->form->expectString( 'start_date', array( 'required' => 'true' ) );
		$this->form->expectString( 'end_date', array( 'required' => true ) );

		if ( $this->form->validate() ) {
			$params = array(
				'name' => $this->form->get( 'name' ),
				'start_date' => $this->form->get( 'start_date' ),
				'end_date' => $this->form->get( 'end_date' ),
			);
			if ( $this->dao->addCampaign( $params ) ) {
					$this->flash( 'info', 'Campaign created!' );
			} else {
				$this->flash( 'error', 'Error.' );
			}
		} else {
			$this->flash( 'error', 'Check errors' );
			}

		$this->redirect( $this->urlFor( 'admin_edit_campaign' ) );
	}
}
