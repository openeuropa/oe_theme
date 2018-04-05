<?php

declare(strict_types = 1);

namespace Drupal\oe_theme\TaskRunner\Commands;

use Gitonomy\Git\Repository;
use OpenEuropa\TaskRunner\Commands\AbstractCommands;
use OpenEuropa\TaskRunner\Contract\ComposerAwareInterface;
use OpenEuropa\TaskRunner\Traits\ComposerAwareTrait;
use Robo\Collection\CollectionBuilder;
use Symfony\Component\Yaml\Yaml;

/**
 * Project release commands.
 */
class ReleaseCommands extends AbstractCommands implements ComposerAwareInterface {

  use ComposerAwareTrait;

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
   *
   * @option keep Whereas to keep the release directory or not.
   *
   * @aliases project:cr,pcr
   */
  public function createRelease(array $options = ['keep' => FALSE]): CollectionBuilder {
    $name = $this->composer->getProject();
    $archive = "$name.tar.gz";

    $tasks = [
      // Make sure we do not have a release directory yet.
      $this->taskFilesystemStack()->remove([$archive, $name]),

      // Get non-modified code using git archive.
      $this->taskGitStack()->exec(["archive", "HEAD", "-o $name.zip"]),
      $this->taskExtract("$name.zip")->to("$name"),

      // Copy git-ignored files and directories.
      $this->taskCopyDir(["css" => "$name/css"]),
      $this->taskCopyDir(["fonts" => "$name/fonts"]),
      $this->taskCopyDir(["images" => "$name/images"]),
      $this->taskCopyDir(["templates/components" => "$name/templates/components"]),
      $this->taskFilesystemStack()->copy("js/base.js", "$name/js/base.js", TRUE),

      // Remove tests and development tools.
      $this->taskFilesystemStack()->remove([
        "$name/sass",
        "$name/tests",
        "$name/src/TaskRunner",
        "$name/.editorconfig",
        "$name/.gitignore",
        "$name/.travis.yml",
        "$name/behat.yml.dist",
        "$name/docker-compose.yml",
        "$name/ecl-builder.config.js",
        "$name/grumphp.yml.dist",
        "$name/package.json",
        "$name/phpunit.xml.dist",
        "$name/runner.yml.dist",
      ]),

      // Append release notes to project info file.
      $this->taskWriteToFile("$name/$name.info.yml")
        ->append()
        ->text($this->getReleaseNote()),
    ];

    // Create archive.
    $tasks[] = $this->taskExecStack()->exec("tar -czf $archive $name");

    // Remove release directory, if not specified otherwise.
    if (!$options['keep']) {
      $tasks[] = $this->taskFilesystemStack()->remove($name);
    }

    return $this->collectionBuilder()->addTaskList($tasks);
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
   * @return string
   *   Release note.
   */
  private function getReleaseNote(): string {
    $timestamp = time();
    $date = date("Y-m-d", $timestamp);

    $info = [];
    $info['version'] = $this->getTag();
    $info['project'] = $this->composer->getProject();
    $info['datestamp'] = $timestamp;

    $note = "\n# Information added by OpenEuropa packaging script on $date\n";
    $note .= Yaml::dump($info);

    return $note;
  }

}
