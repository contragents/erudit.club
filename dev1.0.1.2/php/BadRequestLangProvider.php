<?php
use Dadata\Cache;

class BadRequest extends Exception
{
    const HTTP_BAD_REQUEST_CODE = 400;
    const ERRORS_KEY = 'erudit_bot_errors';
    const MAX_ERRORS = 100;

    public static $eMessage = '';

    public static function sendBadRequest(array $params = [])
    {
        ob_clean();
        http_response_code(self::HTTP_BAD_REQUEST_CODE);
        print json_encode(['result' => 'error', 'message' => self::$eMessage ?: ($params['message'] ?? 'No message'), 'ext_data' => $params]);

        Cache::hset(self::ERRORS_KEY, time() % self::MAX_ERRORS, ['date' => date('Y-m-d H:i:s'), 'error' => $params]);

        //exit();
    }

    public function __construct(string $message)
    {
        self::$eMessage = $message;
        return parent::__construct($message);
    }

    /**
     * Gets a string representation of the thrown object
     * @link https://php.net/manual/en/throwable.tostring.php
     * @return string <p>Returns the string representation of the thrown object.</p>
     * @since 7.0
     */
    public function __toString()
    {
        return self::$eMessage;
    }
}

