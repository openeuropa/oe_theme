<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\Behat;

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\user\Entity\Role;

/**
 * Preserve anonymous permissions for specifically tagged scenarios.
 */
class PreserveAnonymousPermissionsContext extends RawDrupalContext {

  /**
   * Store anonymous user permission list.
   *
   * @var array
   */
  protected $anonymousPermissions = [];

  /**
   * Backup anonymous permissions.
   *
   * @BeforeScenario @preserve_anonymous_permissions
   */
  public function backupAnonymousPermissions(): void {
    $this->anonymousPermissions = Role::load(Role::ANONYMOUS_ID)->getPermissions();
  }

  /**
   * Restore anonymous permissions, if changed.
   *
   * @AfterScenario @preserve_anonymous_permissions
   */
  public function restoreAnonymousPermissions(): void {
    if (empty($this->anonymousPermissions)) {
      return;
    }

    // Revoke all current permissions.
    $role = Role::load(Role::ANONYMOUS_ID);
    $permissions = $role->getPermissions();
    user_role_revoke_permissions(Role::ANONYMOUS_ID, $permissions);

    // Restore initial permission set by granting them.
    user_role_grant_permissions(Role::ANONYMOUS_ID, $this->anonymousPermissions);

    // Clears the static cache of DatabaseCacheTagsChecksum.
    \Drupal::service('cache_tags.invalidator')->resetCheckSums();
  }

}
