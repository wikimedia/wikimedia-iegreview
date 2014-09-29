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
 * Data access object for reports.
 *
 * @author Bryan Davis <bd808@wikimedia.org>
 * @copyright © 2014 Bryan Davis, Wikimedia Foundation and contributors.
 */
class Reports extends AbstractDao {

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

	public function aggregatedScores() {
		$sql = self::concat(
			'SELECT p.id, p.title, p.theme, p.amount,',
			'r.impact,',
			'r.innovation,',
			'r.ability,',
			'r.engagement,',
			'r.recommend,',
			'r.cnt AS rcnt,',
			'ROUND((r.recommend / r.cnt) * 100, 2) AS pcnt',
			'FROM proposals p',
			'INNER JOIN (',
				'SELECT COUNT(*) AS cnt,',
				'AVG(impact) AS impact,',
				'AVG(innovation) AS innovation,',
				'AVG(ability) AS ability,',
				'AVG(engagement) AS engagement,',
				'SUM(recommendation) AS recommend,',
				'proposal',
				'FROM reviews',
				'GROUP BY proposal',
			') r ON p.id = r.proposal',
			'ORDER BY pcnt DESC, rcnt DESC, p.id'
		);
		return $this->fetchAll( $sql );
	}
}
