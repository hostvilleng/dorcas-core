<?php

namespace App\Dorcas\Enum;


class ResponseStatus
{
    const ERROR = 'error';
    const EXCEPTION = 'exception';
    const INPUT_ERROR = 'input_error';
    const HTTP_ERROR = 'http_error';
    const NOT_FOUND = 'not_found';
    const SUCCESS = 'success';
    const VALIDATION_FAILED = 'validation_failed';
}