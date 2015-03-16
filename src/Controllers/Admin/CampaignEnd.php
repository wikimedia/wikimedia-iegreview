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

/**
 * End a campaign.
 *
 * @author Niharika Kohli <nkohli@wikimedia.org>
 * @copyright © 2014 Niharika Kohli, Wikimedia Foundation and contributors.
 */
class CampaignEnd extends Controller {

	protected function handlePost( $id ) {
		if ( $this->dao->endCampaign( $id ) === false ) {
			$this->flash( 'error',
				$this->i18nContext->message( 'admin-campaign-update-fail' )
			);
		} else {
			$this->flash( 'info',
				$this->i18nContext->message( 'admin-campaign-end-success' )
			);
		}

		$this->redirect( $this->urlFor( 'admin_campaign', array( 'id' => $id ) ) );
	}

}
