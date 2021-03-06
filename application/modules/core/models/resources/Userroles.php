<?php
/**
 * CORE Conference Manager
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.terena.org/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to webmaster@terena.org so we can send you a copy immediately.
 *
 * @copyright  Copyright (c) 2011 TERENA (http://www.terena.org)
 * @license    http://www.terena.org/license/new-bsd     New BSD License
 * @revision   $Id$
 */

/** 
 *
 * @package Core_Resource
 * @author Christian Gijtenbeek <gijtenbeek@terena.org>
 */
class Core_Resource_Userroles extends TA_Model_Resource_Db_Table_Abstract
{

	protected $_name = 'user_role';

	protected $_primary = 'user_role_id';

	public function init() {}

	/**
	 * Gets item by primary key
	 * @return object Zend_Db_Table_Row
	 */
	public function getItemById($id)
	{
		return $this->find( (int)$id )->current();
	}

	/**
	 * returns item based on id values
	 *
	 * @param	array	$data	user_id and role_id values
	 * @return	object	Zend_Db_Table_Row
	 */
	public function getItemByValues(array $data)
	{
		return $this->fetchRow(
					$this->select()
					->where('user_id = ?', $data['user_id'])
					->where('role_id = ?', $data['role_id'])
				);
	}

	/**
	 * Save rows to the database. (insert or update)
	 *
	 * @param 	array 	$values
	 * @return	Zend_Db_Statement_Pdo on success or void if nothing is inserted
	 */
	public function saveRows($values = array())
	{
		$db = $this->getAdapter();

		// get current user_id/role_id combinations
		$currentValues = $db->fetchCol(
			$this->select()
			->from($this->_name, array('user_id'))
			->where('user_id IN (?)', $values['user_id'])
			->where('role_id = ?', $values['role_id'])
		);

		foreach ($values['user_id'] as $val) {
			if (!in_array($val, $currentValues)) {
				// using $val as key keeps the entries unique (in case one user submitted multiple papers)
				$insertValues[$val] = '('. $val . ',' . $values['role_id'] .')';
			}
		}
		
		// only insert users if they are not already inserted
		if (isset($insertValues)) {
			$query = "INSERT INTO " . $this->_name . " (user_id, role_id) VALUES ".implode(',', $insertValues);
			return $db->query($query);
		}
	}

}