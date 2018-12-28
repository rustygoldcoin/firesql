<?php

namespace Fire\Sql;

class Statement
{
    static private $_statements;

    static public function init()
    {
        self::$_statements = [
            'CREATE_DB_TABLES' =>
                'CREATE TABLE IF NOT EXISTS @collection__object (' .
                    'id TEXT NOT NULL, ' .
                    'revision INTEGER NOT NULL, ' .
                    'committed INTEGER NOT NULL, ' .
                    'updated TEXT NOT NULL, ' .
                    'origin TEXT NOT NULL, ' .
                    'obj BLOB NOT NULL' .
                '); ' .
                'CREATE TABLE IF NOT EXISTS @collection__index (' .
                    'type TEXT NOT NULL, ' .
                    'prop TEXT NOT NULL, ' .
                    'val TEXT NOT NULL, ' .
                    'id TEXT NOT NULL, ' .
                    'origin TEXT NOT NULL' .
                ');',
            'DELETE_OBJECT' =>
                'DELETE FROM @collection__object ' .
                'WHERE id = @id;',
            'DELETE_OBJECT_EXCEPT_REVISION' =>
                'DELETE FROM @collection__object ' .
                'WHERE id = @id AND NOT revision = @revision;',
            'DELETE_OBJECT_INDEX' =>
                'DELETE FROM @collection__index ' .
                'WHERE id = @id;',
            'GET_CURRENT_OBJECT' =>
                'SELECT obj ' .
                'FROM @collection__object ' .
                'WHERE id = @id AND committed = 1 ' .
                'ORDER BY updated DESC ' .
                'LIMIT 1;',
            'GET_OBJECT_ORIGIN_DATE' =>
                'SELECT updated ' .
                'FROM @collection__object ' .
                'WHERE id = @id AND committed = 1 ' .
                'ORDER BY updated ASC ' .
                'LIMIT 1;',
            'GET_OBJECTS_BY_FILTER' =>
                'SELECT A.id AS __id, ' .
                'A.type AS __type, ' .
                'A.origin AS __origin' .
                '@columns ' .
                'FROM @collection__index AS A ' .
                '@joinColumns' .
                'WHERE type = @type @filters' .
                'GROUP BY __id, __type, __origin @columns ' .
                'ORDER BY @order @reverse ' .
                'LIMIT @limit ' .
                'OFFSET @offset;',
            'GET_COLLECTION_OBJECT_COUNT' =>
                'SELECT COUNT(*) ' .
                'FROM @collection__index ' .
                'WHERE type = \'registry\'',
            'GET_OBJECTS_COUNT_BY_FILTER' =>
                'SELECT COUNT(*) FROM (' .
                'SELECT A.id AS __id ' .
                '@columns ' .
                'FROM @collection__index AS A ' .
                '@joinColumns' .
                'WHERE type = @type @filters' .
                'GROUP BY __id' .
                ') AS B;',
            'INSERT_OBJECT' =>
                'INSERT INTO @collection__object (id, revision, committed, updated, origin, obj) ' .
                'VALUES (@id, @revision, @committed, @updated, @origin, @obj);',
            'INSERT_OBJECT_INDEX' =>
                'INSERT INTO @collection__index (type, prop, val, id, origin) ' .
                'VALUES (@type, @prop, @val, @id, @origin);',
            'UPDATE_OBJECT_TO_COMMITTED' =>
                'UPDATE @collection__object ' .
                'SET committed = 1 ' .
                'WHERE id = @id AND revision = @revision;'
        ];
    }

    static public function get($sqlStatement, $variables = [])
    {
        if (!is_array(self::$_statements)) {
            self::init();
        }
        $sql = self::$_statements[$sqlStatement];
        if ($variables) {
            foreach ($variables as $variable => $value) {
                $sql = str_replace($variable, $value, $sql);
            }
        }
        return $sql;
    }
}
