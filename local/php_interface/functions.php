<?php

function my_is_int($value)
{
    return strval($value) == strval(intval($value));
}
