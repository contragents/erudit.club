<?php

class TonController extends BaseController
{
    const COMMON_URL = 'mvc/ton/';

    const CELL_BASE64 = 'te6ccgECEAEAAzQAART/APSkE/S88sgLAQIBYgIDAgLMBAUCA3pgDg8E9dkGOASS+B8ADoaYGAuNhJL4HwfSB9IBj9ABi465D9ABj9ABg51NoAAWmP6Z/2omh9AH0gamoYQAqpOF1xgUEIPe7L7yk4XXGBQQgWO1y5qThdcYEam5uR4AHHDRmoGuOC+XAkgf0gGCzkKAJ9ASxni2ZmZPaqcBqBYAJAYHCAkAk7PwUIgG4KhAJqgoB5CgCfQEsZ4sA54tmZJFkZYCJegB6AGWAZJB8gDg6ZGWBZQPl/+ToO8AMZGWCrGeLKAJ9AQnltYlmZmS4/YBAIY1NTY2UTTHBfLgSQL6QPoA1DAg0IBg1yH6ADBTcKCCKWNFeF2KAAC88tBPJRA0UELwCRSgUDPIUAT6AljPFszMye1UAcA2NzcB+gD6QPgoVBIGcFQgE1QUA8hQBPoCWM8WAc8WzMkiyMsBEvQA9ADLAMn5AHB0yMsCygfL/8nQUAbHBfLgSqEDRUXIUAT6AljPFszMye1UAfpAMCDXCwHDAJFb4w0KAf42XwOCCJiWgBWgFbzy4EsC+kDTADCVyCHPFsmRbeKCENFzVABwgBjIywVQBc8WJPoCFMtqE8sfFMs/I/pEMHC6jjP4KEQDcFQgE1QUA8hQBPoCWM8WAc8WzMkiyMsBEvQA9ADLAMn5AHB0yMsCygfL/8nQzxaWbCJwAcsB4vQACwL+jvpRJMcF8uBJ9AQwcMjLB4tkpFVFRPTozxbJgvC3anyhU8JGcWWDNbvQiUY1D/xiH6HFFucSMJXU/9XFgViDB/QXcMjLB4sTmM8WyYLw7oD9Lx4DSA4igjY1lu51LXuyf1B3a5UIagJ5GJZ1kj5Ygwf0F3DIywf0AMlDAOBfBQwNAD6CENUydttwgBDIywVQA88WIvoCEstqyx/LP8mAQvsAAArJgED7AAAayFAE+gJYzxbMzMntVAAIhA/y8AB9rbz2omh9AH0gamoYNhj8FAC4KhAJqgoB5CgCfQEsZ4sA54tmZJFkZYCJegB6AGWAZPyAODpkZYFlA+X/5OhAADmvFvaiaH0AfSBqahgZOCmAOmRlgWWD5f/k6CABwA==';
    const DECODE_TMP_KEY = 'ton_cell_decode';

    public function Run()
    {
        ini_set("display_errors", 1);
        error_reporting(E_ALL);

        return parent::Run();
    }

    public function cell_decodeAction(): string
    {
        $strBin = base64_decode(self::CELL_BASE64);
        Cache::setex(self::DECODE_TMP_KEY, 5000, $strBin);
        //return Cache::get(self::DECODE_TMP_KEY);
        $strlen = strlen($strBin);
        $res = '<html><head><meta http-equiv="Content-Type" content="text/html;charset=ISO-8859-1"></head><body>';

        for ($i = 0; $i <= 7; $i++) {
            $res .= "Offset $i bits: <pre>";
            for ($byteNum = 0; $byteNum <= $strlen; $byteNum++) {
                $res .= chr(Cache::rawcommand('bitfield', [self::DECODE_TMP_KEY, 'get', 'u8', $byteNum * 8 + $i])[0] ?: 0);
            }
            $res .= "</pre>";
        }

        $res .= '</body></html>';
        return $res;
    }
}