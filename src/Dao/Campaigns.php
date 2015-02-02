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

namespace Wikimedia\IEGReview\Dao;

/**
 * Data access object for proposals.
 *
 * @author Bryan Davis <bd808@wikimedia.org>
 * @copyright © 2014 Bryan Davis, Wikimedia Foundation and contributors.
 */
class Campaigns extends AbstractDao {

	/**
	 * @var int|bool $userId
	 */
	protected $userId;


	/**
	 * @param string $dsn PDO data source name
	 * @param string $user Database user
	 * @param string $pass Database password
	 * @param int|bool $uid Authenticated user
	 * @param LoggerInterface $logger Log channel
	 */
	public function __construct(
		$dsn, $user, $pass, $uid = false, $logger = null
	) {
		parent::__construct( $dsn, $user, $pass, $logger );
		$this->userId = $uid;
	}

	public function getCampaign( $status ) {
		return $this->fetch(
			'SELECT * FROM campaigns WHERE campaign_status = ?',
			array( $status )
		);
	}

	/**
	* @param int $campid ID of the campaign to end
	*/
	public function endCampaign( $id ) {
		$sql = self::concat(
			'UPDATE campaigns SET',
			'end_date = now()',
			',campaign_status = 0',
			'WHERE id = :id'
		);
		$crit = array(
			'id' => $id
		);

		return $this->update( $sql, $crit );
	}

	/**
	* @param string $campname Name of the campaign to start
	
	public function startCampaign( $campname ) {
		$crit = array();
		$sql = self::concat(
			'INSERT INTO campaigns SET',
			'end_date = now()',
			',campaign_status = 0'
			'WHERE id = :id'
		);
		$crit['id'] = $campid;

		return $this->insert( $sql, $crit );
	}*/

}
