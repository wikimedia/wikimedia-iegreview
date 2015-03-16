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
 * Review a proposal.
 *
 * @author Bryan Davis <bd808@wikimedia.org>
 * @copyright © 2014 Bryan Davis, Wikimedia Foundation and contributors.
 */
class Review extends Controller {

	protected function handlePost( $id ) {
		$this->form->requireInt( 'proposal' );
		$this->form->requireInt( 'impact' );
		$this->form->expectString( 'impact_note' );
		$this->form->requireInt( 'innovation' );
		$this->form->expectString( 'innovation_note' );
		$this->form->requireInt( 'ability' );
		$this->form->expectString( 'ability_note' );
		$this->form->requireInt( 'engagement' );
		$this->form->expectString( 'engagement_note' );
		$this->form->requireInt( 'recommendation' );
		$this->form->expectString( 'comments' );

		if ( $this->form->validate() ) {
			$review = array(
				'proposal' => $this->form->get( 'proposal' ),
				'impact' => $this->form->get( 'impact' ),
				'impact_note' => $this->form->get( 'impact_note' ),
				'innovation' => $this->form->get( 'innovation' ),
				'innovation_note' => $this->form->get( 'innovation_note' ),
				'ability' => $this->form->get( 'ability' ),
				'ability_note' => $this->form->get( 'ability_note' ),
				'engagement' => $this->form->get( 'engagement' ),
				'engagement_note' => $this->form->get( 'engagement_note' ),
				'recommendation' => $this->form->get( 'recommendation' ),
				'comments' => $this->form->get( 'comments' ),
			);

			$ok = $this->dao->saveReview( $review );

			if ( $ok ) {
				$this->flash( 'info', $this->msg( 'review-edit-save' ) );
			} else {
				$this->flash( 'error',
					$this->msg( 'review-edit-save-error' )
				);
				// TODO: save input to be shown in get screen
			}

		} else {
			// TODO: save input to be shown in get screen
		}
		$this->redirect(
			$this->urlFor( 'proposals_view', array( 'id' => $id ) )
		);
	}
}
