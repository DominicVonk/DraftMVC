<?php
namespace DraftMVC;
class DraftModel
{
    private $data;
    private static $db;
    private $dbname;
    private $class;
    public static function useDB ($db) 
    {
        self::$db = $db;
    }

    public function __construct($data = null)
    {
        $this->dbname = self::getDBName(get_called_class());
        $this->class = get_called_class();
        $statement = self::$db->prepare('DESCRIBE `' . $this->dbname . '`');
        $statement->execute();
        $rows = $statement->fetchAll();
        foreach($rows as $row) {
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
        if ($variable !== 'createdate' && $variable !== 'changedate' && $variable !== 'id') {
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
        foreach($this->data as $field => $value){
            if ($field !== 'id') {
                $query .= '`' . $field . '` = :' . $field . ', ';
                $_data[':' . $field] = $value;
            }
        }
        if ($this->data['id'] !== null) {
            $query .= ' WHERE `id` = :' . $id; 
            $_data[':id'] = $value;
        }
        $query = substr($query, 0, -2);
        
        if ($this->data['id'] === null) {
            $query = 'INSERT INTO `' . $this->dbname . '` ' . $query;
        } else {
            $query = 'UPDATE `' . $this->dbname . '` ' . $query;
        }

        $statement = self::$db->prepare($query);
        $statement->execute($_data);

        if ($this->data['id'] === null) {
            $this->data['id'] = self::$db->lastInsertId();
        }
    }
    
    public function delete()
    {
        if ($this->data['id'] !== null) {
            $statement = self::$db->prepare('DELETE FROM `' . $this->dbname . '`' . ' WHERE `id` = :id');
            $statement->execute(array(':id' => $this->data['id']));
            $this->data['id'] = null;
        }
    }

    public static function getDBName($className)
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/' , '\1_\2', $className));
    }

    public static function findAll()
    {
        $class = get_called_class();
        $dbname = self::getDBName(get_called_class());
        $_query = 'SELECT * FROM `' . $dbname . '`';
       
        $statement = self::$db->prepare($_query);
        $statement->execute($query);
        $fetched = $statement->fetchAll(\PDO::FETCH_ASSOC);
        if ($fetched) {
            $list = array();
            foreach($fetched as $data) {
                array_push($list, new $class($data));
            }
            return $list;
        }
        return false;
    }

    public static function find($query, $data = null)
    {
        $class = get_called_class();
        $dbname = self::getDBName(get_called_class());
        $_query = 'SELECT * FROM `' . $dbname . '` WHERE ' . $query;
        $statement = self::$db->prepare($_query);
        $statement->execute($data);
        $fetched = $statement->fetchAll(\PDO::FETCH_ASSOC);
        if ($fetched) {
            $list = array();
            foreach($fetched as $data) {
                array_push($list, new $class($data));
            }
            return $list;
        }
        return false;
    }

    public static function findOne($query, $data = null)
    {
        $class = get_called_class();
        $dbname = self::getDBName(get_called_class());
        $_query = 'SELECT * FROM `' . $dbname . '` WHERE ' . $query . 'LIMIT 1';
        $statement = self::$db->prepare($_query);
        $statement->execute($query);
        
        $fetched = $statement->fetch(\PDO::FETCH_ASSOC);

        if ($fetched) {
            return new $class($fetched);
        }
        return false;
    }
}