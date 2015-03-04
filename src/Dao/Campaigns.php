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
 * @copyright © 2014 Niharika Kohli, Wikimedia Foundation and contributors.
 */

namespace Wikimedia\IEGReview\Dao;

/**
 * Data access object for campaigns.
 *
 * @author Niharika Kohli <nkohli@wikimedia.org>
 * @copyright © 2014 Niharika Kohli, Wikimedia Foundation and contributors.
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

	public function activeCampaign() {
		return $this->fetch(
			'SELECT id FROM campaigns WHERE status = 1'
		);
	}

	/**
	 * @param int $id ID of campaign whose data is to be fetched
	 */
	public function getCampaign( $id ) {
		return $this->fetch(
			'SELECT * FROM campaigns WHERE id = ?',
			array( $id )
		);
	}


	/**
	 * @param int $id Fetches reviewers registered in the system
	 * If no parameter is passed, all registered reviewers are returned
	 */
	public function getReviewers( $id = null ) {
		if ( $id == null ) {
			return $this->fetchAll(
				'SELECT id, username, email FROM users WHERE reviewer = 1'
			);
		} else {
			$sql = self::concat(
				'SELECT u.id, u.username, u.email',
				'FROM users u, campaign_users cu',
				'WHERE u.id = cu.user_id AND cu.campaign_id = ?'
			);
			return $this->fetchAll( $sql, array( $id ) );
		}
	}


	/**
	 * @param int $id ID of campaign to end
	*/
	public function endCampaign( $id ) {
		$sql = self::concat(
			'UPDATE campaigns SET',
			'end_date = now()',
			',status = 0',
			'WHERE id = ?'
		);

		return $this->update( $sql, array( $id ) );
	}


	/**
	 * @param array $data Campaign data for a new campaign
	 * @param array $questions Review questions
	 * @return int Id of the newly inserted campaign
	 */
	public function addCampaign( array $data, array $questions ) {
		$data['created_by'] = $this->userId ? : null;
		$cols = array_keys( $data );
		$params = array_map( function ( $elm ) { return ":{$elm}"; }, $cols );

		$sql = self::concat(
			'INSERT INTO campaigns (',
			implode( ', ', $cols ),
			') VALUES (',
			implode( ', ', $params ),
			')'
		);
		return $this->insert( $sql, $data ) && $this->insertQuestions( $questions );

	}


	/**
	 * @param integer $id ID of campaign
	 * @param array $reviewers to be added
	 * @return bool true/false depending on success of the operation
	 */
	private function addReviewers( $id, array $reviewers ) {
		$added_by = $this->userId ? : null;
		$cols = array( 'campaign_id', 'user_id', 'added_by' );
		$params = array_map( function ( $elm ) { return ":{$elm}"; }, $cols );

		foreach ( $reviewers as $r ) {
			$sql = self::concat(
				'INSERT INTO campaign_users (',
				implode( ', ', $cols ),
				') VALUES (',
				implode( ', ', $params ),
				')'
			);
			$data = array(
				'campaign_id' => $id,
				'user_id' => $r,
				'added_by' => $added_by
			);
			$this->insert( $sql, $data2 );
		}
		return true;

	}


	/**
	 * @param integer $id ID of campaign
	 * @param array $reviewers to be removed
	 * @return bool true/false depending on success of the operation
	 */
	private function removeReviewers( $id, array $reviewers ) {
		foreach ( $reviewers as $r ) {
			$sql = self::concat(
				'DELETE FROM campaign_users WHERE',
				'campaign_id = :cid',
				'AND user_id = :uid'
			);
			$data = array(
				'cid' => $id,
				'uid' => $r
			);
			if ( $this->update( $sql, $data ) !== true ) {
				return false;
			}
		}
		return true;
	}


	/**
	 * @param array $questions Array of questions to be added
	 */
	public function insertQuestions( array $questions ) {
		$campaign = $this->activeCampaign();
		$created_by = $this->userId ? : null;
		$cols = array( 'campaign', 'question', 'added_by' );

		foreach ( $questions as $q ) {
			$sql = self::concat(
				'INSERT INTO review_questions (',
				implode( ', ', $cols ),
				') VALUES (',
				$campaign, ',', $q, ',', $created_by,
				')'
			);
			$this->insert( $sql );
		}
	}


	/**
	 * @param integer $id ID of campaign
	 * @param array $reviewers New set of reviewers for the campaign $id
	 * @return bool true/false depending on success of the operation
	 */
	public function updateReviewers( $id, array $reviewers ) {
		if( $reviewers['add'] ) {
			if( $this->addReviewers( $id, $reviewers['add'] ) === false ) {
				return false;
			}
		}
		if( $reviewers['remove'] ) {
			if( $this->removeReviewers( $id, $reviewers['remove'] ) === false ) {
				return false;
			}
		}
		return true;
	}


	/**
	 * @param array $questions Array of questions to be updated
	 */
	public function updateQuestions( array $questions ) {
		
	}

	/**
	 * @param string $params Campaign data to be updated
	 * @param array $questions Review questions
	 * @param int $id Id of campaign to be updated
	 * @return bool True if update suceeded, false otherwise
	 */
	public function updateCampaign( $params, $questions, $id ) {
		$fields = array( 'name', 'start_date', 'end_date' );
		$placeholders = array();
		foreach ( $fields as $field ) {
			$placeholders[] = "{$field} = :{$field}";
		}

		$sql = self::concat(
			'UPDATE campaigns SET',
			implode( ', ', $placeholders ),
			'WHERE id = :id'
		);
		$params['id'] = $id;
		$stmt = $this->dbh->prepare( $sql );

		try {
			$this->dbh->beginTransaction();
			$stmt->execute( $params );
			$this->dbh->commit();
			return true;

		} catch ( PDOException $e) {
			$this->dbh->rollback();
			$this->logger->error( 'Failed to update campaign', array(
				'method' => __METHOD__,
				'exception' => $e,
				'sql' => $sql,
				'params' => $params,
			) );
			return false;
		}
	}


	public function search( array $params ) {
		$defaults = array(
			'name' => null,
			'sort' => 'id',
			'order' => 'asc',
			'items' => 20,
			'page' => 0,
		);
		$params = array_merge( $defaults, $params );

		$where = array();
		$crit = array();

		$validSorts = array(
			'id', 'name'
		);
		$sortby = in_array( $params['sort'], $validSorts ) ?
			$params['sort'] : $defaults['sort'];
		$order = $params['order'] === 'desc' ? 'DESC' : 'ASC';

		if ( $params['items'] == 'all' ) {
			$limit = '';
			$offset = '';
		} else {
			$crit['int_limit'] = (int)$params['items'];
			$crit['int_offset'] = (int)$params['page'] * (int)$params['items'];
			$limit = 'LIMIT :int_limit';
			$offset = 'OFFSET :int_offset';
		}
		if ( $params['name'] !== null ) {
			$where[] = 'name like :name';
			$crit['name'] = $params['name'];
		}

		$sql = self::concat(
			'SELECT SQL_CALC_FOUND_ROWS * FROM campaigns',
			self::buildWhere( $where ),
			"ORDER BY {$sortby} {$order}, id {$order}",
			$limit, $offset
		);
		return $this->fetchAllWithFound( $sql, $crit );
	}

}
