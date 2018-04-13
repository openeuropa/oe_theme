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
   * Working directory.
   *
   * @var string
   */
  private $workingDir;

  /**
   * ReleaseCommands constructor.
   */
  public function __construct() {
    // @todo: Inject working directory as a dependency.
    // @todo: Add a proper inflector to Task Runner or to Robo.
    $this->workingDir = realpath(__DIR__ . '/../../..');
  }

  /**
   * Create project release.
   *
   * The command will create an archive file containing the release package.
   *
   * @param array $options
   *   Command options.
   *
   * @return \Robo\Collection\CollectionBuilder
   *   Collection builder.
   *
   * @command project:create-release
   *
   * @option keep Whereas to keep the temporary release directory or not.
   *
   * @aliases project:cr,pcr
   */
  public function createRelease(array $options = ['keep' => FALSE]): CollectionBuilder {
    if ($this->getRepository($this->workingDir)->isHeadDetached()) {
      throw new \RuntimeException('Release cannot be generated in detached state.');
    }

    $name = $this->composer->getProject();
    $version = $this->getVersionString();
    $archive = "$name-$version.tar.gz";
    $note = $this->getReleaseNote($name, $version, time());

    $tasks = [
      // Make sure we do not have a release directory yet.
      $this->taskFilesystemStack()->remove([$archive, $name]),

      // Get non-modified code using git archive.
      $this->taskGitStack()->exec(["archive", "HEAD", "-o $name.zip"]),
      $this->taskExtract("$name.zip")->to("$name"),
      $this->taskFilesystemStack()->remove("$name.zip"),

      // Copy git-ignored files and directories.
      $this->taskCopyDir(["css" => "$name/css"]),
      $this->taskCopyDir(["fonts" => "$name/fonts"]),
      $this->taskCopyDir(["images" => "$name/images"]),
      $this->taskCopyDir(["templates/components" => "$name/templates/components"]),
      $this->taskFilesystemStack()->copy("js/base.js", "$name/js/base.js", TRUE),

      // Remove tests and development tools.
      $this->taskFilesystemStack()->remove([
        "$name/sass",
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
      $this->taskWriteToFile("$name/$name.info.yml")->append()->text($note),
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
   * Return version string for current HEAD: either a tag or local branch name.
   *
   * @return string
   *   Tag name or empty string if none set.
   */
  private function getVersionString(): string {
    $repository = $this->getRepository($this->workingDir);

    // Get commit has from current HEAD.
    $hash = $repository->getHead()->getCommitHash();

    // Resolve tags for current HEAD.
    // In case of multiple tags per commit take the latest one.
    $tags = $repository->getReferences()->resolveTags($hash);
    $tag = end($tags);

    // Resolve local branch name for current HEAD.
    $branches = array_filter($repository->getReferences()->resolveBranches($hash), function ($branch) {
      return $branch->isLocal();
    });
    $branch = reset($branches);

    return ($tag !== FALSE) ? $tag->getName() : $branch->getName();
  }

  /**
   * Get current Git repository.
   *
   * @param string $path
   *   Path to Git repository.
   *
   * @return \Gitonomy\Git\Repository
   *   Repository object.
   */
  private function getRepository(string $path): Repository {
    return new Repository($path);
  }

  /**
   * Build release note to be appended to project's info file.
   *
   * @return string
   *   Release note.
   */
  private function getReleaseNote(string $project, string $version, int $timestamp): string {
    $info = [];
    $info['version'] = $version;
    $info['project'] = $project;
    $info['datestamp'] = $timestamp;

    $date = date("Y-m-d", $timestamp);
    $note = "\n# Information added by OpenEuropa packaging script on $date\n";
    $note .= Yaml::dump($info);

    return $note;
  }

}
