<?php

namespace App\Dorcas\Support;


use Carbon\Carbon;

class TimePeriod
{
    /** @var Carbon  */
    public $from;
    
    /** @var Carbon  */
    public $to;
    
    /**
     * TimePeriod constructor.
     *
     * @param Carbon $from
     * @param Carbon $to
     */
    public function __construct(Carbon $from, Carbon $to)
    {
        $this->from = $from;
        $this->to = $to;
    }
    
    /**
     * Returns a  TImePeriod instance for the same dates from $removeYears years ago.
     *
     * @param int $removeYears
     *
     * @return TimePeriod
     */
    public function getSamePeriodFromPastYear(int $removeYears = 1): TimePeriod
    {
        $from = $this->from->subYears($removeYears);
        $to = $this->to->subYears($removeYears);
        return new static($from, $to);
    }
}