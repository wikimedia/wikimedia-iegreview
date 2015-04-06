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
			'SELECT * FROM campaigns WHERE status = 1'
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
	 * Checks if a given user is a reviewer for the given campaign
	 * @param int $campaign Campaign ID
	 * @param int $user User ID
	 */
	public function isReviewer( $campaign, $user ) {
		$reviewers = $this->getReviewers( $campaign );
		foreach ( $reviewers as $r ) {
			if ( $r['id'] == $user ) {
				return true;
			}
		}
		return false;
	}


	/**
	 * Get total proposal count for a given campaign
	 * @param int $campaign Campaign ID
	 * @return int Number of proposals
	 */
	public function getProposalCount( $campaign ) {
		$sql = self::concat(
			'SELECT COUNT(id) AS total',
			'FROM proposals',
			'WHERE campaign = ?'
		);
		return $this->fetch( $sql, array( $campaign ) );
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
	 * Get stats on reviewers from a given campaign
	 * @param int $campaign Campaign ID
	 * @return array Reviewer stats - proposals reviewed, total proposals, username
	 */
	public function getReviewerStats( $campaign ) {
		$fields = array(
			'cu.campaign_id',
			'cu.user_id',
			'COALESCE(ra.reviewed, 0) AS reviewed',
			'u.username'
		);
		$sql = self::concat(
			'SELECT', implode( ',', $fields ),
			'FROM campaign_users cu',
			'LEFT OUTER JOIN (',
				'SELECT COUNT(DISTINCT proposal) AS reviewed,',
				'reviewer',
				'FROM review_answers',
				'WHERE proposal IN (',
					'SELECT id FROM proposals WHERE campaign = :campaign',
				') GROUP BY reviewer',
			') ra ON ra.reviewer = cu.user_id',
			'INNER JOIN (',
				'SELECT username, id FROM users',
			') u ON u.id = cu.user_id',
			'WHERE cu.campaign_id = :campaign',
			'ORDER BY ra.reviewed DESC'
		);
		$result = $this->fetchAll( $sql, array( 'campaign' => $campaign ) );
		$totalProposals = $this->getProposalCount( $campaign );

		array_walk( $result, function ( &$val, $key, $totalProposals ) {
			$val['total'] = $totalProposals['total'];
		}, $totalProposals );
		return $result;
	}


	/**
	 * Fetch reviews by given user for given campaign
	 * @param int $user User ID
	 * @param int $campaign Campaign ID
	 * @return array proposals reviewed by a given user in a given campaign
	 */
	public function getReviewsByUser( $user, $campaign ) {
		if ( !$this->isReviewer( $campaign, $user ) ) {
			return false;
		} else {
			$sql = self::concat(
				'SELECT ra.proposal, ra.reviewer',
				'FROM review_answers ra',
				'INNER JOIN proposals p ON p.id = ra.proposal',
				'WHERE p.campaign = :campaign',
				'AND ra.reviewer = :user'
			);
			$data = array(
				'campaign' => $campaign,
				'user' => $user
			);
			return $this->fetchAllWithFound( $sql, $data );
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
	 * @return int Id of the newly inserted campaign
	 */
	public function addCampaign( array $data ) {
		$data['created_by'] = $this->userId ? : null;
		$cols = array_keys( $data );
		$params = self::makeBindParams( $cols );

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
	 * @param integer $id ID of campaign
	 * @param array $reviewers to be added
	 * @return bool true/false depending on success of the operation
	 */
	private function addReviewers( $id, array $reviewers ) {
		$added_by = $this->userId ? : null;
		$cols = array( 'campaign_id', 'user_id', 'added_by' );
		$params = self::makeBindParams( $cols );

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
			$this->insert( $sql, $data );
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
	 * @param integer $id ID of campaign
	 * @param array $reviewers New set of reviewers for the campaign $id
	 * @return bool true/false depending on success of the operation
	 */
	public function updateReviewers( $id, array $reviewers ) {
		if ( $reviewers['add'] ) {
			if ( $this->addReviewers( $id, $reviewers['add'] ) === false ) {
				return false;
			}
		}
		if ( $reviewers['remove'] ) {
			if ( $this->removeReviewers( $id, $reviewers['remove'] ) === false ) {
				return false;
			}
		}
		return true;
	}


	/**
	 * @param string $params Campaign data to be updated
	 * @param int $id Id of campaign to be updated
	 * @return bool True if update suceeded, false otherwise
	 */
	public function updateCampaign( $params, $id ) {
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


	/**
	 * Fetches all questions associated with a given campaign
	 * @param integer $campaign ID of campaign whose questions are to be fetched
	 */
	public function getQuestions( $campaign ) {
		return $this->fetchAll(
			self::concat(
				'SELECT *',
				'FROM review_questions',
				'WHERE campaign = ?',
				'ORDER BY id'
			),
			array( $campaign )
		);
	}


	/**
	 * Inserts new questions into the campaign_questions table
	 * @param integer $campaign Campaign id
	 * @param array $questions Array of questions to be added
	 * @param array $questionTitles Array of question titles
	 * @param array $questionFooters Array of question footers
	 */
	public function insertQuestions( $campaign, array $questions,
		array $questionTitles, array $questionFooters, array $questionTypes ) {

		$created_by = $this->userId ? : null;
		$cols = array(
			'campaign',
			'question_title',
			'question_body',
			'question_footer',
			'type',
			'created_by'
		);
		$params = self::makeBindParams( $cols );

		foreach ( $questions as $id => $ques ) {
			$sql = self::concat(
				'INSERT INTO review_questions (',
				implode( ', ', $cols ),
				') VALUES (',
				implode( ', ', $params ),
				')'
			);
			$data = array(
				'campaign'        => $campaign,
				'question_title'  => $questionTitles[$id],
				'question_body'   => $ques,
				'question_footer' => $questionFooters[$id],
				'type'            => $questionTypes[$id],
				'created_by'      => $created_by
			);
			$this->insert( $sql, $data );
		}
		return true;
	}


	/**
	 * Update questions associated with a campaign
	 * @param integer $campaign Campaign ID
	 * @param array $questions Associative array of id=>question(s) to be updated
	 * @param array $questionTitles Array of question titles
	 * @param array $questionFooters Array of question footers
	 */
	public function updateQuestions( $campaign, array $questions,
		array $questionTitles, array $questionFooters ) {
		$modified_by = $this->userId ? : null;
		$sql = self::concat(
			'UPDATE review_questions',
			'SET question_body = :qbody,',
			'question_title = :qtitle,',
			'question_footer = :qfooter,',
			'modified_at = :modified_at,',
			'modified_by = :modified_by',
			'WHERE id = :id'
		);
		foreach ( $questions as $id => $ques ) {
			$data = array(
				'id'         => $id,
				'qtitle'     => $questionTitles[$id],
				'qbody'      => $ques,
				'qfooter'    => $questionFooters[$id],
				'modified_by'=> $modified_by,
				'modified_at'=> date( 'Y-m-d H:i:s' )
			);
			$this->update( $sql, $data );
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
			'id', 'name', 'status'
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
