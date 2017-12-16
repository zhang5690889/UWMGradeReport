<?php
require 'vendor/autoload.php';

// Create and configure Slim app
$config = ['settings' => [
    'addContentLengthHeader' => false,
]];
$app = new \Slim\App($config);

// Get container
$container = $app->getContainer();

// Register template engine on container
$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig('views', [
        'cache' => false
    ]);
    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));
    return $view;
};


//DB configuration
function connect_db() {
    $servername = "localhost";
    $username   = "zhang5690889";
    $password   = "";
    $dbname     = "c9";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        echo( $conn->connect_error);
    }
	return $conn;
}


// Define app routes
$app->get('/', function ($request, $response, $args) {
    
    $conn=connect_db();
    $semasters = "SELECT distinct Stu_Strm_Ldesc FROM record ORDER BY Stu_Strm_Ldesc";
    $semasters = $conn->query($semasters);
    
    $majors = "SELECT distinct Cla_Subject_Ldesc FROM record ORDER BY Cla_Subject_Ldesc";
    $majors = $conn->query($majors);
        
    while ($row = $semasters->fetch_assoc()) {
        $semastersResult[] = $row["Stu_Strm_Ldesc"];
    }
  
   while ($row = $majors->fetch_assoc()) {
        $majorsResult[] = $row["Cla_Subject_Ldesc"];
    }
  
    return $this->view->render($response, 'index.html',[
        "semasters"=>$semastersResult,
        "majors"=>$majorsResult
        ]);
});

$app->post('/recordBySmt', function ($request, $response, $args) {

    $conn=connect_db();
    $allPostPutVars = $request->getParsedBody();
    $smt=$allPostPutVars['smt'];
    $major=$allPostPutVars['major'];
    
    $data = "select * from record where Stu_Strm_Ldesc='".$smt."'And Cla_Subject_Ldesc='".$major."'And Cla_Instructor_Name!='Course Total'";
    $data = $conn->query($data);
    $emparray = array();
    while($row =mysqli_fetch_assoc($data))
    {
        $emparray[] = $row;
    }
    $emparray=array('data'=>$emparray);
    return $response->withJson($emparray);
});

// Run app
$app->run();