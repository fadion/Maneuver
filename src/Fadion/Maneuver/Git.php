<?php namespace Fadion\Maneuver;

use Exception;


/**
 * Class Git
 *
 * Handles the reading of git repos, submodules
 * and other git commands.
 *
 * @package Fadion\Maneuver\Git
 * @author Fadion Dashi <jonidashi@gmail.com>
 * @author Baki Goxhaj <banago@gmail.com>
 * @licence MIT
 * @version 1.0
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
    protected $submodules = array();

    /**
     * @var array List of ignore files, read from config
     */
    protected $ignoredFiles = array();

    /**
     * Constructor
     *
     * @param null|string $repo
     * @param null|array $rollback
     * @throws Exception if not a Git repository
     */
    public function __construct($repo = null, $rollback)
    {
        $this->revision = 'HEAD';

        // A rollback is called, so set the specified
        // commit, or to HEAD^ (one commit before).
        if (isset($rollback))
        {
            $this->revision = ($rollback['commit']) ? $rollback['commit'] : 'HEAD^';
        }

        $this->repo = (isset($repo)) ? rtrim($repo, '/') : getcwd();

        // Check if it's a git repository.
        if (! file_exists("$this->repo/.git"))
        {
            throw new Exception("'$this->repo' is not a Git repository.");
        }

        $this->subModules();

        // Load the ignored files array from config.
        $ignored = app()->config['maneuver::config.ignored'];

        if ($ignored)
        {
            foreach ($ignored as $file)
            {
                $this->ignore($file);
            }
        }
    }

    /**
     * Checks submodules
     * 
     * @return void
     */
    protected function subModules()
    {
        $repo = $this->repo;
        $command = "git --git-dir=\"$repo/.git\" --work-tree=\"$repo\" submodule status";
        $output = array();

        exec(escapeshellcmd($command), $output);

        if ($output)
        {
            foreach ($output as $line)
            {
                $line = explode(' ', trim($line));
                $this->submodules[] = array('revision' => $line[0], 'name' => $line[1], 'path' => $repo.'/'.$line[1]);
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
     * @return void
     */
    protected function checkSubSubmodules($repo, $name)
    {
        $command = "git --git-dir=\"$repo/.git\" --work-tree=\"$repo\" submodule foreach git submodule status";
        $output = array();

        exec(escapeshellcmd($command), $output);

        if ($output)
        {
            foreach ($output as $line)
            {
                $line = explode(' ', trim($line));

                if (trim($line[0]) == 'Entering') continue;

                $this->submodules[] = array('revision' => $line[0], 'name' => $name.'/'.$line[1], 'path' => $repo.'/'.$name.'/'.$line[1]);
                $this->ignoredFiles[] = $name.'/'.$line[1];
            }
        }
    }

    /**
     * Gets files from the diff between revisions
     *
     * @param $revision
     * @return mixed
     */
    public function diff($revision)
    {
        if ($this->revision == 'HEAD')
        {
            $command = "git --git-dir=\"$this->repo/.git\" --work-tree=\"$this->repo\" diff --name-status {$revision}...{$this->revision}";
        }
        else
        {
            $command = "git --git-dir=\"$this->repo/.git\" --work-tree=\"$this->repo\" diff --name-status {$revision}... {$this->revision}";
        }

        exec(escapeshellcmd($command), $output);

        return $output;
    }

    /**
     * Gets files from work tree
     *
     * @return mixed
     */
    public function files()
    {
        $command = "git --git-dir=\"$this->repo/.git\" --work-tree=\"$this->repo\" ls-files";
        exec(escapeshellcmd($command), $output);

        return $output;
    }

    /**
     * Gets the current revision hash
     *
     * @return string
     */
    public function localRevision()
    {
        $command = "git --git-dir=\"$this->repo/.git\" --work-tree=\"$this->repo\" rev-parse HEAD";

        return exec(escapeshellcmd($command));
    }

    /**
     * Rolls back revision
     *
     * @return string
     */
    public function rollback()
    {
        $command = 'git --git-dir="'.$this->repo.'/.git" --work-tree="'.$this->repo.'" checkout '.$this->revision;

        return exec(escapeshellcmd($command));
    }

    /**
     * Reverts to master
     *
     * @return string
     */
    public function revertToMaster()
    {
        $command = 'git --git-dir="'.$this->repo.'/.git" --work-tree="'.$this->repo.'" checkout master';

        return exec(escapeshellcmd($command));
    }

    /**
     * Adds files to ignore list
     *
     * @param $file
     * @return void
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