<?php

namespace PWA\Product\Helper;

class Data
{
    public function getDateTime($str)
    {
        if (!is_string($str) || $str == '') {
            return null;
        }
        if (preg_match('/^(\d\d?)\/(\d\d?)\/(\d\d\d\d)$/', $str, $match)) {
            return new \DateTime($str);
        } else if (preg_match('/^(\d\d\d\d)-(\d\d?)-(\d\d?)$/', $str, $match)) {
            return new \DateTime($str);
        } else if (preg_match('/^(\d\d?).(\d\d?)\.(\d\d\d\d)$/', $str, $match)) {
            return new \DateTime($str);
        }
        return null;
    }
}
