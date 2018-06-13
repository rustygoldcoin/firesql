<?php

namespace Fire\Sql;

class Statement
{

    static private $_statements;

    static public function init()
    {
        self::$_statements = [
            'CREATE_DB_TABLES' =>
                'CREATE TABLE IF NOT EXISTS __object (' .
                    'collection TEXT NOT NULL, ' .
                    'id TEXT NOT NULL, ' .
                    'revision INTEGER NOT NULL, ' .
                    'committed INTEGER NOT NULL, ' .
                    'updated TEXT NOT NULL, ' .
                    'origin TEXT NOT NULL, ' .
                    'obj BLOB NOT NULL' .
                '); ' .
                'CREATE TABLE IF NOT EXISTS __index (' .
                    'type TEXT NOT NULL, ' .
                    'prop TEXT NOT NULL, ' .
                    'val TEXT NOT NULL, ' .
                    'collection TEXT NOT NULL, ' .
                    'id TEXT NOT NULL, ' .
                    'origin TEXT NOT NULL' .
                ');',
            'DELETE_OBJECT' =>
                'DELETE FROM __object ' .
                'WHERE id = @id;',
            'DELETE_OBJECT_INDEX' =>
                'DELETE FROM __index ' .
                'WHERE id = @id;',
            'GET_CURRENT_OBJECT' =>
                'SELECT obj ' .
                'FROM __object ' .
                'WHERE id = @id AND committed = 1 ' .
                'ORDER BY updated DESC ' .
                'LIMIT 1;',
            'GET_OBJECT_ORIGIN_DATE' =>
                'SELECT updated ' .
                'FROM __object ' .
                'WHERE id = @id AND committed = 1 ' .
                'ORDER BY updated ASC ' .
                'LIMIT 1;',
            'GET_OBJECTS_BY_FILTER' =>
                'SELECT A.id as __id, ' .
                'A.type AS __type, ' .
                'A.collection as __collection, ' .
                'A.origin AS __origin' .
                '@columns ' .
                'FROM __index AS A ' .
                '@joinColumns' .
                'WHERE collection = @collection AND type = @type @filters' .
                'GROUP BY A.id ' .
                'ORDER BY @order @reverse ' .
                'LIMIT @limit ' .
                'OFFSET @offset;',
            'INSERT_OBJECT' =>
                'INSERT INTO __object (collection, id, revision, committed, updated, origin, obj) ' .
                'VALUES (@collection, @id, @revision, @committed, @updated, @origin, @obj);',
            'INSERT_OBJECT_INDEX' =>
                'INSERT INTO __index (type, prop, val, collection, id, origin) ' .
                'VALUES (@type, @prop, @val, @collection, @id, @origin);',
            'UPDATE_OBJECT_TO_COMMITTED' =>
                'UPDATE __object ' .
                'SET committed = 1 ' .
                'WHERE id = @id ' .
                'AND revision = @revision;'
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
