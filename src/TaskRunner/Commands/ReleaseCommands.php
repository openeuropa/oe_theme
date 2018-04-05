<?php

declare(strict_types = 1);

namespace Drupal\oe_theme\TaskRunner\Commands;

use Gitonomy\Git\Repository;
use OpenEuropa\TaskRunner\Commands\AbstractCommands;
use Robo\Collection\CollectionBuilder;

/**
 * Project release commands.
 */
class ReleaseCommands extends AbstractCommands {

  /**
   * Create project release.
   *
   * Release project in given directory, suitable for use on production.
   * It will excludes all tests and development tools.
   *
   * @param array $options
   *   Command options.
   *
   * @return \Robo\Collection\CollectionBuilder
   *   Collection builder.
   *
   * @command project:create-release
   * @option destination Release destination directory.
   * @aliases project:cr,pcr
   */
  public function release(array $options = [
    'destination' => 'release',
  ]): CollectionBuilder {
    $destination = $options['destination'];

    return $this->collectionBuilder()->addTaskList([
      // Make sure we do not have a release directory yet.
      $this->taskFilesystemStack()->remove("$destination"),

      // Get non-modified code using git archive.
      $this->taskGitStack()->exec(["archive", "HEAD", "-o $destination.zip"]),
      $this->taskExtract("$destination.zip")->to("$destination"),

      // Copy git-ignored files and directories.
      $this->taskCopyDir(["css" => "$destination/css"]),
      $this->taskCopyDir(["fonts" => "$destination/fonts"]),
      $this->taskCopyDir(["images" => "$destination/images"]),
      $this->taskCopyDir(["templates/components" => "$destination/templates/components"]),
      $this->taskFilesystemStack()->copy("js/base.js", "$destination/js/base.js", TRUE),

      // Remove tests and development tools.
      $this->taskFilesystemStack()->remove([
        "$destination/tests",
        "$destination/src/TaskRunner",
        "$destination/.editorconfig",
        "$destination/.gitignore",
        "$destination/.travis.yml",
        "$destination/behat.yml.dist",
        "$destination/docker-compose.yml",
        "$destination/ecl-builder.config.js",
        "$destination/grumphp.yml.dist",
        "$destination/package.json",
        "$destination/phpunit.xml.dist",
        "$destination/runner.yml.dist",
      ]),

      // Append release notes to project info file.
      $this->taskWriteToFile("$destination/oe_theme.info.yml")
        ->append()
        ->lines($this->getReleaseNote()),
    ]);
  }

  /**
   * Return latest tag on current branch.
   *
   * @return string
   *   Tag name or empty string if none set.
   */
  private function getTag(): string {
    $repository = new Repository(__DIR__ . '/../../..');

    /** @var \Gitonomy\Git\Reference\Tag[] $tags */
    $tags = $repository->getReferences()->getTags();
    if (!empty($tags)) {
      // In case of multiple tags on the same commit take the last one.
      $tag = array_pop($tags);
      return $tag->getName();
    }

    return '';
  }

  /**
   * Build release note to be appended to project's info file.
   *
   * @return array
   *   Release note lines as an array.
   */
  private function getReleaseNote(): array {
    $tag = $this->getTag();
    $timestamp = time();
    $date = date("Y-m-d", $timestamp);

    $lines = [];
    $lines[] = "";
    $lines[] = "# Information added by OpenEuropa packaging script on $date";
    $lines[] = "timestamp: $timestamp";
    if (!empty($tag)) {
      $lines[] = "version: $tag";
    }

    return $lines;
  }

}
