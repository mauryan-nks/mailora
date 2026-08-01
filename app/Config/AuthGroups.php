<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Config;

use CodeIgniter\Shield\Config\AuthGroups as ShieldAuthGroups;

class AuthGroups extends ShieldAuthGroups
{
    /**
     * --------------------------------------------------------------------
     * Default Group
     * --------------------------------------------------------------------
     * The group that a newly registered user is added to.
     */
    public string $defaultGroup = 'customer';

    /**
     * --------------------------------------------------------------------
     * Groups
     * --------------------------------------------------------------------
     * An associative array of the available groups in the system, where the keys
     * are the group names and the values are arrays of the group info.
     *
     * Whatever value you assign as the key will be used to refer to the group
     * when using functions such as:
     *      $user->addGroup('superadmin');
     *
     * @var array<string, array<string, string>>
     *
     * @see https://codeigniter4.github.io/shield/quick_start_guide/using_authorization/#change-available-groups for more info
     */
    public array $groups = [
        'superadmin' => [
            'title'       => 'Super Admin',
            'description' => 'Complete control of the site.',
        ],
        'platform_admin' => [
            'title'       => 'Platform Admin',
            'description' => 'Full control over the platform and every tenant.',
        ],
        'admin_team' => [
            'title'       => 'Admin Team',
            'description' => 'Platform staff delegated by an administrator.',
        ],
        'reseller' => [
            'title'       => 'Reseller',
            'description' => 'Owns a white-label portal, team and customers.',
        ],
        'reseller_team' => [
            'title'       => 'Reseller Team',
            'description' => 'Staff delegated by a reseller.',
        ],
        'customer' => [
            'title'       => 'Customer / Company',
            'description' => 'Uses the product in an isolated workspace.',
        ],
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions
     * --------------------------------------------------------------------
     * The available permissions in the system.
     *
     * If a permission is not listed here it cannot be used.
     */
    public array $permissions = [
        'workspace.settings' => 'Can manage workspace and branding',
        'team.manage'        => 'Can invite and manage team members',
        'contacts.manage'    => 'Can create, import and edit contacts',
        'campaigns.manage'   => 'Can create, test and schedule campaigns',
        'analytics.view'     => 'Can view analytics and reports',
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions Matrix
     * --------------------------------------------------------------------
     * Maps permissions to groups.
     *
     * This defines group-level permissions.
     */
    public array $matrix = [
        'superadmin' => [
            '*',
        ],
        'platform_admin' => [
            'workspace.*', 'team.*', 'contacts.*', 'campaigns.*', 'analytics.*',
        ],
        'admin_team' => [
            'workspace.settings', 'team.manage', 'contacts.manage', 'campaigns.manage', 'analytics.view',
        ],
        'reseller' => [
            'workspace.*', 'team.*', 'contacts.*', 'campaigns.*', 'analytics.*',
        ],
        'reseller_team' => [
            'team.manage', 'contacts.manage', 'campaigns.manage', 'analytics.view',
        ],
        'customer' => [
            'workspace.settings', 'team.manage', 'contacts.manage', 'campaigns.manage', 'analytics.view',
        ],
    ];
}
