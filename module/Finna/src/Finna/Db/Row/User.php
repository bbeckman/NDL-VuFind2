<?php
/**
 * Row Definition for user
 *
 * PHP version 5
 *
 * Copyright (C) The National Library of Finland 2015-2016.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category VuFind
 * @package  Db_Row
 * @author   Samuli Sillanpää <samuli.sillanpaa@helsinki.fi>
 * @author   Ere Maijala <ere.maijala@helsinki.fi>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org   Main Site
 */
namespace Finna\Db\Row;

/**
 * Row Definition for user
 *
 * @category VuFind
 * @package  Db_Row
 * @author   Samuli Sillanpää <samuli.sillanpaa@helsinki.fi>
 * @author   Ere Maijala <ere.maijala@helsinki.fi>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org   Main Site
 */
class User extends \VuFind\Db\Row\User
{
    use FinnaUserTrait;

    /**
     * Get all of the lists associated with this user.
     *
     * @return \Zend\Db\ResultSet\AbstractResultSet
     */
    public function getLists()
    {
        $lists = parent::getLists();

        // Sort lists by id
        $listsSorted = [];
        foreach ($lists as $l) {
            $listsSorted[$l['id']] = $l;
        }
        ksort($listsSorted);

        return array_values($listsSorted);
    }

    /**
     * Get number of distinct user resources in all lists.
     *
     * @return int
     */
    public function getNumOfResources()
    {
        $resource = $this->getDbTable('Resource');
        $userResources = $resource->getFavorites(
            $this->id, null, null, null
        );
        return count($userResources);
    }

    /**
     * Anonymize user account by updating username to a random string
     * and setting other user object fields (besides id) to their default values.
     * User comments are preserved. Catalog accounts, due date reminders,
     * saved searches and lists are deleted.
     *
     * @return boolean True on success
     */
    public function anonymizeAccount()
    {
        $connection = $this->sql->getAdapter()->getDriver()->getConnection();

        if (!$connection) {
            return false;
        }

        try {
            $connection->beginTransaction();

            // Delete library cards
            $cards = $this->getLibraryCards();
            foreach ($cards as $card) {
                $card->delete();
            }

            // Todo: Delete due date reminders

            // Delete lists (linked user_resource objects cascade)
            $lists = $this->getLists();
            foreach ($lists as $list) {
                $list->delete($this);
            }

            // Delete saved searches
            $searchTable = $this->getDbTable('Search');
            $searches = $searchTable->getSearches(null, $this->id);
            foreach ($searches as $search) {
                $search->delete();
            }

            // Anonymize user object
            $this->username = 'deleted:' . uniqid();
            $this->password = '';
            $this->firstname = '';
            $this->lastname = '';
            $this->email = '';

            $this->cat_username = null;
            $this->cat_password = null;

            $this->college = '';
            $this->major = '';
            $this->home_library = '';

            $this->finna_due_date_reminder = 0;
            $this->finna_auth_method = null;

            $this->save();

            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            return false;
        }
        return true;
    }

    /**
     * Save ILS ID.
     *
     * @param string $catId Catalog ID to save.
     *
     * @return mixed        The output of the save method.
     * @throws \VuFind\Exception\PasswordSecurity
     */
    public function saveCatalogId($catId)
    {
        if (isset($this->config->Site->institution)) {
            $catId = $this->config->Site->institution . ":$catId";
        }
        return parent::saveCatalogId($catId);
    }

    /**
     * Updated saved language
     *
     * @param string $language New language
     *
     * @return void
     */
    public function updateFinnaLanguage($language)
    {
        $this->finna_language = $language;
        $this->save();
    }
}
