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

use Wikimedia\Slimapp\Controller;

/**
 * Search applications.
 *
 * @author Bryan Davis <bd808@wikimedia.org>
 * @copyright © 2014 Bryan Davis, Wikimedia Foundation and contributors.
 */
class Search extends Controller {

	protected function handleGet( $campaign ) {
		$this->form->expectString( 't' );
		$this->form->expectString( 'th' );
		$this->form->expectString( 'campaign-select' );
		$this->form->expectInArray( 'stat',
			array( 'open', 'approved', 'rejected', 'abandoned' ),
			array( 'default' => 'open' )
		);
		$this->form->expectInt( 'items',
			array( 'min_range' => 1, 'max_range' => 250, 'default' => 50 )
		);
		$this->form->expectInt( 'p', array( 'min_range' => 0, 'default' => 0 ) );
		$this->form->expectString( 's', array( 'default' => 'id' ) );
		$this->form->expectInArray( 'o', array( 'asc', 'desc' ),
			array( 'default' => 'asc' )
		);
		$this->form->validate( $_GET );

		$this->view->set( 't', $this->form->get( 't' ) );
		$this->view->set( 'th', $this->form->get( 'th' ) );
		$this->view->set( 'campaign-select', $this->form->get( 'campaign-select' ) );
		$this->view->set( 'stat', $this->form->get( 'stat' ) );
		$this->view->set( 'items', $this->form->get( 'items' ) );
		$this->view->set( 'p', $this->form->get( 'p' ) );
		$this->view->set( 's', $this->form->get( 's' ) );
		$this->view->set( 'o', $this->form->get( 'o' ) );
		$this->view->set( 'found', null );
		$this->view->set( 'campaign', $campaign );

		$campaignslist = $this->dao->getCampaigns();
		$this->view->set( 'campaigns', $campaignslist );

		if ( $this->form->get( 't' ) ||
			$this->form->get( 'th' ) ||
			$this->form->get( 'campaign' )
		) {
			$params = array(
				'title' => $this->form->get( 't' ),
				'theme' => $this->form->get( 'th' ),
				'campaign' => $this->form->get( 'campaign-select' ),
				'status' => $this->form->get( 'stat' ),
				'sort' => $this->form->get( 's' ),
				'order' => $this->form->get( 'o' ),
				'items' => $this->form->get( 'items' ),
				'page' => $this->form->get( 'p' ),
			);

			$ret = $this->dao->search( $params );
			$this->view->set( 'records', $ret->rows );
			$this->view->set( 'found', $ret->found );

			// pagination information
			list( $pageCount, $first, $last ) = $this->pagination(
				$ret->found, $this->form->get( 'p' ), $this->form->get( 'items' ) );
			$this->view->set( 'pages', $pageCount );
			$this->view->set( 'left', $first );
			$this->view->set( 'right', $last );
		}

		$this->render( 'proposals/search.html' );
	}

}
