<?php

function hiddenKey($arr, $keyArr)
{
    var_dump($arr);
    foreach ($arr as $key => $value) {
        echo $key . "<br>";
        // 若是数组，则递归
        if (is_array($value)) {
            hiddenKey($value, $keyArr);
        }

        if (in_array($key, $keyArr)) {
            echo $key . "<br>";
            unset($arr[$key]);
        }
    }

    return $arr;
}
