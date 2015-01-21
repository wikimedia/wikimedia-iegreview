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
 * Data access object for campaigns.
 *
 * @author Niharika Kohli <nkohli@wikimedia.org>
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
			'SELECT * FROM campaigns WHERE status = ?',
			array( $status )
		);
	}


	/**
	* Ends current in progress campaign
	*/
	public function endCampaign() {
		$sql = self::concat(
			'UPDATE campaigns SET',
			'end_date = now()',
			',status = 0',
			'WHERE status = 1'
		);

		return $this->update( $sql );
	}


	/**
	 * @param array $data Campaign data for a new campaign
	 */
	public function addCampaign( array $data ) {
		$data['created_by'] = $this->userId ? : null;
		$data['status'] = 1;
		$cols = array_keys( $data );
		$params = array_map( function ( $elm ) { return ":{$elm}"; }, $cols );

		$sql = self::concat(
			'INSERT INTO campaigns (',
			implode( ', ', $cols ),
			') VALUES (',
			implode( ', ', $params ),
			')'
		);
		return $this->insert( $sql, $data );

	}


	/**
	 * @param string $params Campaign data to be updated
	 */
	public function updateCampaign( $params ) {
		$fields = array( 'name', 'start_date', 'end_date' );
		$placeholders = array();
		foreach ( $fields as $field ) {
			$placeholders[] = "{$field} = :{$field}";
		}

		$sql = self::concat(
			'UPDATE campaigns SET',
			implode( ', ', $placeholders ),
			'WHERE status = 1'
		);
		$stmt = $this->dbh->prepare( $sql );

		try {
			$this->dbh->beginTransaction();
			$stmt->execute( $params );
			$this->dbh->commit();
			return true;

		} catch ( PDOException $e) {
			$this->dbh->rollback();
			$this->logger->error( 'Failed to update user', array(
				'method' => __METHOD__,
				'exception' => $e,
				'sql' => $sql,
				'params' => $params,
			) );
			return false;
		}

	}

}
