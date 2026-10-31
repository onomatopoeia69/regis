<?php 

 namespace App\Models;

 use App\Core\Model;

 class EnrollmentNotification extends Model
 {

    public $tableName = 'enr_notifications';
    public $primaryKey = 'id';

 public static function __callStatic($name, $arguments)
    {
            $instance = new self();     
            return $instance->$name(...$arguments);
    }

 }