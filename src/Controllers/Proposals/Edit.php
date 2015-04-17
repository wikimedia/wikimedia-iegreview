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

namespace Wikimedia\IEGReview\Controllers\Proposals;

use Wikimedia\IEGReview\Controller;

/**
 * Edit a proposal.
 *
 * @author Bryan Davis <bd808@wikimedia.org>
 * @copyright © 2014 Bryan Davis, Wikimedia Foundation and contributors.
 */
class Edit extends Controller {

	protected function setupForm( array $defaults ) {
		$defaults = array_merge(
			array(
				'title'       => null,
				'description' => null,
				'url'         => null,
				'amount'      => null,
				'theme'       => null,
				'notes'       => null,
				'status'      => false
			),
			$defaults
		);
		$this->form->requireString( 'title', array(
			'default' => $defaults['title'],
		) );
		$this->form->requireUrl( 'url', array(
			'default' => $defaults['url'],
		) );
		// TODO: themes from db?
		$this->form->requireInArray( 'theme',
			array( 'online', 'offline', 'tool', 'research' ),
			array(
				'default' => $defaults['theme'],
			) );
		$this->form->expectInt( 'amount', array(
			'default' => $defaults['amount'],
		) );
		$this->form->expectString( 'description', array(
			'default' => $defaults['description'],
		) );

		$this->form->expectString( 'notes', array(
			'default' => $defaults['notes'],
		) );

		$this->form->expectString( 'status', array(
			'default' => $defaults['status'],
		) );

		$this->log->debug( print_r( $this->form, true ) );
		$this->view->setData( 'form', $this->form );
	}

	protected function handleGet( $id ) {
		if ( is_numeric( $id ) ) {
			$proposal = $this->dao->getProposal( $id );
			if ( $proposal['status'] == 'open' ) {
				$proposal['status'] = false;
			} else {
				$proposal['status'] = true;
			}
		} else {
			$proposal = array();
		}
		$this->setupForm( $proposal );
		$this->view->setData( 'id', $id );
		$this->render( 'proposals/edit.html' );
	}

	protected function handlePost() {
		$id = $this->request->post( 'id' );
		$this->setupForm( array() );
		$redir = $this->urlFor( 'proposals_edit', array( 'id' => $id ) );

		if ( $this->form->validate() ) {
			$proposal = array(
				'title' => $this->form->get( 'title' ),
				'description' => $this->form->get( 'description' ),
				'url' => $this->form->get( 'url' ),
				'amount' => $this->form->get( 'amount' ),
				'theme' => $this->form->get( 'theme' ),
				'notes' => $this->form->get( 'notes' ),
				'status' => $this->form->get( 'status' ),
				'campaign' => $this->activeCampaign
			);

			if ( is_numeric( $id ) ) {
				$ok = $this->dao->updateProposal( $id, $proposal );
			} else {
				$ok = $this->dao->createProposal( $proposal );
				if ( $ok !== false ) {
					$id = $ok;
				}
			}

			if ( $ok ) {
				$this->flash( 'info', $this->msg( 'proposals-edit-save' ) );
				$redir = $this->urlFor(
					'proposals_view', array( 'id' => $id )
				);
			} else {
				$this->flash( 'error',
					$this->msg( 'proposals-edit-save-error' )
				);
				// TODO: save input to be shown in get screen
			}

		} else {
			// TODO: save input to be shown in get screen
		}
		$this->redirect( $redir );
	}
}
