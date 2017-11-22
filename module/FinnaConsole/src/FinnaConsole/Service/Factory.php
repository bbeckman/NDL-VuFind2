<?php
/**
 * Factory for various top-level VuFind services.
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
 * @package  Service
 * @author   Samuli Sillanpää <samuli.sillanpaa@helsinki.fi>
 * @author   Ere Maijala <ere.maijala@helsinki.fi>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */
namespace FinnaConsole\Service;

use Zend\Console\Console;
use Zend\ServiceManager\ServiceManager;

/**
 * Factory for various top-level VuFind services.
 *
 * @category VuFind
 * @package  Service
 * @author   Samuli Sillanpää <samuli.sillanpaa@helsinki.fi>
 * @author   Ere Maijala <ere.maijala@helsinki.fi>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */
class Factory
{
    /**
     * Construct the console service for reminding on expiring user accounts
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return \FinnaConsole\Service\AccountExpirationReminders
     */
    public static function getAccountExpirationReminders(ServiceManager $sm)
    {
        $table = $sm->get('VuFind\DbTablePluginManager')->get('User');
        $renderer = $sm->get('ViewRenderer');
        $configReader = $sm->get('VuFind\Config');
        $translator = $sm->get('VuFind\Translator');

        return new AccountExpirationReminders(
            $table, $renderer, $configReader, $translator, $sm
        );
    }

    /**
     * Construct the console service for sending due date reminders.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return \FinnaConsole\Service\DueDateReminder
     */
    public static function getDueDateReminders(ServiceManager $sm)
    {
        $tableManager = $sm->get('VuFind\DbTablePluginManager');
        $userTable = $tableManager->get('user');
        $dueDateReminderTable = $tableManager->get('due-date-reminder');

        $catalog = \Finna\Service\Factory::getILSConnection($sm);
        $configReader = $sm->get('VuFind\Config');
        $renderer = $sm->get('ViewRenderer');
        $loader = $sm->get('VuFind\RecordLoader');
        $hmac = $sm->get('VuFind\HMAC');
        $translator = $sm->get('VuFind\Translator');

        return new DueDateReminders(
            $userTable, $dueDateReminderTable, $catalog,
            $configReader, $renderer, $loader, $hmac, $translator, $sm
        );
    }

    /**
     * Construct the console service for encrypting catalog passwords.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return \FinnaConsole\Service\EncryptCatalogPasswords
     */
    public static function getEncryptCatalogPasswords(ServiceManager $sm)
    {
        $table = $sm->get('VuFind\DbTablePluginManager')->get('User');
        $config = $sm->get('VuFind\Config')->get('config');

        return new EncryptCatalogPasswords($table, $config);
    }

    /**
     * Construct the console service for anonymizing expired users accounts.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return \FinnaConsole\Service\ExpireUsers
     */
    public static function getExpireUsers(ServiceManager $sm)
    {
        $table = $sm->get('VuFind\DbTablePluginManager')->get('User');

        return new ExpireUsers($table);
    }

    /**
     * Construct the console service for sending scheduled alerts.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return \FinnaConsole\Service\ScheduledAlerts
     */
    public static function getOnlinePaymentMonitor(ServiceManager $sm)
    {
        $catalog = \Finna\Service\Factory::getILSConnection($sm);
        $tableManager = $sm->get('VuFind\DbTablePluginManager');
        $transactionTable = $tableManager->get('transaction');
        $userTable = $tableManager->get('user');
        $configReader = $sm->get('VuFind\Config');
        $mailer = $sm->get('VuFind\Mailer');
        $viewManager = $sm->get('viewmanager');
        $viewRenderer = $sm->get('ViewRenderer');

        return new OnlinePaymentMonitor(
            $catalog, $transactionTable, $userTable, $configReader, $mailer,
            $viewManager, $viewRenderer
        );
    }

    /**
     * Construct the console service for sending scheduled alerts.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return \FinnaConsole\Service\ScheduledAlerts
     */
    public static function getScheduledAlerts(ServiceManager $sm)
    {
        return new ScheduledAlerts($sm);
    }

    /**
     * Construct the console service for updating search hashes.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return \FinnaConsole\Service\UpdateSearchHashes
     */
    public static function getUpdateSearchHashes(ServiceManager $sm)
    {
        $table = $sm->get('VuFind\DbTablePluginManager')->get('Search');
        $manager = $sm->get('VuFind\SearchResultsPluginManager');
        return new UpdateSearchHashes($table, $manager);
    }

    /**
     * Construct the console service for verifying record links.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return \FinnaConsole\Service\VerifyRecordLinks
     */
    public static function getVerifyRecordLinks(ServiceManager $sm)
    {
        $commentsTable = $sm->get('VuFind\DbTablePluginManager')
            ->get('Comments');
        $commentsRecordTable = $sm->get('VuFind\DbTablePluginManager')
            ->get('CommentsRecord');

        $searchRunner = $sm->get('VuFind\SearchRunner');

        return new VerifyRecordLinks(
            $commentsTable, $commentsRecordTable, $searchRunner
        );
    }

    /**
     * Construct the console service for verifying resource metadata.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return \FinnaConsole\Service\VerifyResourceMetadata
     */
    public static function getVerifyResourceMetadata(ServiceManager $sm)
    {
        $resourceTable = $sm->get('VuFind\DbTablePluginManager')
            ->get('Resource');
        $dateConverter = $sm->get('VuFind\DateConverter');
        $recordLoader = $sm->get('VuFind\RecordLoader');

        return new VerifyResourceMetadata(
            $resourceTable, $dateConverter, $recordLoader
        );
    }
}
