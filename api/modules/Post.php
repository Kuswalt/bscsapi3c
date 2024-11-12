<?php
class Post {
  private $pdo;

  public function __construct(\PDO $pdo) {
    $this->pdo = $pdo;
  }

  public function insertRecord($param) {
    $dt = $param->payload[0];
    print($dt->studno);
    $sqlString = "INSERT INTO students (studno, fname, mname, lname, birthdate, email) VALUES (?,?,?,?,?,?)";
    $res = [];
    try {
      $stmt = $this->pdo->prepare($sqlString);
      $stmt->execute([
        $dt->studno,
        $dt->fname,
        $dt->mname,
        $dt->lname,
        $dt->birthdate,
        $dt->email
      ]);
    } catch (\Throwable $th) {
      $res = array(
        "msg"=>"Unable to fetch data", 
        "error"=>$th->getMessage(),
        "code"=>$th->getCode()
      );
    }
    return $res;
  }

  public function updateRecord($param) {
    if ($param === null) {
        return [
            "msg" => "No data provided for update.",
            "error" => "Invalid input",
            "code" => 400
        ];
    }

    $sqlString = "UPDATE students SET fname=?, mname=?, lname=?, birthdate=?, email=? WHERE studno=?";
    $res = [];
    try {
        $stmt = $this->pdo->prepare($sqlString);
        $stmt->execute([
            $param->fname,
            $param->mname,
            $param->lname,
            $param->birthdate,
            $param->email,
            $param->studno
        ]);
    } catch (\Throwable $th) {
        $res = array(
            "msg" => "Unable to fetch data",
            "error" => $th->getMessage(),
            "code" => $th->getCode()
        );
    }
    return $res;
  }

  public function deleteRecord($param) {
    $sqlString = "DELETE FROM students WHERE studno=?";
    $res = [];
    try {
      $stmt = $this->pdo->prepare($sqlString);
      $stmt->execute([$param]);
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