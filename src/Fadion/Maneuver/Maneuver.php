<?php namespace Fadion\Maneuver;

use Banago\Bridge\Bridge;
use Exception;

/**
 * Class Maneuver
 */
class Maneuver {

    /**
     * @var null|string $optServer
     */
    protected $optServer = null;

    /**
     * @var null|string $optRepo
     */
    protected $optRepo = null;

    /**
     * @var null|string $optRollback
     */
    protected $optRollback = null;

    /**
     * @var null|string $optSyncCommit
     */
    protected $optSyncCommit = null;

    /**
     * @var string $mode
     */
    protected $mode;

    /**
     * @const MODE_DEPLOY
     */
    const MODE_DEPLOY = 'deploy';

    /**
     * @const MODE_LIST
     */
    const MODE_LIST = 'list';

    /**
     * @const MODE_ROLLBACK
     */
    const MODE_ROLLBACK = 'rollback';

    /**
     * @const MODE_SYNC
     */
    const MODE_SYNC = 'sync';

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct(Array $options = array())
    {
        // Merge options with a set of null defaults,
        // so parameters can be omitted safely.
        $defaults = array('server' => null, 'repo' => null, 'rollback' => null, 'sync' => null);
        $options = array_merge($defaults, $options);

        $this->optServer = $options['server'];
        $this->optRepo = $options['repo'];
        $this->optRollback = $options['rollback'];
        $this->optSyncCommit = $options['sync'];
    }

    /**
     * Sets mode
     *
     * @param string $mode
     */
    public function mode($mode)
    {
        $this->mode = $mode;
    }

    /**
     * Starts the Maneuver
     */
    public function start()
    {
        // Get server list.
        $connection = new Connection($this->optServer);
        $servers = $connection->servers();

        $rollback = null;

        // When in rollback mode, get the commit.
        if ($this->mode == self::MODE_ROLLBACK) {
            $rollback = array('commit' => $this->optRollback);
        }

        // Init the Git object with the repo and
        // rollback option.
        $git = new Git($this->optRepo, $rollback);

        // There may be one or more servers, but in each
        // case it's build as an array.
        foreach ($servers as $name => $credentials) {
            try {
                // Connect to the server using the selected
                // scheme and options.
                $bridge = new Bridge(http_build_url('', $credentials));
            }
            catch (Exception $e) {
                print "Oh snap: {$e->getMessage()}";
                continue;
            }

            $deploy = new Deploy($git, $bridge, $credentials);

            print "\r\n+ --------------- § --------------- +";
            print "\n» Server: $name";

            // Sync mode. Write revision and close the
            // connection, so no other files are uploaded.
            if ($this->mode == self::MODE_SYNC) {
                $deploy->setSyncCommit($this->optSyncCommit);
                $deploy->writeRevision();

                print "\n √ Synced local revision file to remote";
                print "\n+ --------------- √ --------------- +\r\n";

                continue;
            }

            // Rollback to the specified commit.
            if ($this->mode == self::MODE_ROLLBACK) {
                print "\n« Rolling back ";
                $git->rollback();
            }

            $dirtyRepo = $this->push($deploy);
            $dirtySubmodules = false;

            // Check if there are any submodules.
            if ($git->getSubModules()) {
                foreach ($git->getSubModules() as $submodule) {
                    // Change repo.
                    $git->setRepo($submodule['path']);

                    // Set submodule name.
                    $deploy->setIsSubmodule($submodule['name']);

                    print "\n» Submodule: " . $submodule['name'];

                    $dirtySubmodules = $this->push($deploy);
                }
            }

            // Files are uploaded or deleted, for the main
            // repo or submodules.
            if (($dirtyRepo or $dirtySubmodules)) {
                if ($this->mode == self::MODE_DEPLOY or $this->mode == self::MODE_ROLLBACK) {
                    // Write latest revision to server.
                    $deploy->writeRevision();
                }
            }
            else {
                print "\n» Nothing to do.";
            }

            print "\n+ --------------- √ --------------- +\r\n";

            // On rollback mode, revert to master.
            if ($this->mode == self::MODE_ROLLBACK) {
                $git->revertToMaster();
            }
        }
    }

    /**
     * Handles the upload and delete processes
     *
     * @param \Fadion\Maneuver\Deploy $deploy
     * @return bool
     */
    public function push($deploy)
    {
        // Compare local revision to the remote one, to
        // build files to upload and delete.
        $message = $deploy->compare();
        print $message;
        print "\n+ --------------- + --------------- +";

        $dirty = false;

        $filesToUpload = $deploy->getFilesToUpload();
        $filesToDelete = $deploy->getFilesToDelete();

        if ($filesToUpload) {
            foreach ($filesToUpload as $file) {
                // On list mode, just print the file.
                if ($this->mode == self::MODE_LIST) {
                    print "\n√ \033[0;37m{$file}\033[0m \033[0;32mwill be uploaded\033[0m";
                    continue;
                }

                $output = $deploy->upload($file);

                // An upload procedure may have more than one
                // output message (uploaded file, created dir, etc).
                foreach ($output as $message) {
                    print "\n" . $message;
                }
            }
        }

        if ($filesToDelete) {
            foreach ($filesToDelete as $file) {
                // On list mode, just print the file.
                if ($this->mode == self::MODE_LIST) {
                    print "\n× \033[0;37m{$file}\033[0m \033[0;31mwill be removed\033[0m";
                    continue;
                }

                print "\n" . $deploy->delete($file);
            }
        }

        // Files were uploaded or deleted, so mark
        // it as dirty.
        if ($filesToUpload or $filesToDelete) {
            $dirty = true;
        }

        return $dirty;
    }

}
