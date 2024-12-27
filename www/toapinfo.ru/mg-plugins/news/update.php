<?php

$res = DB::query('SHOW TABLES LIKE "mpl_news"');
if (DB::numRows($res)) {
  $res = DB::query('SHOW TABLES LIKE "' . PREFIX . 'mpl_news"');
  if (!DB::numRows($res)) {
    DB::query("RENAME TABLE mpl_news TO " . PREFIX . "mpl_news;");
  }
  DB::query("DROP TABLE `mpl_news`");
}

$dbQuery = DB::query("SHOW COLUMNS FROM `" . PREFIX . "mpl_news` LIKE 'author'");
if(!$row = DB::fetchArray($dbQuery)) {
  DB::query("ALTER TABLE `" . PREFIX . "mpl_news` ADD `author` text NOT NULL");
}

