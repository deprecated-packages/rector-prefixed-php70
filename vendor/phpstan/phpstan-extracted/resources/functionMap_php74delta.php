<?php

namespace RectorPrefix20210620;

// @codingStandardsIgnoreFile (phpcs runs out of memory)
/**
 * Copied over from https://github.com/phan/phan/blob/da49cd287e3b315f5dfdb440522da797d980fe63/src/Phan/Language/Internal/FunctionSignatureMap_php74_delta.php
 * with more functions added from PHP 7.4 changelog
 * Copyright (c) 2015 Rasmus Lerdorf
 * Copyright (c) 2015 Andrew Morrison
 */
/**
 * This contains the information needed to convert the function signatures for php 7.4 to php 7.3 (and vice versa)
 *
 * This has two sections.
 * The 'new' section contains function/method names from FunctionSignatureMap (And alternates, if applicable) that do not exist in php7.3 or have different signatures in php 7.4.
 *   If they were just updated, the function/method will be present in the 'added' signatures.
 * The 'old' signatures contains the signatures that are different in php 7.3.
 *   Functions are expected to be removed only in major releases of php.
 *
 * @see FunctionSignatureMap.php
 *
 * @phan-file-suppress PhanPluginMixedKeyNoKey (read by Phan when analyzing this file)
 */
return ['new' => ['FFI::addr' => ['RectorPrefix20210620\\FFI\\CData', '&ptr' => 'RectorPrefix20210620\\FFI\\CData'], 'FFI::alignof' => ['int', '&ptr' => 'mixed'], 'FFI::arrayType' => ['RectorPrefix20210620\\FFI\\CType', 'type' => 'RectorPrefix20210620\\string|FFI\\CType', 'dims' => 'array<int,int>'], 'FFI::cast' => ['RectorPrefix20210620\\FFI\\CData', 'type' => 'RectorPrefix20210620\\string|FFI\\CType', '&ptr' => ''], 'FFI::cdef' => ['FFI', 'code=' => 'string', 'lib=' => '?string'], 'FFI::free' => ['void', '&ptr' => 'RectorPrefix20210620\\FFI\\CData'], 'FFI::load' => ['FFI', 'filename' => 'string'], 'FFI::memcmp' => ['int', '&ptr1' => 'FFI\\CData|string', '&ptr2' => 'FFI\\CData|string', 'size' => 'int'], 'FFI::memcpy' => ['void', '&dst' => 'RectorPrefix20210620\\FFI\\CData', '&src' => 'RectorPrefix20210620\\string|FFI\\CData', 'size' => 'int'], 'FFI::memset' => ['void', '&ptr' => 'RectorPrefix20210620\\FFI\\CData', 'ch' => 'int', 'size' => 'int'], 'FFI::new' => ['RectorPrefix20210620\\FFI\\CData', 'type' => 'RectorPrefix20210620\\string|FFI\\CType', 'owned=' => 'bool', 'persistent=' => 'bool'], 'FFI::scope' => ['FFI', 'scope_name' => 'string'], 'FFI::sizeof' => ['int', '&ptr' => 'RectorPrefix20210620\\FFI\\CData|FFI\\CType'], 'FFI::string' => ['string', '&ptr' => 'RectorPrefix20210620\\FFI\\CData', 'size=' => 'int'], 'FFI::typeof' => ['RectorPrefix20210620\\FFI\\CType', '&ptr' => 'RectorPrefix20210620\\FFI\\CData'], 'FFI::type' => ['RectorPrefix20210620\\FFI\\CType', 'type' => 'string'], 'get_mangled_object_vars' => ['array', 'obj' => 'object'], 'mb_str_split' => ['array<int,string>|false', 'str' => 'string', 'split_length=' => 'int', 'encoding=' => 'string'], 'password_algos' => ['array<int, string>'], 'password_hash' => ['string|false', 'password' => 'string', 'algo' => 'string|null', 'options=' => 'array'], 'password_needs_rehash' => ['bool', 'hash' => 'string', 'algo' => 'string|null', 'options=' => 'array'], 'preg_replace_callback' => ['string|array|null', 'regex' => 'string|array', 'callback' => 'callable', 'subject' => 'string|array', 'limit=' => 'int', '&w_count=' => 'int', 'flags=' => 'int'], 'preg_replace_callback_array' => ['string|array|null', 'pattern' => 'array<string,callable>', 'subject' => 'string|array', 'limit=' => 'int', '&w_count=' => 'int', 'flags=' => 'int'], 'sapi_windows_set_ctrl_handler' => ['bool', 'callable' => 'callable', 'add=' => 'bool'], 'ReflectionProperty::getType' => ['?ReflectionType'], 'ReflectionProperty::hasType' => ['bool'], 'ReflectionProperty::isInitialized' => ['bool', 'object=' => '?object'], 'ReflectionReference::fromArrayElement' => ['?ReflectionReference', 'array' => 'array', 'key' => 'int|string'], 'ReflectionReference::getId' => ['string'], 'SQLite3Stmt::getSQL' => ['string', 'expanded=' => 'bool'], 'strip_tags' => ['string', 'str' => 'string', 'allowable_tags=' => 'string|array<int, string>'], 'WeakReference::create' => ['WeakReference', 'referent' => 'object'], 'WeakReference::get' => ['?object']], 'old' => ['implode\'2' => ['string', 'pieces' => 'array', 'glue' => 'string']]];
