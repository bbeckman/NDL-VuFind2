<?php
namespace FinnaConsole\Module\Configuration;

$config = [
    'controllers' => [
        'factories' => [
            'util' => 'FinnaConsole\Controller\Factory::getUtilController',
        ]
    ],
    'service_manager' => [
        'factories' => [
            'VuFind\HMAC' => 'VuFind\Service\Factory::getHMAC',
            'Finna\AccountExpirationReminders' => 'FinnaConsole\Service\Factory::getAccountExpirationReminders',
            'Finna\DueDateReminders' => 'FinnaConsole\Service\Factory::getDueDateReminders',
            'Finna\EncryptCatalogPasswords' => 'FinnaConsole\Service\Factory::getEncryptCatalogPasswords',
            'Finna\ExpireUsers' => 'FinnaConsole\Service\Factory::getExpireUsers',
            'Finna\OnlinePaymentMonitor' => 'FinnaConsole\Service\Factory::getOnlinePaymentMonitor',
            'Finna\ScheduledAlerts' => 'FinnaConsole\Service\Factory::getScheduledAlerts',
            'Finna\UpdateSearchHashes' => 'FinnaConsole\Service\Factory::getUpdateSearchHashes',
            'Finna\VerifyRecordLinks' => 'FinnaConsole\Service\Factory::getVerifyRecordLinks',
            'Finna\VerifyResourceMetadata' => 'FinnaConsole\Service\Factory::getVerifyResourceMetadata'
        ]
    ]
];

$routes = [
    'util/due_date_reminders' => 'util due_date_reminders <vufind_dir> <view_dir>',
    'util/encrypt_catalog_passwords' => 'util encrypt_catalog_passwords Y',
    'util/expire_finna_cache' => 'util expire_finna_cache [--help|-h] [--batch=] [--sleep=] [<daysOld>]',
    'util/expire_users' => 'util expire_users <days>',
    'util/online_payment_monitor' => 'util online_payment_monitor <expire_hours> <from_email> <report_interval_hours>',
    'util/scheduled_alerts' => 'util scheduled_alerts <view_base_directory> <VuFind_local_configuration_directory>',
    'util/update_search_hashes' => 'util update_search_hashes Y',
    'util/verify_record_links' => 'util verify_record_links',
    'util/verify_resource_metadata' => 'util verify_resource_metadata'
];

$routeGenerator = new \VuFindConsole\Route\RouteGenerator();
$routeGenerator->addRoutes($config, $routes);

return $config;
