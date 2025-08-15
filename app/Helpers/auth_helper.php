<?php

if (!function_exists('is_admin')) {
    function is_admin()
    {
        $user = auth()->user();
        return $user && $user->inGroup('admin');
    }
}

if (!function_exists('is_vendor')) {
    function is_vendor()
    {
        $user = auth()->user();
        return $user && $user->inGroup('vendor');
    }
}

if (!function_exists('is_seo_team')) {
    function is_seo_team()
    {
        $user = auth()->user();
        return $user && $user->inGroup('seo_team');
    }
}