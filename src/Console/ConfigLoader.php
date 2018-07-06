<?php
namespace ngyuki\DbdaTool\Console;

class ConfigLoader
{
    public function loadFile($file)
    {
        /** @noinspection PhpIncludeInspection */
        $arr = include $file;
        if (!is_array($arr)) {
            throw new \RuntimeException('Should return array from config file.');
        }
        return $arr;
    }

    /**
     * @param string|null $path
     * @return array
     */
    public function load($path)
    {
        if ($path !== null) {
            return $this->loadFile($path);
        }

        $composerFile = $this->findUpComposer();
        if ($composerFile === false) {
            return [];
        }

        $composerDir = dirname($composerFile);

        $composerContent = file_get_contents($composerFile);
        if ($composerContent === false) {
            throw new \RuntimeException("Unable read \"$composerFile\"");
        }

        $composerConfig = json_decode($composerContent, true);
        if (!isset($composerConfig['extra']['dbdatool-config'])) {
            return [];
        }

        $list = (array)$composerConfig['extra']['dbdatool-config'];
        foreach ($list as $path) {
            if (file_exists($composerDir . DIRECTORY_SEPARATOR . $path)) {
                return $this->loadFile($composerDir . DIRECTORY_SEPARATOR . $path);
            }
        }

        return [];
    }

    private function findUpComposer()
    {
        $dir = getcwd();
        for (;;) {
            $composer = $dir . DIRECTORY_SEPARATOR . 'composer.json';
            if (file_exists($composer)) {
                return $composer;
            }
            $next = dirname($dir);
            if ($next === $dir) {
                break;
            }
            $dir = $next;
        }

        return false;
    }
}
