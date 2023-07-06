<?php

function mapWithId($arr, $id)
    {
        return array_map(function ($itemId) use ($id) {
            return [$itemId, $id];
        }, $arr);
    }

var_dump(mapWithId(["1", "2"], "product id"));