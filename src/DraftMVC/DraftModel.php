<?php

namespace DraftMVC;

class DraftModel
{
    protected $data;
    protected static $db;
    protected $dbname;
    protected $class;
    public static function useDB($db)
    {
        static::$db = $db;
    }
    public static function getTableName()
    {
        if (isset(static::$table)) {
            return static::$table;
        } else {
            return static::getDBName(get_called_class());
        }
    }
    public function dump()
    {
        return $this->toArray();
    }
    public function toArray()
    {
        return $this->data;
    }
    public function __construct($data = null)
    {
        $this->dbname = static::getTableName();
        $this->class = get_called_class();
        $statement = static::$db->prepare('DESCRIBE `' . $this->dbname . '`');
        $statement->execute();
        $rows = $statement->fetchAll();
        $this->data = [];
        foreach ($rows as $row) {
            $this->data[$row['Field']] = null;
        }

        if ($data !== null) {
            $this->data = array_merge($this->data, $data);
        }
    }

    public function __get($variable)
    {
        return $this->data[$variable];
    }

    public function __set($variable, $value)
    {
        if ($variable !== 'createdate' && $variable !== 'deletedate' && $variable !== 'changedate' && $variable !== 'id') {
            $this->data[$variable] = $value;
        }
    }

    public function save()
    {
        $_data = array();
        if ($this->data['id'] === null) {
            $this->data['createdate'] = date('Y-m-d H:i:s');
        }
        $this->data['changedate'] = date('Y-m-d H:i:s');
        $query = 'SET ';
        foreach ($this->data as $field => $value) {
            if ($field !== 'id') {
                $query .= '`' . $field . '` = :' . $field . ', ';
                $_data[':' . $field] = $value;
            }
        }
        $query = substr($query, 0, -2);
        if ($this->data['id'] !== null) {
            $query .= ' WHERE `id` = :id';
            $_data[':id'] = $this->data['id'];
        }

        if ($this->data['id'] === null) {
            $query = 'INSERT INTO `' . $this->dbname . '` ' . $query;
        } else {
            $query = 'UPDATE `' . $this->dbname . '` ' . $query;
        }
        $statement = static::$db->prepare($query);
        $statement->execute($_data);

        if ($this->data['id'] === null) {
            $this->data['id'] = static::$db->lastInsertId();
        }
    }

    public function delete($force = false)
    {
        if ($this->data['id'] !== null) {
            if (isset($this->data['deletedate']) && !$force) {
                $this->data['deletedate'] = date('Y-m-d H:i:s');
                $statement = static::$db->prepare('UPDATE `' . $this->dbname . '` SET `deletedate` = :deletedate WHERE `id` = :id');
                $statement->execute(array(':id' => $this->data['id'], ':deletedate' => $this->data['deletedate']));
            } else {
                $statement = static::$db->prepare('DELETE FROM `' . $this->dbname . '`' . ' WHERE `id` = :id');
                $statement->execute(array(':id' => $this->data['id']));
                $this->data['id'] = null;
            }
        }
    }
    public function undelete()
    {
        if (isset($this->data['deletedate']) && $this->data['id'] !== null) {
            $statement = static::$db->prepare('UPDATE `' . $this->dbname . '` SET `deletedate` = NULL WHERE `id` = :id');
            $statement->execute(array(':id' => $this->data['id']));
            $this->data['deletedate'] = null;
        }
    }
    public static function getDBName($className)
    {
        $className = explode('\\', $className);
        $className = end($className);
        return strtolower(preg_replace('/([a-z])([A-Z])/', '\1_\2', $className));
    }

    public static function findAll($withTrashed = false)
    {
        $class = get_called_class();
        $dbname = static::getTableName();
        $_query = 'SELECT * FROM `' . $dbname . '`';

        $statement = static::$db->prepare($_query);
        $statement->execute();
        $fetched = $statement->fetchAll(\PDO::FETCH_ASSOC);
        if ($fetched) {
            $list = array();
            foreach ($fetched as $data) {
                if (!isset($data['deletedate']) || empty($data['deletedate']) || $withTrashed) {
                    array_push($list, new $class($data));
                }
            }
            return new DraftCollection($list);
        }
        return false;
    }

    public static function find($query, $data = null, $withTrashed = false)
    {
        $class = get_called_class();
        $dbname = static::getTableName();
        $_query = 'SELECT * FROM `' . $dbname . '` WHERE ' . $query;
        $statement = static::$db->prepare($_query);
        if ($data === null) {
            $statement->execute();
        } elseif (is_array($data)) {
            $statement->execute($data);
        } else {
            $statement->execute([$data]);
        }
        $fetched = $statement->fetchAll(\PDO::FETCH_ASSOC);
        if ($fetched) {
            $list = array();
            foreach ($fetched as $data) {
                if (!isset($data['deletedate']) || empty($data['deletedate']) || $withTrashed) {
                    array_push($list, new $class($data));
                }
            }
            return new DraftCollection($list);
        }
        return false;
    }

    public static function findOne($query, $data = null, $withTrashed = false)
    {
        $class = get_called_class();
        $dbname = static::getTableName();
        $_query = 'SELECT * FROM `' . $dbname . '` WHERE ' . $query . ' LIMIT 1';

        $statement = static::$db->prepare($_query);

        if ($data === null) {
            $statement->execute();
        } elseif (is_array($data)) {
            $statement->execute($data);
        } else {
            $statement->execute([$data]);
        }

        $fetched = $statement->fetch(\PDO::FETCH_ASSOC);

        if ($fetched) {
            if (!isset($fetched['deletedate']) || empty($fetched['deletedate']) || $withTrashed) {
                return new $class($fetched);
            }
        }
        return false;
    }
}
