<?php

class CybineContentFilterUtils
{
    public static function parseOptionalValue(string $type, ?string $value)
    {
        if(!$value || empty(trim($value)))
        {
            return '';
        }

        return "$type='$value'";
    }
}