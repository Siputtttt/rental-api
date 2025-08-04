<?php

namespace App\Services\Core;

class DatabaseService
{
    public function getEngine()
    {
        return [
            ['value' => 'InnoDB', 'text' => 'InnoDB'],
            ['value' => 'MyISAM', 'text' => 'MyISAM'],
            ['value' => 'MEMORY', 'text' => 'MEMORY'],
            ['value' => 'CSV', 'text' => 'CSV'],
            ['value' => 'ARCHIVE', 'text' => 'ARCHIVE'],
            ['value' => 'BLACKHOLE', 'text' => 'BLACKHOLE'],
            ['value' => 'MERGE', 'text' => 'MERGE'],
        ];
    }

    public function getTypeData()
    {
        return [
            ['value' => 'varchar', 'text' => 'varchar'],
            ['value' => 'char', 'text' => 'char'],
            ['value' => 'tinyint', 'text' => 'tinyint'],
            ['value' => 'smallint', 'text' => 'smallint'],
            ['value' => 'mediumint', 'text' => 'mediumint'],
            ['value' => 'bigint', 'text' => 'bigint'],
            ['value' => 'string', 'text' => 'string'],
            ['value' => 'int', 'text' => 'integer'],
            ['value' => 'text', 'text' => 'text'],
            ['value' => 'float', 'text' => 'float'],
            ['value' => 'double', 'text' => 'double'],
            ['value' => 'decimal', 'text' => 'decimal'],
            ['value' => 'boolean', 'text' => 'boolean'],
            ['value' => 'date', 'text' => 'date'],
            ['value' => 'datetime', 'text' => 'datetime'],
            ['value' => 'timestamp', 'text' => 'timestamp'],
            ['value' => 'time', 'text' => 'time'],
            ['value' => 'year', 'text' => 'year'],
            ['value' => 'json', 'text' => 'json'],
            ['value' => 'jsonb', 'text' => 'jsonb'],
            ['value' => 'binary', 'text' => 'binary'],
            ['value' => 'varbinary', 'text' => 'varbinary'],
            ['value' => 'tinyblob', 'text' => 'tinyblob'],
            ['value' => 'blob', 'text' => 'blob'],
            ['value' => 'mediumblob', 'text' => 'mediumblob'],
            ['value' => 'longblob', 'text' => 'longblob'],
            ['value' => 'tinytext', 'text' => 'tinytext'],
            ['value' => 'mediumtext', 'text' => 'mediumtext'],
            ['value' => 'longtext', 'text' => 'longtext'],
            ['value' => 'enum', 'text' => 'enum'],
            ['value' => 'set', 'text' => 'set'],
            ['value' => 'bit', 'text' => 'bit'],
            ['value' => 'geometry', 'text' => 'geometry'],
            ['value' => 'point', 'text' => 'point'],
            ['value' => 'linestring', 'text' => 'linestring'],
            ['value' => 'polygon', 'text' => 'polygon'],
            ['value' => 'multipoint', 'text' => 'multipoint'],
            ['value' => 'multilinestring', 'text' => 'multilinestring'],
            ['value' => 'multipolygon', 'text' => 'multipolygon'],
            ['value' => 'geometrycollection', 'text' => 'geometrycollection'],
            ['value' => 'uuid', 'text' => 'uuid'],
            ['value' => 'xml', 'text' => 'xml'],
            ['value' => 'cidr', 'text' => 'cidr'],
            ['value' => 'inet', 'text' => 'inet'],
            ['value' => 'macaddr', 'text' => 'macaddr'],
            ['value' => 'tsvector', 'text' => 'tsvector'],
            ['value' => 'tsquery', 'text' => 'tsquery'],
            ['value' => 'hstore', 'text' => 'hstore'],
            ['value' => 'lseg', 'text' => 'lseg'],
            ['value' => 'box', 'text' => 'box'],
            ['value' => 'path', 'text' => 'path'],
            ['value' => 'polygon', 'text' => 'polygon'],
            ['value' => 'circle', 'text' => 'circle'],
            ['value' => 'interval', 'text' => 'interval'],
            ['value' => 'jsonpath', 'text' => 'jsonpath'],
            ['value' => 'timestamptz', 'text' => 'timestamptz'],
            ['value' => 'timetz', 'text' => 'timetz'],
            ['value' => 'date', 'text' => 'date'],
            ['value' => 'time', 'text' => 'time'],
        ];
    }

    public function createTable($table, $engine, $columns)
    {
        $sql = "CREATE TABLE `$table` (";
        $fields = [];

        foreach ($columns as $col) {
            $name = $col['name'];
            $type = strtolower($col['type']);
            $length = $col['length'] != '' ? "({$col['length']})" : '';
            $nullable = ($col['not_null'] == 'true' || $col['not_null'] == true) ? 'NOT NULL' : 'NULL';
            $default = $col['default'] !== null ? "DEFAULT '{$col['default']}'" : '';
            $autoIncrement = ($col['auto_increment'] === 'true' || $col['auto_increment'] === true) ? 'AUTO_INCREMENT' : '';

            $fields[] = "`$name` $type$length $nullable $default $autoIncrement";
        }

        $primaryKeys = [];
        foreach ($columns as $col) {
            if ($col['primary_key'] == 'true' || $col['primary_key'] === true) {
                $primaryKeys[] = "`{$col['name']}`";
            }
        }

        if (count($primaryKeys)) {
            $fields[] = "PRIMARY KEY (" . implode(', ', $primaryKeys) . ")";
        }

        $sql .= implode(', ', $fields);
        $sql .= ") ENGINE=$engine";

        return $sql;
    }

    public function fields($data)
    {

        $type = strtolower($data['field_type']);
        $length = "";

        if (in_array($type, ['varchar', 'char', 'decimal', 'float', 'int'])) {
            $length = $data['field_length'] ? "({$data['field_length']})" : ($type == 'varchar' ? "(255)" : '');
        }

        $key = $data['key'] ? 'PRIMARY KEY' : '';
        $null = $data['null'] ? 'NOT NULL' : 'NULL';
        $autoIncrement = $data['auto_increment'] ? 'AUTO_INCREMENT' : '';

        $columnDef = trim("{$type}{$length} {$null} {$autoIncrement} {$key}");

        $cek = \DB::select("SHOW COLUMNS FROM {$data['table']} WHERE Field = ?", [$data['Field']]);
        if ($cek) {
            $sql = "ALTER TABLE `{$data['table']}` MODIFY `{$data['Field']}` {$columnDef};";
        } else {
            $sql = "ALTER TABLE `{$data['table']}` ADD COLUMN `{$data['Field']}` {$columnDef};";
        }
        return $sql;
    }
}
