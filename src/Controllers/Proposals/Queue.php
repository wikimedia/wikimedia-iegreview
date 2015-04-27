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
 * Proposal reqiew queue.
 *
 * @author Bryan Davis <bd808@wikimedia.org>
 * @copyright © 2014 Bryan Davis, Wikimedia Foundation and contributors.
 */
class Queue extends Controller {

	protected function handleGet( $campaign ) {
		$this->form->expectString( 'type', array( 'default' => 'myqueue' ) );
		$this->form->expectString( 'th' );
		$this->form->expectInt( 'items',
			array( 'min_range' => 1, 'max_range' => 250, 'default' => 20 )
		);
		$this->form->expectInt( 'p', array( 'min_range' => 0, 'default' => 0 ) );
		$this->form->expectString( 's', array( 'default' => 'theme' ) );
		$this->form->expectInArray( 'o', array( 'asc', 'desc' ),
			array( 'default' => 'asc' )
		);
		$this->form->validate( $_GET );

		$this->view->set( 'type', $this->form->get( 'type' ) );
		$this->view->set( 'th', $this->form->get( 'th' ) );
		$this->view->set( 'items', $this->form->get( 'items' ) );
		$this->view->set( 'p', $this->form->get( 'p' ) );
		$this->view->set( 's', $this->form->get( 's' ) );
		$this->view->set( 'o', $this->form->get( 'o' ) );
		$this->view->set( 'found', null );
		$this->view->set( 'campaign', $campaign );

		$params = array(
			'type' => $this->form->get( 'type' ),
			'theme' => $this->form->get( 'th' ),
			'items' => $this->form->get( 'items' ),
			'page' => $this->form->get( 'p' ),
			'sort' => $this->form->get( 's' ),
			'order' => $this->form->get( 'o' ),
			'campaign' => $campaign
		);

		$ret = $this->dao->search( $params );
		$this->view->set( 'records', $ret->rows );
		$this->view->set( 'found', $ret->found );

		// pagination information
		list( $pageCount, $first, $last ) = $this->pagination(
			$ret->found, $this->form->get( 'p' ), $this->form->get( 'items' )
		);
		$this->view->set( 'pages', $pageCount );
		$this->view->set( 'left', $first );
		$this->view->set( 'right', $last );
		$this->render( 'proposals/queue.html', array( 'campaign' => $campaign ) );
	}

}
