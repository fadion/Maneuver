<?php namespace Fadion\Maneuver;

use Exception;

/**
 * Class Deploy
 *
 * Handles FTP connection and transfers.
 *
 * @package Fadion\Maneuver\Deploy
 * @author Fadion Dashi <jonidashi@gmail.com>
 * @author Baki Goxhaj <banago@gmail.com>
 * @licence MIT
 * @version 1.0
 */
class Deploy
{

    /**
     * @var \Fadion\Maneuver\Git
     */
    protected $git;

    /**
     * @var string Server credentials
     */
    protected $server;

    /**
     * @var resource FTP resource
     */
    protected $connection;

    /**
     * @var string Revision filename
     */
    protected $revisionFile = '.revision';

    /**
     * @var bool
     */
    protected $isSubmodule = false;

    /**
     * @var array List of ignored files
     */
    protected $ignoredFiles;

    /**
     * @var array List of files to upload
     */
    protected $filesToUpload;

    /**
     * @var array List of files to delete
     */
    protected $filesToDelete;

    /**
     * Constructor
     *
     * @param \Fadion\Maneuver\Git $git
     * @param $server
     */
    public function __construct(Git $git, $server)
    {
        $this->git = $git;
        $this->ignoredFiles = $git->getIgnored();
        $this->server = $server;
    }

    /**
     * Connects to FTP server
     *
     * @return void
     * @throws Exception if it can't connect to FTP server
     * @throws Exception if it can't login to FTP server
     * @throws Exception if it can't change FTP directory
     */
    public function connect()
    {
        $server = $this->server;

        // Make sure the path has a trailing slash.
        $server['path'] = rtrim($server['path'], '/').'/';

        // Make the connection.
        $connection = @ftp_connect($server['host'], $server['port']);

        if (! $connection)
        {
            throw new Exception("Couldn't connect to '{$server['host']}'.");
        }

        // Try logging in.
        if (! @ftp_login($connection, $server['username'], $server['password']))
        {
            throw new Exception("Couldn't login to '{$server['host']}' with user '{$server['username']}'");
        }

        // Set passive mode from connection config.
        ftp_pasv($connection, (bool) $server['passive']);

        // Try changing the directory to the one set
        // in the connection config.
        if (! ftp_chdir($connection, $server['path']))
        {
            throw new Exception("Couldn't change the FTP directory to '{$server['path']}'.");
        }

        $this->connection = $connection;
    }

    /**
     * Compares local revision to the remote one and
     * builds files to upload and delete
     *
     * @return string
     * @throws Exception if unknown git diff status
     */
    public function compare()
    {
        $remoteRevision = null;
        $tempFile = tmpfile();
        $filesToUpload = array();
        $filesToDelete = array();

        // The revision file goes inside the submodule.
        if ($this->isSubmodule)
        {
            $this->revisionFile = $this->isSubmodule.'/'.$this->revisionFile;
        }

        // When a revision file exists, get the version,
        // so a diff can be made.
        if (@ftp_fget($this->connection, $tempFile, $this->revisionFile, FTP_ASCII))
        {
            fseek($tempFile, 0);
            $remoteRevision = trim(fread($tempFile, 1024));
            fclose($tempFile);

            $message = "\r\n» Taking it from '".substr($remoteRevision, 0, 7)."'";
        }
        // Otherwise it will start a fresh upload.
        else
        {
            $message = "\r\n» Fresh deployment - grab a coffee";
        }

        // A remote version exists.
        if ($remoteRevision)
        {
            // Get the files from the diff.
            $output = $this->git->diff($remoteRevision);

            foreach ($output as $line)
            {
                // Added, changed or modified.
                if ($line[0] == 'A' or $line[0] == 'C' or $line[0] == 'M')
                {
                    $filesToUpload[] = trim(substr($line, 1));
                }
                // Deleted.
                elseif ($line[0] == 'D')
                {
                    $filesToDelete[] = trim(substr($line, 1));
                }
                // Unknown status.
                else
                {
                    throw new Exception("Unknown git-diff status: {$line[0]}");
                }
            }
        }
        // No remote version. Get all files.
        else
        {
            $filesToUpload = $this->git->files();
        }

        // Remove ignored files from the list of uploads.
        $filesToUpload = array_diff($filesToUpload, $this->ignoredFiles);

        // Integrated files tu upload with vendor and or composer files
        $filesToUpload = $this->addVendorFiles($filesToUpload);

        $this->filesToUpload = $filesToUpload;
        $this->filesToDelete = $filesToDelete;

        return $message;
    }

    /**
     * Getter for $this->filesToUpload
     *
     * @return array
     */
    public function getFilesToUpload()
    {
        return $this->filesToUpload;
    }

    /**
     * Getter for $this->filesToDelete
     *
     * @return array
     */
    public function getFilesToDelete()
    {
        return $this->filesToDelete;
    }

    /**
     * Getter for $this->isSubmodule
     *
     * @return mixed
     */
    public function getIsSubmodule()
    {
        return $this->isSubmodule;
    }

    /**
     * Setter for $this->repo
     *
     * @param $value
     * @return void
     */
    public function setRepo($value)
    {
        $this->repo = $value;
    }

    /**
     * Setter for $this->isSubmodule
     *
     * @param $value
     * @return void
     */
    public function setIsSubmodule($value)
    {
        $this->isSubmodule = $value;
    }

    /**
     * Uploads file
     *
     * @param $file
     * @return array
     */
    public function upload($file)
    {
        if ($this->isSubmodule)
        {
            $file = $this->isSubmodule.'/'.$file;
        }

        $dir = explode('/', dirname($file));
        $path = '';
        $pathThatExists = null;
        $output = array();

        // Iterate through directory pieces.
        for ($i = 0, $count = count($dir); $i < $count; $i++)
        {
            $path .= $dir[$i].'/';

            if (! isset($pathThatExists[$path]))
            {
                $origin = ftp_pwd($this->connection);

                // When it fails changing the directory, it means
                // that it doesn't exist.
                if (! @ftp_chdir($this->connection, $path))
                {
                    // Attempt to create the directory.
                    if (! @ftp_mkdir($this->connection, $path))
                    {
                        $output[] = "Failed to create '$path'.'";
                        return $output;
                    }
                    else
                    {
                        $output[] = "Created directory '$path'.";
                        $pathThatExists[$path] = true;
                    }
                }
                // The directory exists.
                else
                {
                    $pathThatExists[$path] = true;
                }

                ftp_chdir($this->connection, $origin);
            }
        }

        $uploaded = false;
        $attempts = 1;

        // Loop until $uploaded becomes a valid
        // resource.
        while (! $uploaded)
        {
            // Attempt to upload the file 10 times
            // and exit if it fails.
            if ($attempts == 10)
            {
                $output[] = "Tried to upload $file 10 times, and failed 10 times. Something is wrong, so I'm going to stop executing now.";
                return $output;
            }

            $uploaded = @ftp_put($this->connection, $file, $file, FTP_BINARY);

            if (! $uploaded)
            {
                $attempts++;
            }
        }

        $output[] = "√ \033[0;37m{$file}\033[0m \033[0;32muploaded\033[0m";

        return $output;
    }

    /**
     * Delete file
     *
     * @param $file
     * @return string
     */
    public function delete($file)
    {
        @ftp_delete($this->connection, $file);

        return "× \033[0;37m{$file}\033[0m \033[0;31mremoved\033[0m";
    }

    /**
     * Writes latest revision to the remote
     * revision file
     *
     * @return void
     */
    public function writeRevision()
    {
        $locRev = $this->git->localRevision();
        $temp = tempnam(sys_get_temp_dir(), 'gitRevision');

        file_put_contents($temp, $locRev);
        ftp_put($this->connection, $this->revisionFile, $temp, FTP_BINARY);
        unlink($temp);
    }

    /**
     * Closes FTP connection
     *
     * @return void
     */
    public function close()
    {
        ftp_close($this->connection);
    }

    /**
     * Update list of file to upload with vendor and or composer files
     *
     * @return array
     */
    protected function addVendorFiles(array $source)
    {
        if (in_array('composer.json', $source))
        {
            foreach(\File::allFiles(base_path() . '/vendor') as $file)
            {
                $path = str_replace(base_path() .'/', '', $file->getPathname());
                array_push($source, $path);
            }
        }
        else {
            array_push($source, 'vendor/autoload.php');
            foreach(\File::allFiles(base_path() . '/vendor/composer') as $file)
            {
                $path = str_replace(base_path() .'/', '', $file->getPathname());
                array_push($source, $path);
            }
        }
        return $source;
    }

}
