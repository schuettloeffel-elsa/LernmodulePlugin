<?php

class AddBlocks extends Migration
{
    function up()
    {
        DBManager::get()->exec("
            CREATE TABLE `lernmodule_blocks` (
                `block_id` varchar(32) NOT NULL DEFAULT '',
                `seminar_id` varchar(32) NOT NULL,
                `title` varchar(256) DEFAULT NULL,
                `infotext` text DEFAULT NULL,
                `position` int(11) DEFAULT '1',
                `chdate` int(11) NOT NULL,
                `mkdate` int(11) NOT NULL,
                PRIMARY KEY (`block_id`),
                KEY `seminar_id` (`seminar_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        DBManager::get()->exec("
            INSERT INTO `lernmodule_blocks` (`block_id`, `seminar_id`, `chdate`, `mkdate`)
            SELECT lernmodule_courses.seminar_id, lernmodule_courses.seminar_id, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
            FROM lernmodule_courses
            GROUP BY lernmodule_courses.seminar_id
        ");
        DBManager::get()->exec("
            ALTER TABLE `lernmodule_courses`
            ADD COLUMN `block_id` VARCHAR(32) DEFAULT NULL,
            ADD COLUMN `position` int(11) DEFAULT '1',
            ADD COLUMN chdate int(11) NOT NULL,
            ADD COLUMN mkdate int(11) NOT NULL
        ");
        DBManager::get()->exec("
            UPDATE `lernmodule_courses`
            SET `block_id` = `lernmodule_courses`.seminar_id,
                mkdate = UNIX_TIMESTAMP(),
                chdate = UNIX_TIMESTAMP()
        ");

        DBManager::get()->exec("
            DELETE lernmodule_courses.*
            FROM lernmodule_courses
                LEFT JOIN lernmodule_module USING (module_id)
            WHERE lernmodule_module.module_id IS NULL
        ");

        SimpleORMap::expireTableScheme();
    }

    function down()
    {
        DBManager::get()->exec("
            DROP TABLE `lernmodule_blocks`
        ");
        DBManager::get()->exec("
            ALTER TABLE `lernmodule_courses`
            DROP COLUMN `block_id`
        ");
        SimpleORMap::expireTableScheme();
    }
}
