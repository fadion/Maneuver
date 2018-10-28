<?php namespace Fadion\Maneuver;

use Exception;


/**
 * Class Git
 *
 * Handles the reading of git repos, submodules
 * and other git commands.
 */
class Git
{
    /**
     * @var string Git revision
     */
    protected $revision;

    /**
     * @var string Git repository
     */
    protected $repo;

    /**
     * @var array List of submodules and subsubmodules
     */
    protected $submodules = [];

    /**
     * @var array List of ignore files, read from config
     */
    protected $ignoredFiles = [];

    /**
     * Constructor
     *
     * @param null|string $repo
     * @param array $rollback
     * @throws Exception if not a Git repository
     */
    public function __construct($repo = null, $rollback)
    {
        $this->revision = 'HEAD';

        // A rollback is called, so set the specified
        // commit, or to HEAD^ (one commit before).
        if (isset($rollback)) {
            $this->revision = ($rollback['commit']) ? $rollback['commit'] : 'HEAD^';
        }

        $this->repo = (isset($repo)) ? rtrim($repo, '/') : getcwd();

        // Check if it's a git repository.
        if (!file_exists("$this->repo/.git")) {
            throw new Exception("'$this->repo' is not a Git repository.");
        }

        $this->subModules();

        // Load the ignored files array from config.
        $ignored = config('maneuver.ignored');

        if ($ignored) {
            foreach ($ignored as $file) {
                $this->ignore($file);
            }
        }
    }

    /**
     * Runs a Git command.
     *
     * @param string $command
     * @param null|string $repoPath
     * @throws Exception if command fails
     * @return array
     */
    protected function command($command, $repoPath = null)
    {
        if (!$repoPath) {
            $repoPath = $this->repo;
        }

        $command = 'git --git-dir="' . $repoPath . '/.git" --work-tree="' . $repoPath . '" ' . $command;

        exec(escapeshellcmd($command), $output, $returnStatus);

        if ($returnStatus != 0) {
            throw new Exception("The following command was attempted but failed:\r\n$command");
        }

        return $output;
    }

    /**
     * Checks submodules
     *
     */
    protected function subModules()
    {
        $repo = $this->repo;
        $output = $this->command('submodule status');

        if ($output) {
            foreach ($output as $line) {
                $line = explode(' ', trim($line));
                $this->submodules[] = array(
                    'revision' => $line[0],
                    'name' => $line[1],
                    'path' => $repo . '/' . $line[1]
                );
                $this->ignoredFiles[] = $line[1];
                $this->checkSubSubmodules($repo, $line[1]);
            }
        }
    }

    /**
     * Checks submodules of submodules
     *
     * @param string $repo
     * @param string $name
     * @throws Exception
     */
    protected function checkSubSubmodules($repo, $name)
    {
        $output = $this->command('submodule foreach git submodule status');

        if ($output) {
            foreach ($output as $line) {
                $line = explode(' ', trim($line));

                if (trim($line[0]) == 'Entering') continue;

                $this->submodules[] = array(
                    'revision' => $line[0],
                    'name' => $name . '/' . $line[1],
                    'path' => $repo . '/' . $name . '/' . $line[1]
                );
                $this->ignoredFiles[] = $name . '/' . $line[1];
            }
        }
    }

    /**
     * Gets files from the diff between revisions
     *
     * @param $revision
     * @return mixed
     * @throws Exception
     */
    public function diff($revision)
    {
        if (!$revision) {
            return $this->command('ls-files');
        }

        return $this->command("diff --name-status --no-renames {$revision}... {$this->revision}");
    }

    /**
     * Gets files from work tree
     *
     * @return mixed
     * @throws Exception
     */
    public function files()
    {
        return $this->command('ls-files');
    }

    /**
     * Gets the current revision hash
     *
     * @return array
     * @throws Exception
     */
    public function localRevision()
    {
        return $this->command('rev-parse HEAD');
    }

    /**
     * Rolls back revision
     *
     * @return array
     * @throws Exception
     */
    public function rollback()
    {
        return $this->command("checkout {$this->revision}");
    }

    /**
     * Reverts to master
     *
     * @return array
     * @throws Exception
     */
    public function revertToMaster()
    {
        return $this->command('checkout master');
    }

    /**
     * Adds files to ignore list
     *
     * @param $file
     */
    protected function ignore($file)
    {
        $this->ignoredFiles[] = $file;
    }

    /**
     * Getter for $this->revision
     *
     * @return null|string
     */
    public function getRevision()
    {
        return $this->revision;
    }

    /**
     * Getter for $this->repo
     *
     * @return string
     */
    public function getRepo()
    {
        return $this->repo;
    }

    /**
     * Setter for $this->repo
     *
     * @param string $value
     * @return string
     */
    public function setRepo($value)
    {
        return $this->repo = $value;
    }

    /**
     * Getter for $this->ignoredFiles
     *
     * @return array
     */
    public function getIgnored()
    {
        return $this->ignoredFiles;
    }

    /**
     * Getter for $this->submodules
     *
     * @return array
     */
    public function getSubModules()
    {
        return $this->submodules;
    }

}