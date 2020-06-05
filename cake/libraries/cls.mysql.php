<?php
/**
 * Note: 这个类是为了对之前所用的mysql类进行过度而进行封装的。将来，有可能在tinycake类中直接使用mysqli
 *
 */
class mysql
{
    // mysqli 对象
    public $mi;
    // 日志文件句柄
    public $logfile;
    // 链接mysql服务器的参数
    public $db;

    public $connected;

    public function __construct($server)
    {
        $this->connected = false;

        $this->last_sql = '';
        // 如果$server有配置，则用server,如果没有，则使用defcfg的默认配置
        $defcfg = array(
            "host"     => '127.0.0.1',
            "port"     => "3306",
            'username' => '',
            'password' => '',
            'database' => '',
            'socket'   => '',
            'charset'  => 'utf8',
        );

        if (!is_array($server)) {
            $server = array();
        }

        $res = array_diff_key($defcfg, $server);

        $this->logfile = "php://output";

        $this->db = array_merge($res, $server);

        // 建立链接
        try
        {
            $this->mi = new mysqli(
                $this->db['host'], $this->db['username'], $this->db['password'],
                $this->db['database'], $this->db['port'], $this->db['socket']
            );
        } catch (Exception $e) {
            die('hhh');
        }

        if (!isset($this->errno) || $this->errno === null) {
            $this->connected = true;
            $this->mi->set_charset($this->db['charset']);
        }
    }

    public function isConnected()
    {
        return $this->connected;
    }

    public function setLogfile($logfile = 'php://output')
    {
        $this->logfile = $logfile;
    }

    public function connect()
    {
        $ret = $this->mi->real_connect(
            $this->db['host'],
            $this->db['username'],
            $this->db['password'],
            $this->db['database'],
            $this->db['port'],
            $this->db['socket']
        );

        if ($ret) {
            $this->mi->set_charset($this->db['charset']);
        } else {
            $this->show_error($this->mi->connect_error);
        }
    }

    public function select_db($database)
    {
        // inherent
        $this->mi->select_db($database);
    }

    public function get_error()
    {
        if ($this->mi->connect_errno) {
            $error['sql']         = $this->mi->escape_string;
            $error['number']      = $this->mi->connect_errno;
            $error['description'] = $this->mi->connect_error;
        } else {
            $error['sql']         = $this->mi->escape_string;
            $error['number']      = $this->mi->errno;
            $error['description'] = $this->mi->error;
        }
        return $error;
    }

    public function show_error($title)
    {
        $content = "--------------------\n";
        $content .= $title . "\n";
        $content .= $this->mi->connect_errno ? $this->mi->connect_error : $this->mi->error;
        $content .= "\n";
        $content .= "$this->last_sql\n";

        error_log($content, 3, $this->logfile);

        return $content;
    }

    public function affected_rows()
    {
        // inherent
        return $this->mi->affected_rows;
    }

    public function query($sql)
    {
        // result maybe false
        $this->last_sql = $sql;
        $this->lastresult = $this->mi->query($sql);
        return $this->lastresult;
    }

    public function fetch_row($result)
    {
        if ($result) {
            $row = $result->fetch_row();
            return $row;
        } else {
            return false;
        }

    }

    public function insert_id()
    {
        // inherent
        return $this->mi->insert_id;
    }

    public function num_rows($result)
    {

        return $result->num_rows();
    }

    public function fetch_object($result)
    {
        $ret = $result->fetch_object();

        return $ret;
    }

    public function fetch_array($result)
    {
        $ret = $result->fetch_array();
        return $ret;
    }

    public function fetch_assoc($result)
    {
        $ret = $result->fetch_assoc();
        return $ret;
    }

    public function fetch_one_object($sql)
    {
        $query = $this->query($sql);

        if ($query) {
            return $this->fetch_object($query);
        } else {
            return false;
        }

    }

    public function fetch_one_array($sql)
    {
        $list = $this->query($sql);

        if ($list) {
            $list_array = $this->fetch_array($list);
            return $list_array;
        } else {
            return false;
        }

    }

    public function fetch_one_assoc($sql)
    {

        $list = $this->query($sql);

        if ($list) {
            $list_array = $this->fetch_assoc($list);
            return $list_array;
        } else {
            return false;
        }

    }

    public function fetch_all_array($sql, $max = 0)
    {
        if (is_string($sql)) {
            $query = $this->query($sql);
        } else {
            $query = $sql;
        }

        $all_array = array();
        while ($list_item = $this->fetch_array($query)) {
            $current_index++;

            if ($current_index > $max && $max != 0) {
                break;
            }

            $all_array[] = $list_item;

        }

        return $all_array;
    }

    public function fetch_all_assoc($sql, $max = 0)
    {
        $current_index = 0;
        $all_array     = array();

        if (is_string($sql)) {
            $this->last_sql = $sql;
            $query = $this->mi->query($sql);

            if ($query === false) {
                return false;
            }
        } else if (is_object($sql)) {
            $query = $sql;
        } else {
            return false;
        }

        /*
        if($query===false)
        {
        return false;
        }
         */

        while ($list_item = $query->fetch_assoc()) {

            $current_index++;

            if ($current_index > $max && $max != 0) {
                break;
            }

            $all_array[] = $list_item;

        }

        return $all_array;
    }

    public function fetch_all_object($sql, $max = 0)
    {
        $current_index = 0;
        $result        = $this->query($sql);

        $all_array = array();
        if ($result) {
            /* fetch object array */
            while ($obj = $result->fetch_object()) {
                $current_index++;

                if ($current_index > $max && $max != 0) {
                    break;
                }

                $all_array[] = $obj;
            }

            /* free result set */
            $result->close();
            return $all_array;
        } else {
            return false;
        }

    }

    /**
     * 定义添加数据的方法
     * @param string $table 表名
     * @param string orarray $data [数据]
     * @return int 最新添加的id
     */
    public function insert($table, $data)
    {
        //遍历数组，得到每一个字段和字段的值
        $key_str = '';
        $v_str   = '';

        if( !$data || count($data)<=0 )
            return false;
        
        foreach ($data as $key => $v) {
            if (empty($v)) {
                //值为0时会有BUG
                if (!is_numeric($v)) {

                    if (is_array($v)) {

                        if (empty($v)) {
                            continue;
                        }
                    }

                    continue;
                }
            }

            //$key的值是每一个字段s一个字段所对应的值
            $key_str .= '`' . $key . '`,';

            if (stripos($v, "PASSWORD(") === 1) {

                $v_str .= " $v,";
            } else {
                $v_str .= "'$v',";
            }
        }
        $key_str = trim($key_str, ',');
        $v_str   = trim($v_str, ',');

        //判断数据是否为空
        $sql = "insert into $table ($key_str) values ($v_str)";

        $ret = $this->query($sql);

        //返回insert结果
        #return $ret;
        return $this->insert_id();
    }

/**
 * [修改操作description]
 * @param [type] $table [表名]
 * @param [type] $data [数据]
 * @param [type] $where [条件]
 * @return [type]
 */
    public function update($table, $data, $where)
    {

//遍历数组，得到每一个字段和字段的值
        $str = '';
        foreach ($data as $key => $v) {
            $str .= "$key='$v',";
        }
        $str = rtrim($str, ',');
//修改SQL语句
        $sql = "update $table set $str where $where";
// echo $sql;die;
        $this->query($sql);
//返回受影响的行数
        if ($this->affected_rows() >= 0) {
            return 1;
        } else {

            return 0;
        }
    }

    /**
     * 定义添加数据的方法
     * @param string $table 表名
     * @param string orarray $data [数据]
     * @return int 最新添加的id
     */
    public function replaceInto($table, $data)
    {
//遍历数组，得到每一个字段和字段的值
        $key_str = '';
        $v_str   = '';

        foreach ($data as $key => $v) {
            if (empty($v)) {
                //值为0时会有BUG
                if (!is_numeric($v)) {
                    return false;

                }

            }

//$key的值是每一个字段s一个字段所对应的值
            $key_str .= '`' . $key . '`,';

            if (stripos($v, "PASSWORD(") === 1) {

                $v_str .= " $v,";
            } else {
                $v_str .= "'$v',";
            }
        }
        $key_str = trim($key_str, ',');
        $v_str   = trim($v_str, ',');

//判断数据是否为空
        $sql = "replace into $table ($key_str) values ($v_str)";

        #echo $sql;die;
        $this->query($sql);

        //返回上一次增加操做产生ID值
        $insertID=$this->insert_id();
        if ($insertID) {

            return $insertID;
        } else {

            if ($this->affected_rows() >= 0) {
                return 1;
            } else {

                return 0;
            }
        }
    }

}
