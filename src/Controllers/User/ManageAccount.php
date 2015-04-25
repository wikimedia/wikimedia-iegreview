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

namespace Wikimedia\IEGReview\Controllers\User;

use Wikimedia\IEGReview\Controller;

/**
 * Manage account screen for user.
 *
 * @author Bryan Davis <bd808@wikimedia.org>
 * @copyright © 2014 Bryan Davis, Wikimedia Foundation and contributors.
 */
class ManageAccount extends Controller {

	protected function handleGet() {
		$id = $this->authManager->getUserId();
		$this->view->set( 'user', $this->dao->getUserInfo( $id ) );
		$this->render( 'user/manageAccount.html' );
	}


	protected function handlePost() {
		$this->form->requireString( 'username' );
		$this->form->expectEmail( 'email' );
		$this->form->expectBool( 'blocked' );

		if ( $this->form->validate() ) {
			$params = array(
				'username' => $this->form->get( 'username' ),
				'email' => $this->form->get( 'email' ),
				'blocked' => $this->form->get( 'blocked' ) ? '1': '0',
			);
			$id = $this->authManager->getUserId();

			$ret = $this->dao->updateUserAccount( $id, $params );
			if ( !$ret ) {
				$this->flash( 'error',
					$this->i18nContext->message( 'user-manage-update-error' )
				);
			} else {
				$this->flash( 'info',
					$this->i18nContext->message( 'user-manage-update-success' )
				);
			}
		} else {
			$this->flash( 'error',
				$this->i18nContext->message( 'user-manage-form-error' )
			);
		}

		if ( $params['blocked'] === '1' ) {
			$this->redirect( $this->urlFor( 'logout' ) );
		} else {
			$this->redirect( $this->urlFor( 'user_manageaccount' ) );
		}
	}

}
