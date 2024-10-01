<?php
//handle power status  
//Powebox_1
if (isset($_GET['get-pb1_status'])) {
    $pb1_url = '"http://10.148.100.6/cgi-bin/control2.cgi?%20&user=one&passwd=1234"';
    $pb1_get_status = "curl -X GET $pb1_url";
    $pb1_exec_get_status = shell_exec($pb1_get_status);
    // 解析 XML
    $pb1_xml = simplexml_load_string($pb1_exec_get_status);
	//網址內容關鍵字字串化
    $pb1_outletStatus = (string) $pb1_xml->outlet_status;
	//逗號隔開 取值
    $pb1_values = explode(",", $pb1_outletStatus);
	// 值為1 = on 反之
    $pb1_firstValue = trim($pb1_values[0]);
    $pb1_output1 = ($pb1_firstValue === "1") ? "On" : "Off";
    $pb1_firstValue2 = trim($pb1_values[1]);
    $pb1_output2 = ($pb1_firstValue2 === "1") ? "On" : "Off";
	//回傳變數到 button.php 的 function fetchPb1Status()
    echo json_encode(['pb1_output1' => $pb1_output1, 'pb1_output2' => $pb1_output2]);
}
//Powebox_2
if (isset($_GET['get-pb2_status'])) {
    $pb2_url = '"http://10.148.100.5/cgi-bin/control2.cgi?%20&user=one&passwd=1234"';
    $pb2_get_status = "curl -X GET $pb2_url";
    $pb2_exec_get_status = shell_exec($pb2_get_status);
    $pb2_xml = simplexml_load_string($pb2_exec_get_status);
    $pb2_outletStatus = (string) $pb2_xml->outlet_status;
    $pb2_values = explode(",", $pb2_outletStatus);
    $pb2_firstValue = trim($pb2_values[0]);
    $pb2_output1 = ($pb2_firstValue === "1") ? "On" : "Off";
    $pb2_firstValue2 = trim($pb2_values[1]);
    $pb2_output2 = ($pb2_firstValue2 === "1") ? "On" : "Off";

    echo json_encode(['pb2_output1' => $pb2_output1, 'pb2_output2' => $pb2_output2]);
}
?>
