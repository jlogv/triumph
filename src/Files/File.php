<?php

    /**
     * Project: Triumph Framework
     * Class: File
     * Copyright (c) Alexey Logvinov, 2014-2023. All rights reserved.
     */

    namespace Triumph\Files;

    use Triumph\Kernel\Exception;
    use Triumph\Utils\OS;

    class File
    {
        /**
         * @var array object array of initialized $_realpath
         */
        private static array $_instances = [];

        /**
         * @var string directory name
         * return '/www/files' for '/www/files/file.html'
         */
        private string $_dirname;

        /**
         * @var string file name with extension
         * return file.html for '/www/files/file.html'
         */
        private string $_basename;

        /**
         * @var string file name without extension
         * return file for '/www/files/file.html'
         */
        private string $_filename;

        /**
         * @var string file extension
         * return html for '/www/files/file.html'
         */
        private string $_extension;

        /**
         * @var boolean 'true' if its a file
         */
        private bool $_is_file = false;

        /**
         * @var boolean 'true' if its a directory
         */
        private bool $_is_dir = false;

        /**
         * @var string path to file
         */
        private string $_realpath;

        /**
         * @var int size in bytes
         */
        private int $_size;

        /**
         * Duplication of constructor for static call
         * @param $path string
         * @return mixed
         * @throws Exception
         */
        public static function init(string $path)
        {
            if(!array_key_exists($path, self::$_instances))
            {
                self::$_instances[$path] = new self($path);
            }
            return self::$_instances[$path];
        }

        /**
         * File constructor.
         * @param $path
         * @param bool $extra
         * @throws Exception
         */
        public function __construct($path, bool $extra = true)
        {
            if (trim($path) != '')
            {
                if (file_exists($path))
                {
                    $this->_realpath = self::realpath($path);
                    $this->pathInfo();

                    if ($extra === true)
                    {
                        $this->extra();
                    }
                }
                else
                    throw new Exception('File don`t exists');
            }
            else
                throw new Exception('Path can`t be empty');
        }

        /**
         * Get file name with extension
         */
        public function baseName(): string
        {
            return $this->_basename;
        }

        /**
         * Get file name without extension
         * @return string
         */
        public function fileName(): string
        {
            return $this->_filename;
        }

        /**
         * Get file extension. If object is directory returns null
         * @return ?string
         */
        public function extension(): ?string
        {
            return $this->_extension;
        }

        /**
         * If object is folder its return true
         * @return boolean
         */
        public function isDir(): bool
        {
            return $this->_is_dir;
        }

        /**
         * if object is file its return true
         * @return boolean
         */
        public function isFile(): bool
        {
            return $this->_is_file;
        }

        /**
         * size of file or directory
         * @param bool $human
         * @return int
         * @internal param bool $human if 'true' result will be in human like style (Kb, Mb, etc.)
         */
        public function size(bool $human = false): int
        {
            if($this->_is_file) $this->_size = filesize($this->_realpath);

            if($this->_is_dir) $this->_size = $this->folderSize($this->_realpath);

            if($human === true)
                return $this->humanFilesize($this->_size);
            else
                return $this->_size;
        }

        /**
         * size of directory
         * @param $dir string
         * @return int
         */
        public function folderSize(string $dir) : int
        {
            $size = 0;
            foreach (glob(rtrim($dir, '/').'/*', GLOB_NOSORT) as $each) {
                $size += is_file($each) ? filesize($each) : $this->folderSize($each);
            }
            return $size;
        }

        /**
         * check if file exists
         * @param string $path path to file
         * @return boolean
         */
        public static function exists(string $path) : bool
        {
            //$realpath = self::realpath($path);
            if(file_exists($path))
                return true;
            else
                return false;
        }

        /**
         * create new directory
         * @param string $path
         * @param int $permissions
         * @return File
         * @throws Exception
         * @internal param string $path dir path
         * @internal param int $permissions permission for write access
         */
        public static function createDir(string $path, int $permissions = 0754): File
        {
            $realpath = self::realpath($path);
            if(!self::exists($realpath))
            {
                if (mkdir($realpath, $permissions, true))
                {
                    return self::set($realpath);
                }
                else
                    throw new Exception('Error dyring creating the dir');
            }
            else
                return self::init($realpath);
        }

        /**
         * @param $realpath
         * @return File
         */
        private function set($realpath): File
        {
            $this->_realpath = $realpath;
            return $this;
        }

        /**
         * base collection of file info
         */
        private function pathInfo(): void
        {
            if (is_file($this->_realpath))
            {
                $this->_is_file = true;
            }
            elseif (is_dir($this->_realpath))
            {
                $this->_is_dir = true;
            }

            $info = pathinfo($this->_realpath);
            $this->_dirname = $info['dirname'];
            $this->_basename = $info['basename'];
            $this->_filename = $info['filename'];

            if(key_exists('extension', $info))
                $this->_extension = $info['extension'];
            else
                $this->_extension = '';
        }

        /**
         * Collection file info
         */
        private function extra(): void
        {
            $this->_size = filesize($this->_realpath);
        }

        /**
         * normalize file path for OS style
         * @param string $path
         * @return string
         */
        private static function realpath(string $path): string
        {
            if (OS::isWindows())
                return preg_replace('/\//', '\\', $path);
            if (OS::isLinux())
                return $path;

            return '';
        }

        /**
         * Convert size for human
         * @param int $size size in bytes
         * @return string
         */
        private function humanFilesize(int $size): string
        {
            $units = array('B','KB','MB','GB','TB','PB','EB','ZB','YB');
            for ($i = 0; $size > 1024; $i++)
            {
                $size /= 1024;
            }
            return round($size, 2).' '.$units[$i];
        }

        /**
         * @return bool
         * @throws Exception
         */
        public function deleteDir() : bool
        {
            if (!$this->isDir())
            {
                throw new Exception('Must be a directory');
            }
            else
            {
                $realpath = $this->_realpath;

                if (!str_ends_with($realpath, '/')) {
                    $realpath .= '/';
                }

                $files = glob($realpath . '*', GLOB_MARK);

                foreach ($files as $file)
                {
                    if (is_dir($file))
                    {
                        File::init($file)->deleteDir();
                    }
                    else
                    {
                        unlink($file);
                    }
                }
                rmdir($realpath);

                return true;
            }

        }
    }