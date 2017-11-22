<?php
/**
 * Additional functionality for Finna records.
 *
 * PHP version 5
 *
 * Copyright (C) The National Library 2015-2016.
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
 * @package  RecordDrivers
 * @author   Samuli Sillanpää <samuli.sillanpaa@helsinki.fi>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:record_drivers Wiki
 */
namespace Finna\RecordDriver;

/**
 * Additional functionality for Finna records.
 *
 * @category VuFind
 * @package  RecordDrivers
 * @author   Samuli Sillanpää <samuli.sillanpaa@helsinki.fi>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:record_drivers Wiki
 */
trait FinnaRecord
{
    /**
     * Get inappropriate comments for this record reported by the given user.
     *
     * @param object $userId Reporter ID
     *
     * @return array
     */
    public function getInappropriateComments($userId)
    {
        $table = $this->getDbTable('CommentsInappropriate');
        return $table->getForRecord(
            $userId, $this->getUniqueID()
        );
    }

    /**
     * Get OpenURL parameters for a book section.
     *
     * @return array
     */
    protected function getBookSectionOpenUrlParams()
    {
        $params = $this->getBookOpenUrlParams();
        $params['rft.volume'] = $this->getContainerVolume();
        $params['rft.issue'] = $this->getContainerIssue();
        $params['rft.spage'] = $this->getContainerStartPage();
        unset($params['rft.title']);
        $params['rft.btitle'] = $this->getContainerTitle();
        $params['rft.atitle'] = $this->getTitle();

        return $params;
    }

    /**
     * Get OpenURL parameters for a journal.
     *
     * @return array
     */
    protected function getJournalOpenUrlParams()
    {
        $params = parent::getJournalOpenUrlParams();
        if ($objectId = $this->tryMethod('getSfxObjectId')) {
            $params['rft.object_id'] = $objectId;
        }
        return $params;
    }

    /**
     * Get saved time associated with this record in a user list.
     *
     * @param int $list_id List id
     * @param int $user_id List owner id
     *
     * @return timestamp
     */
    public function getListSavedDate($list_id, $user_id)
    {
        $db = $this->getDbTable('UserResource');
        $data = $db->getSavedData(
            $this->getUniqueId(), $this->getSourceIdentifier(), $list_id, $user_id
        );
        foreach ($data as $current) {
            return $current->saved;
        }
        return null;
    }
}
