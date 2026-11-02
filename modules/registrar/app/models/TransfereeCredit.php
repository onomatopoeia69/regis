<?php 

namespace App\Models;

use App\Core\Model;


class TransfereeCredit extends Model
{
  
    public $tableName = 'rgr_student_subject_evaluations';
    public $primaryKey = 'id';


    


     public static function __callStatic($name, $arguments)
    {
            $instance = new self();     
            return $instance->$name(...$arguments);
    }
    
 
 

}