<?php

namespace App1\Helper\File\Import;

class Qif
{

    const _AMOUNT = 'amount';
    const _SUM = 'sum';
    const _DATE = 'date';
    const _PAYEE = 'payee';
    const _INVESTMENT = 'investment';
    const _COMMENT = 'comment';
    const _D = 'D';
    const _T = 'T';
    const _P = 'P';
    const _N = 'N';
    const _M = 'M';

    protected $filename;
    protected $offset;
    protected $results;
    protected $types;

    /**
     * __construct
     *
     * @param string $filename
     * @param int $offset
     * @return $this
     */
    public function __construct(string $filename, int $offset = 0)
    {
        $this->filename = $filename;
        $this->offset = $offset;
        $this->results = [];
        $this->types = [];
        return $this;
    }

    /**
     * parse
     *
     * @return $this
     */
    public function parse()
    {
        $lines = file($this->filename);
        $records = [];
        $record = [];
        $end = 0;
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '^') {
                $end = 1;
            } elseif (preg_match('#^!Type:(.*)$#', $line, $match)) {
                $this->types[] = trim($match[1]);
            } else {
                switch (substr($line, 0, 1)) {
                    case self::_D:
                        $record[self::_DATE] = trim(substr($line, 1));
                        break;
                    case self::_T:
                        $record[self::_AMOUNT] = trim(substr($line, 1));
                        $record[self::_AMOUNT] = str_replace(',', '', $record[self::_AMOUNT]);
                        $record[self::_SUM] = $this->offset + floatval($record[self::_AMOUNT]);
                        $this->offset = $record[self::_SUM];
                        break;
                    case self::_P:
                        $line = htmlentities($line);
                        $line = str_replace('  ', '', $line);
                        $record[self::_PAYEE] = trim(substr($line, 1));
                        break;
                    case self::_N:
                        $record[self::_INVESTMENT] = $this->asciiFilter(trim(substr($line, 1)));
                        break;
                    case self::_M:
                        $record[self::_COMMENT] = $this->asciiFilter(trim(substr($line, 1)));
                        break;
                }
            }
            if ($end == 1) {
                $records[] = $record;
                $record = [];
                $end = 0;
            }
        }
        $this->results = $records;
        return $this;
    }

    /**
     * asArray
     * @return array
     */
    public function asArray(): array
    {
        return $this->results;
    }

    /**
     * getTypes
     * @return array
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * getTypes
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->types;
    }

    /**
     * asciiFilter
     *
     * @param string $input
     * @return string
     */
    protected function asciiFilter(string $input): string
    {
        return preg_replace('/[^[:print:]\r\n]/', '', $input);
    }
}
