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

namespace Wikimedia\IEGReview\Controllers\User;

use Wikimedia\IEGReview\Controller;

/**
 * Reset password using recovery token
 *
 * @author Bryan Davis <bd808@wikimedia.org>
 * @copyright © 2014 Bryan Davis, Wikimedia Foundation and contributors.
 */
class ResetPassword extends Controller {

	protected function handleGet( $id, $token ) {
		if ( $this->dao->validatePasswordResetToken( $id, $token ) ) {
			$this->view->set( 'id', $id );
			$this->view->set( 'token', $token );
			$this->render( 'account/reset.html' );

		} else {
			$this->flash( 'error',
				$this->i18nContext->message( 'reset-password-bad-token' ) );
			$this->redirect( $this->urlFor( 'account_recover' ) );
		}
	}


	protected function handlePost( $id, $token ) {
		$this->form->requireString( 'newpw1' );
		$this->form->requireString( 'newpw2' );

		$dest = $this->urlFor( 'reset_password', array(
			'uid' => $id,
			'token' => $token,
		) );

		if ( $this->form->validate() ) {
			$newPass = $this->form->get( 'newpw1' );

			if ( $newPass !== $this->form->get( 'newpw2' ) ) {
				$this->flash( 'error',
					$this->i18nContext->message( 'reset-password-no-match' ) );

			} elseif ( empty( $newPass ) ) {
				$this->flash( 'error',
					$this->i18nContext->message( 'reset-password-empty' ) );

			} else {
				if ( $this->dao->resetPassword( $id, $token, $newPass ) ) {
					$this->flash( 'info',
						$this->i18nContext->message( 'reset-password-success' ) );
					$dest = $this->urlFor( 'login' );
				} else {
					$this->flash( 'error',
						$this->i18nContext->message( 'reset-password-fail' ) );
				}
			}
		} else {
			$this->flash( 'error',
				$this->i18nContext->message( 'reset-password-invalid' ) );
		}

		$this->redirect( $dest );
	}

}
