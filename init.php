<?php

/**
 *    __  _____   ___   __          __
 *   / / / /   | <  /  / /   ____ _/ /_  _____
 *  / / / / /| | / /  / /   / __ `/ __ `/ ___/
 * / /_/ / ___ |/ /  / /___/ /_/ / /_/ (__  )
 * `____/_/  |_/_/  /_____/`__,_/_.___/____/
 *
 * @package FireSql
 * @author UA1 Labs Developers https://ua1.us
 * @copyright Copyright (c) UA1 Labs
 */

if (class_exists('\UA1Labs\Fire\Bug')) {
    $fireBug = \UA1Labs\Fire\Bug::get();
    $fireBug->addPanel(new \UA1Labs\Fire\Sql\Panel\FireSqlPanel());
}
