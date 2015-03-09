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

namespace Wikimedia\IEGReview\Controllers\Reports;

use Wikimedia\IEGReview\Controller;

/**
 * Managing campaign reports - Past campaigns and associated proposals
 *
 * @author Niharika Kohli <nkohli@wikimedia.org>
 * @copyright © 2015 Niharika Kohli, Wikimedia Foundation and contributors.
 */
class Campaigns extends AbstractReport {

	/**
	 * @return array Column descriptions
	 */
	protected function describeColumns() {
		return array(
			'report-campaign-name' => array(
				'column' => 'id',
				'sortable' => true,
				'text' => 'name',
				'format' => 'campaign',
				'sortcolumn' => 'name',
			),
			'report-campaign-start-date' => array(
				'column' => 'start_date',
				'sortable' => true,
				'sortcolumn' => 'start_date',
			),
			'report-campaign-end-date' => array(
				'column' => 'end_date',
				'sortable' => true,
				'sortcolumn' => 'end_date',
			),
		);
	}

	/**
	 * @return stdClass Results
	 */
	protected function runReport() {
		$params = array(
			'sort' => $this->form->get( 's' ),
			'order' => $this->form->get( 'o' ),
			'items' => $this->form->get( 'items' ),
			'page' => $this->form->get( 'p' ),
		);
		return $this->dao->campaigns( $params );
	}
}
