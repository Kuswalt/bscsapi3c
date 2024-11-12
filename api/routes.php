<?php
  require_once("./config/Connection.php");
  require_once("./modules/Get.php");
  require_once("./modules/Post.php");
  require_once("./modules/Auth.php");

  $db = new Connection();
  $pdo = $db->connect();
  $get = new Get($pdo);
  $post = new Post($pdo);
  $auth = new Auth($pdo);

  if (isset($_REQUEST['request'])) {
    $req = explode('/', rtrim($_REQUEST['request'], '/'));
  } else {
    $req = array("errorcatcher");
  }

  switch($_SERVER['REQUEST_METHOD']) {
    case 'GET':
      switch($req[0]) {
        case 'getstudents':
          echo json_encode($get->getStudents($req[1] ?? null));
          break;
        default:
          echo json_encode(["error" => "No public API available"]);
      }
    break;

    case 'POST':
      $d = json_decode($auth->decryptData(file_get_contents("php://input")));
      switch($req[0]) {
        case 'getstudents':
          // echo $auth->encryptData($get->getStudents($req[1]));
          echo json_encode($get->getStudents($req[1]));
        break;

        case 'addstudent':
          echo json_encode($post->insertRecord($d));
        break;

        case 'deletestudent':
          echo json_encode($post->deleteRecord($req[1]));
        break;

        case 'updatestudent':
          echo json_encode($post->updateRecord($d));
        break;

        // Sample
        // case 'encryptpword':
        //   echo json_encode($auth->encryptPassword("Sample Password"));
        // break;

        // case 'encryptdata':
        //   echo json_encode($auth->encryptData(array("data"=>"Hello World")));
        // break;

        // case 'decryptdata':
        //   echo json_encode($auth->decryptData("eyJkYXRhIjoicG55RW5Jak5RMXpSdzJKTXUzVVRjVjgyQUtqXC9aUVc5b1dKWHNEVkxZT0U9IiwiaXYiOiJPREU0WXpBMk1UbGhPREEzTW1OaE5qVmxNVGd3TURrMU5tUm1ZMlE0WWpFPSJ9"));
        // break;
      }
    break;

    default:
      echo "NA";
  }