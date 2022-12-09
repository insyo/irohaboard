<?php
class DATABASE_CONFIG
{
        // iroha Board で使用するデータベース
        public $default = [
                'datasource' => 'Database/Mysql', // 変更しないでください
                'persistent' => true,
                'host' => 'mysql', // MySQLサーバのホスト名
                'login' => 'iroha', // ユーザ名
                'password' => 'P@ssword+1', // パスワード
                'database' => 'irohaboard', // データベース名
                'prefix' => 'ib_', // 変更しないでください
                'encoding' => 'utf8mb4'
        ];
}
