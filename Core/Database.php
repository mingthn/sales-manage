<?php

namespace Core;

use App\Config;

/**
 * Base model
 *
 * PHP version 7.0
 */
class Database
{
    
    private $conn;
    /**
     * __construct Hàm khởi tạo
     * @return void;
     */
    public function __construct()
    {
        $this->connect();
    }
    /**
     * connect Kết nối
     * @return void
     */
    public function connect()
    {
        if(!$this->conn){
            $this->conn = mysqli_connect(Config::DB_HOST, Config::DB_USER, Config::DB_PASSWORD, Config::DB_NAME);
            if (mysqli_connect_errno()) {
                echo 'Failed: '. mysqli_connect_error();
                die();
            }
            mysqli_set_charset($this->conn,'utf8');
        }
    }
    /**
     * disConnect Ngắt kết nối
     * @return void
     */
    public function disConnect()
    {
        if($this->conn)
            mysqli_close($this->conn);
    }
    /**
     * error Hiển thị lỗi
     * @return string
     */
    public function error()
    {
        if($this->conn)
            return mysqli_error($this->conn);
        else
            return false;
    }
    /**
     * insert thêm dữ liẹu
     * @param string $table tên bảng muốn thêm, array $data dữ liệu cần thêm
     * @return boolean
     */
    public function login($table = '', $email = '', $password = '')
    {
        $pass = md5($password);
        $sql = "SELECT * FROM " .$table. " WHERE email = '" .$email. "' AND password = '".$pass."'";
        $query = mysqli_query($this->conn,$sql);
        if(mysqli_num_rows($query)>0){
            $result = mysqli_fetch_array($query);
            return $result;
        }else{
            return false;
        }
    }
    public function search($table='', $cols = [], $data = [])
    {
        $sql = 'SELECT * FROM ' .$table. ' WHERE ';
        foreach ($cols as $col) {
            $sql .= $col. ' LIKE "'.'%'.$data.'%" OR ';
        }
        $sql = trim($sql, ' OR ');
        $data=[];
        $query = mysqli_query($this->conn,$sql);
        if($query){
            while($row = mysqli_fetch_array($query)){
                $data[] = $row;
            }
            return $data;
        }else{
            return false;
        }
    }
    public function insert($table = '', $data = [])
    {
        $keys = '';
        $values= '';
        foreach ($data as $key => $value) {
            $keys .= ',' . $key;
            $values .= ',"' . mysqli_real_escape_string($this->conn,$value).'"';
        }
        $sql = 'INSERT INTO ' .$table . '(' . trim($keys,',') . ') VALUES (' . trim($values,',') . ')';
        return mysqli_query($this->conn,$sql);
    }
    /**
     * update sửa dữ liệu
     * @param string $table tên bảng muốn sửa, array $data dữ liệu cần sửa, array|int $id điều kiện
     * @return boolean
     */
    public function update($table = '',$data = [], $id =[])
    {
        $content = '';
        if(is_integer($id))
            $where = 'id = '.$id;
        else if(is_array($id) && count($id)==1){
            $listKey = array_keys($id);
            $where = $listKey[0].'='.$id[$listKey[0]];
        }
        else
            die('Không thể có nhiều hơn 1 khóa chính và id truyền vào phải là số');
        foreach ($data as $key => $value) {
            $content .= ','. $key . '="' . mysqli_real_escape_string($this->conn,$value).'"';
        }
        $sql = 'UPDATE ' .$table .' SET '.trim($content,',') . ' WHERE ' . $where ;
        return mysqli_query($this->conn,$sql);
    }
    /**
     * delete xóa dữ liệu
     * @param string $table tên bảng muốn xóa, array|int điều kiện
     * @return boolean
     */
    public function delete($table= '', $id = [])
    {
        $content = '';
        if(is_integer($id))
            $where = 'id = '.$id;
        else if(is_array($id) && count($id)==1){
            $listKey = array_keys($id);
            $where = $listKey[0].'='.$id[$listKey[0]];
        }
        else
            die('Không thể có nhiều hơn 1 khóa chính và id truyền vào phải là số');
        $sql = 'DELETE FROM ' . $table . ' WHERE '. $where;
        return mysqli_query($this->conn,$sql);
    }
    /**
     * getObject lấy hết dữ liệu trong bảng trả về mảng đối tượng
     * @param string $table tên bảng muốn lấy ra dữ liệu
     * @return array objetc
     */
    public function getObject($table = '')
    {
        $sql = 'SELECT * FROM '. $table;
        $data = null;
        if($result = mysqli_query($this->conn,$sql)){
            while($row = mysqli_fetch_object($result)){
                $data[] = $row;
            }
            mysqli_free_result($result);
            return $data;
        }
        return false;
    }
    /**
     * getObject lấy hết dữ liệu trong bảng trả về mảng dữ liệu
     * @param string $table tên bảng muốn lấy dữ liệu
     * @return array
     */
    public function getArray($table = '')
    {
        $sql = 'SELECT * FROM '. $table;
        $data = null;
        if($result = mysqli_query($this->conn,$sql)){
            while($row = mysqli_fetch_array($result)){
                $data[] = $row;
            }
            mysqli_free_result($result);
            return $data;
        }
        else
            return false;
    }
    /**
     * getRowObject lấy một dòng dữ liệu trong bảng trả về mảng dữ liệu
     * @param string $table tên bảng muốn lấy dữ liệu, array|int $id điều kiện
     * @return object
     */
    public function getRowObject($table = '', $id = [])
    {
        if(is_integer($id))
            $where = 'id = '.$id;
        else if(is_array($id) && count($id)==1){
            $listKey = array_keys($id);
            $where = $listKey[0].'='.$id[$listKey[0]];
        }
        else
            die('Không thể có nhiều hơn 1 khóa chính và id truyền vào phải là số');
        $sql = 'SELECT * FROM '. $table . ' WHERE '. $where;
        
        if($result = mysqli_query($this->conn,$sql)){
            $data = mysqli_fetch_object($result);
            mysqli_free_result($result);
            return $data;
        }
        else
            return false;
    }
    /**
     * getRowArray lấy một dòng dữ liệu trong bảng trả về mảng dữ liệu
     * @param string $table tên bảng muốn lấy dữ liệu, array|int $id điều kiện
     * @return array
     */
    public function getRowArray($table = '', $id = [])
    {
        if(is_integer($id))
            $where = 'id = '.$id;
        else if(is_array($id) && count($id)==1){
            $listKey = array_keys($id);
            $where = $listKey[0].'='.$id[$listKey[0]];
        }
        else
            die('Không thể có nhiều hơn 1 khóa chính và id truyền vào phải là số');
        $sql = 'SELECT * FROM '. $table . ' WHERE '. $where;
        
        if($result = mysqli_query($this->conn,$sql)){
            $data = mysqli_fetch_array($result);
            mysqli_free_result($result);
            return $data;
        }
        else
            return false;
    }
    /**
     * query thực hiện query
     * @param string $sql
     * @return boolean|array
     */
    public function query($sql ='', $return = true)
    {
        if($result = mysqli_query($this->conn,$sql))
        {
            if($return === true){
                while ($row = mysqli_fetch_array($result)) {
                    $data[] = $row;
                }
                mysqli_free_result($result);
                return $data;
            }
            else
                return true;
        }
        else
            return false;
    }
    /**
     * __destruct hàm hủy
     * @param none
     * @return void
     */
    public function __destruct()
    {
        $this->disConnect();
    }
}