<?php
class C
{
    public static function f(): C
    {
        return new C();
    }
}
