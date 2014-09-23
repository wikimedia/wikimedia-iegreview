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

namespace Wikimedia\IEGReview\Controllers\Proposals;

use Wikimedia\IEGReview\Controller;

/**
 * Add a new proposal.
 *
 * @author Bryan Davis <bd808@wikimedia.org>
 * @copyright © 2014 Bryan Davis, Wikimedia Foundation and contributors.
 */
class Add extends Controller {

	protected function setupForm() {
		$saved = $this->flashGet( 'form' );
		if ( $saved !== null ) {
			$this->setForm( $saved );
		} else {
			$this->form->expectString( 'title', array( 'required' => true ) );
			$this->form->expectUrl( 'url', array( 'required' => true ) );
			$this->form->expectString( 'description' );
			$this->form->expectInt( 'amount' );
			// TODO: themes from db?
			$this->form->expectInArray( 'theme',
				array( 'online', 'offline', 'tool', 'research' ),
				array( 'required' => true )
			);
		}
		$this->view->setData( 'form', $this->form );
	}

	protected function handleGet() {
		$this->setupForm();
		$this->render( 'proposals/add.html' );
	}

	protected function handlePost() {
		$this->setupForm();
		if ( $this->form->validate() ) {
			$proposal = array(
				'title' => $this->form->get( 'title' ),
				'description' => $this->form->get( 'description' ),
				'url' => $this->form->get( 'url' ),
			);
			$id = $this->dao->createProposal( $proposal );
			if ( $id !== false ) {
				$this->flash( 'info', $this->msg( 'proposals-add-save' ) );
			} else {
				$this->flash( 'error',
					$this->msg( 'proposals-add-save-error' )
				);
			}
		} else {
			$this->flash( 'form', $this->form );
		}
		$this->redirect( $this->urlFor( 'proposals_add' ) );
	}

}
