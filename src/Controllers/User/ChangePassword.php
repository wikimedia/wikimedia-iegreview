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

namespace Wikimedia\IEGReview\Controllers\User;

use Wikimedia\IEGReview\Controller;

/**
 * Routes related to authentication.
 *
 * @author Bryan Davis <bd808@wikimedia.org>
 * @copyright © 2014 Bryan Davis, Wikimedia Foundation and contributors.
 */
class ChangePassword extends Controller {

	protected function handleGet() {
		$this->view->set( 'user', $this->authManager->getUser() );
		$this->render( 'user/changePassword.html' );
	}


	protected function handlePost() {
		$this->form->requireString( 'oldpw' );
		$this->form->requireString( 'newpw1' );
		$this->form->requireString( 'newpw2' );

		if ( $this->form->validate() ) {
			$oldPass = $this->form->get( 'oldpw' );
			$newPass = $this->form->get( 'newpw1' );
			$id = $this->authManager->getUserId();

			if ( $newPass !== $this->form->get( 'newpw2' ) ) {
				$this->flash( 'error', 'Passwords do not match' );

			} elseif ( empty( $newPass ) ) {
				$this->flash( 'error', 'Password can not be empty.' );

			} else {
				if ( $this->dao->updatePassword( $oldPass, $newPass, $id, false ) ) {
					$this->flash( 'info', 'Password change successful.' );

				} else {
					$this->flash( 'error', 'Password change failed. Old password may be invalid.' );
				}
			}
		} else {
			//FIXME: actually pass form errors back to view
			$this->flash( 'error', 'Invalid input.' );
		}

		$this->redirect( $this->urlFor( 'user_changepassword' ) );
	}

}
