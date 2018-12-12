<?php

namespace App1\Helper\Stream\Filter;

class Csv extends \php_user_filter
{

    const MODE_ALL = 'csv.*';
    const MODE_COUNT = 'csv.count';
    const MODE_TRANSFO = 'csv.transfo';

    private $bufferHandle = '';
    private $counters;
    private $datas;
    private $mode;

    /**
     * filter
     *
     * @param mixed $in
     * @param mixed $out
     * @param int $consumed
     * @param bool $closing
     * @return int
     */
    public function filter($in, $out, &$consumed, $closing): int
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            $this->datas = $bucket->data;
            $consumed += $bucket->datalen;
        }
        $buck = stream_bucket_new($this->bufferHandle, '');
        if (false === $buck) {
            return PSFS_ERR_FATAL;
        }
        if (!$closing) {
            if ($this->mode) {
                $this->updateCounters();
            }
        } else {
            if ($this->mode) {
                $buck->data = \json_encode($this->counters);
            }
        }
        stream_bucket_append($out, $buck);
        return PSFS_PASS_ON;
    }

    /**
     * onCreate
     *
     * @return bool
     */
    public function onCreate(): bool
    {
        $this->bufferHandle = @fopen('php://temp', 'w+');
        if (false !== $this->bufferHandle) {
            $this->init();
            return true;
        }
        return false;
    }

    /**
     * onClose
     */
    public function onClose()
    {
        @fclose($this->bufferHandle);
    }

    /**
     * updateCounters
     *
     */
    private function updateCounters()
    {
        $freq = array_filter(count_chars($this->datas));
        foreach ($freq as $ord => $count) {
            if (!isset($this->counters[$ord])) {
                $this->counters[$ord] = 0;
            }
            $this->counters[$ord] += $count;
        }
    }

    /**
     * init
     *
     */
    private function init()
    {
        $this->mode = ($this->filtername == self::MODE_COUNT);
        $this->counters = [];
        $this->datas = [];
    }
}
