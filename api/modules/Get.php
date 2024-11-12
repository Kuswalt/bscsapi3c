<?php
class Get {
  private $pdo;

  public function __construct(\PDO $pdo) {
    $this->pdo = $pdo;
  }

  public function getStudents($param) {
    $sqlString = "SELECT * FROM students";
    if($param) {
      $sqlString.= " WHERE studno=?";
    }
    $res = [];
    try {
      $stmt = $this->pdo->prepare($sqlString);
      $stmt->execute($param?[$param]:null);
      $res = $stmt->fetchAll();
    } catch (\Throwable $th) {
      $res = array(
        "msg"=>"Unable to fetch data", 
        "error"=>$th->getMessage(),
        "code"=>$th->getCode()
      );
    }
    return $res;
  }
}