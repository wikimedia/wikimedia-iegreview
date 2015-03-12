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
 * Review a proposal.
 *
 * @author Bryan Davis <bd808@wikimedia.org>
 * @copyright © 2014 Bryan Davis, Wikimedia Foundation and contributors.
 */
class Review extends Controller {

	protected function handlePost( $id ) {
		$this->form->requireInt( 'proposal' );
		$this->form->requireIntArray( 'points' );
		$this->form->expectStringArray( 'notes' );

		if ( $this->form->validate() ) {
			$review = array(
				'proposal' => $this->form->get( 'proposal' ),
				'points' => $this->form->get( 'points' ),
				'notes' => $this->form->get( 'notes' ),
			);

			$ok = $this->dao->insertOrUpdateReview( $review );

			if ( $ok ) {
				$this->flash( 'info', $this->msg( 'review-edit-save' ) );
			} else {
				$this->flash( 'error',
					$this->msg( 'review-edit-save-error' )
				);
				// TODO: save input to be shown in get screen
			}

		} else {
			$this->flash( 'error',
				$this->msg( 'review-edit-submission-error' )
			);
			// TODO: save input to be shown in get screen
		}
		$this->redirect(
			$this->urlFor( 'proposals_view', array( 'id' => $id ) )
		);
	}
}
