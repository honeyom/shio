<?php
function route_class()
{
    return str_replace('.', '-', Route::currentRouteName());
}
function test_helper() {
    return 'OK';
}