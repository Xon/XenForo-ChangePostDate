<?php

class SV_ChangePostDate_Listener
{
    const AddonNameSpace = 'SV_ChangePostDate_';

    public static function load_class($class, array &$extend)
    {
        $extend[] = self::AddonNameSpace.$class;
    }
}