<?php

declare(strict_types=1);

namespace Drupal\oe_theme_commands\TaskRunner\Commands;

use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use Symfony\Component\Console\Input\InputOption;

/**
 * Defines ec-europa/toolkit commands create a release artifact.
 *
 * The artifact can be used by openeuropa/composer-artifacts.
 */
class ReleaseCommands extends AbstractCommands {

  /**
   * Create a release for the current project.
   *
   * This command creates a .tag.gz archive for the current project named as
   * follow:
   *
   * [PROJECT-NAME]-[CURRENT-TAG].[file-format]
   *
   * Where [file-format] can be tar.gz or zip, in case --zip option is used.
   *
   * @command toolkit:release:create-archive
   *
   * @option name Project name.
   * @option tag  Release tag, will override current repository tag.
   * @option keep Whereas to keep the temporary release directory or not.
   * @option zip Create archive in zip file format.
   */
  public function createRelease(
    array $options = [
      'name' => InputOption::VALUE_REQUIRED,
      'tag' => InputOption::VALUE_REQUIRED,
      'keep' => FALSE,
      'zip' => FALSE,
    ],
  ) {
    $file_format = $options['zip'] ? 'zip' : 'tar.gz';
    $name = $options['name'];
    $version = $options['tag'];
    $archive = "$name-$version." . $file_format;

    $tasks = [
      // Make sure we do not have a release directory yet.
      $this->taskFilesystemStack()->remove([$archive, $name]),

      // Get non-modified code using git archive.
      $this->taskGitStack()->exec(["archive", "HEAD", "-o $name.zip"]),
      $this->taskExtract("$name.zip")->to("$name"),
      $this->taskFilesystemStack()->remove("$name.zip"),
    ];

    // Create archive.
    if ($options['zip']) {
      $tasks[] = $this->taskExecStack()->exec("zip -r $archive $name");
    }
    else {
      $tasks[] = $this->taskExecStack()->exec("tar -czf $archive $name");
    }
    // Remove release directory, if not specified otherwise.
    if (!$options['keep']) {
      $tasks[] = $this->taskFilesystemStack()->remove($name);
    }

    return $this->collectionBuilder()->addTaskList($tasks);
  }

}
