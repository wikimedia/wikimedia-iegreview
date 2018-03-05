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

namespace Wikimedia\IEGReview\Dao;

use Wikimedia\IEGReview\Auth\UserData;

use \PDOException;
use Wikimedia\Slimapp\Auth\Password;
use Wikimedia\Slimapp\Auth\UserManager;
use Wikimedia\Slimapp\Dao\AbstractDao;

/**
 * Data access object for users.
 * @copyright © 2014 Bryan Davis, Wikimedia Foundation and contributors.
 */
class Users extends AbstractDao implements UserManager {

	public function getUserData( $username ) {
		$data = $this->fetch(
			'SELECT * FROM users WHERE username = ? AND isvalid = 1',
			[ $username ]
		);
		if ( $data === false ) {
			$this->logger->info( "No data found for user '{$username}'" );
			$data = [];
		}
		return new UserData( $data );
	}
	public function getUser( $username ) {
		return $this->fetch(
			'SELECT * FROM users WHERE username = ? AND isvalid = 1',
			array( $username )
		);
	}

	public function getUsername( $id ) {
		return $this->fetch(
			'SELECT username FROM users WHERE id = ?',
			array( $id )
		);
	}

	public function search( array $params ) {
		$defaults = array(
			'name' => null,
			'email' => null,
			'sort' => 'id',
			'order' => 'asc',
			'items' => 20,
			'page' => 0,
		);
		$params = array_merge( $defaults, $params );

		$where = array();
		$crit = array();

		$validSorts = array(
			'id', 'username', 'email', 'reviwer', 'isvalid',
			'isadmin', 'viewreports', 'blocked',
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
			$where[] = 'username like :name';
			$crit['name'] = $params['name'];
		}
		if ( $params['email'] !== null ) {
			$where[] = 'email like :email';
			$crit['email'] = $params['email'];
		}

		$sql = self::concat(
			'SELECT SQL_CALC_FOUND_ROWS * FROM users',
			self::buildWhere( $where ),
			"ORDER BY {$sortby} {$order}, id {$order}",
			$limit, $offset
		);
		return $this->fetchAllWithFound( $sql, $crit );
	}

	public function getUserInfo( $user_id ) {
		return $this->fetch(
			"SELECT * FROM users WHERE id = ?",
			array( $user_id )
		);
	}

	public function isSysAdmin( $user_id ) {
		$res = $this->fetch(
			"SELECT isadmin FROM users WHERE id = ?",
			array( $user_id )
		);
		return $res['isadmin'];
	}

	public function newUserCreate( $answers ) {
		$fields = array(
			'username', 'password', 'email', 'reviewer', 'isvalid',
			'isadmin', 'viewreports'
		);
		$placeholders = array();
		$vals = array();
		foreach ( $fields as $field ) {
			$placeholders[] = ":{$field}";
			$vals[$field] = $answers[$field];
		}

		$sql = 'INSERT INTO users (' .
			implode( ', ', $fields ) . ') VALUES (' .
			implode( ',', $placeholders ) . ')';

		return $this->insert( $sql, $vals );
	}

	/**
	 * @param array $answers Updated user data
	 * @param int $id User id
	 * @return bool True if update suceeded, false otherwise
	 */
	public function updateUserInfo( $answers, $id ) {
		$fields = array(
			'username', 'email', 'reviewer', 'isvalid', 'isadmin',
			'viewreports', 'blocked'
		);
		$placeholders = array();
		foreach ( $fields as $field ) {
			$placeholders[] = "{$field} = :{$field}";
		}

		$sql = self::concat(
			'UPDATE users SET',
			implode( ', ', $placeholders ),
			'WHERE id = :id'
		);
		$stmt = $this->dbh->prepare( $sql );
		$answers['id'] = $id;

		try {
			$this->dbh->beginTransaction();
			$stmt->execute( $answers );
			$this->dbh->commit();
			return true;

		} catch ( PDOException $e ) {
			$this->dbh->rollback();
			$this->logger->error( 'Failed to update user', array(
				'method' => __METHOD__,
				'exception' => $e,
				'sql' => $sql,
				'params' => $answers,
			) );
			return false;
		}
	}

	public function updatePassword( $oldpw, $newpw, $id, $force = null ) {
		if ( !$force ) {
			$res = $this->fetch(
				'SELECT password FROM users WHERE id = ?',
				array( $id )
			);

			if ( !Password::comparePasswordToHash( $oldpw, $res['password'] ) ) {
				// passsword doesn't match expected
				$this->logger->notice( 'Invalid old password; will not update', array(
					'method' => __METHOD__,
					'user' => $id,
				) );
				return false;
			}
		}

		$stmt = $this->dbh->prepare( 'UPDATE users SET password = ? WHERE id = ?' );
		try {
			$this->dbh->beginTransaction();
			$stmt->execute( array( Password::encodePassword( $newpw ), $id ) );
			$this->dbh->commit();
			$this->logger->notice( 'Changed password for user', array(
				'method' => __METHOD__,
				'user' => $id,
			) );
			// Invalidate any password reset token that may have been issued
			$this->updatePasswordResetHash( $id, null );
			return true;

		} catch ( PDOException $e ) {
			$this->dbh->rollback();
			$this->logger->error( 'Failed to update password for user', array(
				'method' => __METHOD__,
				'exception' => $e,
			) );
			return false;
		}
	}

	public function userIsBlocked( $id ) {
		$res = $this->query( "SELECT blocked FROM users WHERE id = ?", array( $id ) );
		return $res['blocked'];
	}

	/**
	 * Function to let user update their own details and to disable account
	 * @param integer $id User ID
	 * @param array $params Updated user data
	 */
	public function updateUserAccount( $id, $params ) {
		$fields = array(
			'username', 'email', 'blocked'
		);
		$placeholders = array();
		foreach ( $fields as $field ) {
			$placeholders[] = "{$field} = :{$field}";
		}

		if ( $params['blocked'] === '1' ) {
			$params['username'] = null;
			$params['email'] = null;
		}
		$params['id'] = $id;

		$sql = self::concat(
			'UPDATE users SET',
			implode( ', ', $placeholders ),
			'WHERE id = :id'
		);

		return $this->update( $sql, $params );
	}

	/**
	 * Generate password reset token(s) for the given email address.
	 *
	 * @param string $email Email address
	 * @return array (token, user) pairs; token === false on error
	 */
	public function createPasswordResetToken( $email ) {
		$ret = array();
		$users = $this->search( array(
			'email' => $email,
			'items' => 'all',
		) );
		foreach ( $users->rows as $user ) {
			$token = bin2hex( Password::getBytes( 16, true ) );
			$hash = hash( 'sha256', $token );
			if ( !$this->updatePasswordResetHash( $user['id'], $hash ) ) {
				$token = false;
			}
			$ret[] = array( $token, $user );
		}
		return $ret;
	}

	protected function updatePasswordResetHash( $id, $hash ) {
		$ret = false;
		$stmt = $this->dbh->prepare(
			'UPDATE users SET reset_hash = ?, reset_date = now() WHERE id = ?'
		);
		try {
			$this->dbh->beginTransaction();
			$stmt->execute( array( $hash, $id ) );
			$this->dbh->commit();
			$this->logger->notice( 'Created reset token for user', array(
				'method' => __METHOD__,
				'user' => $id,
			) );
			$ret = true;

		} catch ( PDOException $e ) {
			$this->dbh->rollback();
			$this->logger->error(
				'Failed to update reset_hash for user',
				array(
					'method' => __METHOD__,
					'exception' => $e,
			) );
		}
		return $ret;
	}

	/**
	 * Validate a user's password reset token.
	 *
	 * @param int $id User id
	 * @param string $token Reset token
	 * @return bool
	 */
	public function validatePasswordResetToken( $id, $token ) {
		$hash = hash( 'sha256', $token );
		$row = $this->fetch(
			'SELECT reset_hash, reset_date FROM users WHERE id = ?',
			array( $id )
		);
		return $row &&
			Password::hashEquals( $row['reset_hash'], $hash ) &&
			// Tokens are only good for 48 hours
			( time() - strtotime( $row['reset_date'] ) ) < 172800;
	}

	/**
	 * Reset a user's password after validating the reset token.
	 *
	 * @param int $id User id
	 * @param string $token Reset token
	 * @param string $pass New password
	 * @return bool
	 */
	public function resetPassword( $id, $token, $pass ) {
		$ret = false;
		if ( $this->validatePasswordResetToken( $id, $token ) ) {
			$ret = $this->updatePassword( null, $pass, $id, true );
			if ( $ret ) {
				// Consume token if change was successful
				$this->updatePasswordResetHash( $id, null );
			}
		}
		return $ret;
	}
}
