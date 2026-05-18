<?php

declare(strict_types=1);

use Panchodp\LaravelAction\Actions\ParseActionPath;

test('returns name unchanged when no slashes present', function (): void {
    expect(ParseActionPath::handle('CreateUser', ''))
        ->toBe(['name' => 'CreateUser', 'subfolder' => '']);
});

test('splits forward-slash path into name and subfolder', function (): void {
    expect(ParseActionPath::handle('User/Auth/Login', ''))
        ->toBe(['name' => 'Login', 'subfolder' => 'User/Auth']);
});

test('splits backslash path into name and subfolder', function (): void {
    expect(ParseActionPath::handle('Admin\\DeletePost', ''))
        ->toBe(['name' => 'DeletePost', 'subfolder' => 'Admin']);
});

test('merges path segments with explicit subfolder argument', function (): void {
    expect(ParseActionPath::handle('User/Login', 'Auth'))
        ->toBe(['name' => 'Login', 'subfolder' => 'User/Auth']);
});

test('trims surrounding slashes from subfolder argument', function (): void {
    expect(ParseActionPath::handle('CreateUser', '/Admin/'))
        ->toBe(['name' => 'CreateUser', 'subfolder' => 'Admin']);
});

test('collapses repeated slashes', function (): void {
    expect(ParseActionPath::handle('User//Auth///Login', ''))
        ->toBe(['name' => 'Login', 'subfolder' => 'User/Auth']);
});

test('returns single-segment path as-is', function (): void {
    expect(ParseActionPath::handle('/Foo', ''))
        ->toBe(['name' => '/Foo', 'subfolder' => '']);
});
