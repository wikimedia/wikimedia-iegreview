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

namespace Wikimedia\IEGReview\Controllers;

use Wikimedia\IEGReview\AuthManager;
use Wikimedia\IEGReview\Controller;

/**
 * Routes related to authentication.
 *
 * @author Bryan Davis <bd808@wikimedia.org>
 * @copyright © 2014 Bryan Davis, Wikimedia Foundation and contributors.
 */
class Login extends Controller {

	protected function handleGet() {
		$this->render( 'login.html' );
	}

	protected function handlePost() {
		$next = false;
		if ( isset( $_SESSION[AuthManager::NEXTPAGE_SESSION_KEY] ) ) {
			$next = $_SESSION[AuthManager::NEXTPAGE_SESSION_KEY];
			$next = filter_var( $next, \FILTER_VALIDATE_URL, \FILTER_FLAG_PATH_REQUIRED );
		}

		$this->form->expectString( 'username', array( 'required' => true ) );
		$this->form->expectString( 'password', array( 'required' => true ) );

		if ( $this->form->validate() ) {
			$authed = $this->authManager->authenticate(
				$this->form->get( 'username' ),
				$this->form->get( 'password' )
			);

			if ( $authed ) {
				$this->flash( 'info', $this->i18nContext->message( 'login-success' ) );
				$this->redirect( $next ?: $this->urlFor( 'home' ) );

			} else {
				$this->flash( 'error', $this->i18nContext->message( 'login-failed' ) );
				$this->log->info( 'Failed login attempt for {username}', array(
					'username' => $this->form->get( 'username' ),
				) );
			}

		} else {
			$this->flash( 'error', $this->i18nContext->message( 'login-error' ) );
		}

		$this->redirect( $this->urlFor( 'login' ) );
	}

}
