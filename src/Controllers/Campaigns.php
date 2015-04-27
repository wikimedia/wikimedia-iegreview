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

namespace Wikimedia\IEGReview\Controllers;

use Wikimedia\IEGReview\Controller;

/**
 * Landing page after authentication which lists all the campaigns.
 *
 * @author Niharika Kohli <nkohli@wikimedia.org>
 * @copyright © 2015 Niharika Kohli, Wikimedia Foundation and contributors.
 */
class Campaigns extends Controller {

	protected function handleGet() {
		$campaigns = $this->dao->getUserCampaigns();
		$this->view->set( 'campaigns', $campaigns );
		$this->render( 'campaign.html' );
	}

	protected function handlePost() {
		$this->redirect( $this->urlFor( 'login' ) );
	}

}
