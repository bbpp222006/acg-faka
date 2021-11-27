<?php
declare(strict_types=1);

namespace App\Util;


use JetBrains\PhpStorm\NoReturn;
use Kernel\Exception\JSONException;

class Waf
{
    private static array $RULE = [];
    private static array $DATA = [];
    private static ?self $instance = null;

    /**
     * @throws \Kernel\Exception\JSONException
     */
    #[NoReturn] private function wafPost(): void
    {
        $def = json_decode(self::$RULE["POST"], true);
        foreach ($def as $key => $value) {
            if (preg_match("#" . $value[1] . "#i", self::$DATA["POST"])) {
                throw new JSONException($value[2]);
            }
        }
    }

    /**
     * @throws \Kernel\Exception\JSONException
     */
    #[NoReturn] private function wafRequest(): void
    {
        $def = json_decode(self::$RULE["ARG"], true);
        foreach ($def as $key => $value) {
            if (preg_match("#" . $value[1] . "#i", self::$DATA["REQUEST"])) {
                throw new JSONException($value[2]);
            }
        }
        $sd = json_decode(self::$RULE["URL"], true);
        foreach ($sd as $key => $value) {
            if (preg_match("#" . $value[1] . "#i", self::$DATA["REQUEST"])) {
                throw new JSONException($value[2]);
            }
        }
    }

    /**
     * @throws \Kernel\Exception\JSONException
     */
    #[NoReturn] private function wafCookie(): void
    {
        $def = json_decode(self::$RULE["COOKIE"], true);
        foreach ($def as $key => $value) {
            if (preg_match("#" . $value[1] . "#i", self::$DATA["COOKIE"])) {
                throw new JSONException($value[2]);
            }
        }
    }

    /**
     * @throws \Kernel\Exception\JSONException
     */
    #[NoReturn] private function wafUserAgent(): void
    {
        $def = json_decode(self::$RULE["UA"], true);
        foreach ($def as $key => $value) {
            if (preg_match("#" . $value[1] . "#i", self::$DATA["UA"])) {
                throw new JSONException($value[2]);
            }
        }
    }


    /**
     * @param array $array
     * @return array|string
     */
    private function listToString(array $array): array|string
    {
        if (is_array($array)) {
            $t = '';
            foreach ($array as $key => $value) {
                $t = $t . '&' . $key . '=' . $value;
            }
        } else {
            $t = $array;
        }
        return $t;
    }

    #[NoReturn] public function run(callable $callable): void
    {
        $path = BASE_PATH . "/config/waf";
        self::$RULE["POST"] = file_get_contents($path . "/post.json");
        self::$RULE["URL"] = file_get_contents($path . "/url.json");
        self::$RULE["ARG"] = file_get_contents($path . "/args.json");
        self::$RULE["COOKIE"] = file_get_contents($path . "/cookie.json");
        self::$RULE["UA"] = file_get_contents($path . "/ua.json");

        //DATA INIT
        self::$DATA["POST"] = $this->listToString($_POST);
        self::$DATA["REQUEST"] = (string)$_SERVER["REQUEST_URI"];
        self::$DATA["UA"] = (string)$_SERVER["HTTP_USER_AGENT"];
        self::$DATA["COOKIE"] = (string)$_SERVER["HTTP_COOKIE"];

        try {
            $this->wafPost();
            $this->wafRequest();
            $this->wafCookie();
            $this->wafUserAgent();
        } catch (\Exception $e) {
            $callable($e->getMessage());
        }
    }


    /**
     * @return static
     */
    public static function instance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}