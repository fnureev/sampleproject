<?php
namespace Components;

class Storage
{
    protected $filename;
    protected $file;
    protected $mode;

    public function __construct($filename, $new = false)
    {
        $this->filename = $filename;

        if ($new) {
            file_put_contents($this->filename, '');
        }
    }

    public function __destruct()
    {
        if (is_resource($this->file)) {
            fclose($this->file);
        }
    }

    protected function open($mode = 'read')
    {
        if (is_resource($this->file)) {
            fclose($this->file);
        }

        switch ($mode) {
            case 'write':
                $this->file = fopen($this->filename, 'a');
                $this->mode = 'write';
                break;
            default:
                $this->file = fopen($this->filename, 'r');
                $this->mode = 'read';
                break;
        }
    }

    public function add($line)
    {
        if ($this->mode != 'write') {
            $this->open('write');
        }

        return fputs($this->file, trim($line)."\n");
    }

    public function getList()
    {
        if ($this->mode != 'read') {
            $this->open('read');
        }

        while ($line = fgets($this->file)) {
            yield trim($line);
        }
    }
}
