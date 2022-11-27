<?php

require "config.php";
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
set_time_limit(0);
ini_set('max_execution_time', 0);
ini_set('memory_limit', '-1');

use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;
use Box\Spout\Writer\WriterFactory;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$writer = WriterFactory::create(Type::XLSX);

$slim->get('/provinces', function ($request, $response) use ($db) {
    $provinces = $db->ms_zipcode->select("distinct province")->order("province asc");
    $result = array();
    foreach ($provinces as $p) {
        $result[] = $p["province"];
    }
    return $response->withJson($result);
});

$slim->get('/citys', function ($request, $response) use ($db) {
    $province = $request->getParam("province");
    $citys = $db->ms_zipcode->where("province=?", $province)->select("distinct city")->order("city asc");
    $result = array();
    foreach ($citys as $c) {
        $result[] = $c["city"];
    }
    return $response->withJson($result);
});

$slim->get('/kecamatans', function ($request, $response) use ($db) {
    $province = $request->getParam("province");
    $city = $request->getParam("city");
    $datas = $db->ms_zipcode->where("province=?", $province)->where("city=?", $city)->select("distinct kecamatan")->order("kecamatan asc");
    $result = array();
    foreach ($datas as $data) {
        $result[] = $data["kecamatan"];
    }
    return $response->withJson($result);
});
$slim->get('/kelurahans', function ($request, $response) use ($db) {
    $province = $request->getParam("province");
    $city = $request->getParam("city");
    $kecamatan = $request->getParam("kecamatan");
    $datas = $db->ms_zipcode->where("province=?", $province)->where("city=?", $city)->where("kecamatan=?", $kecamatan)->select("distinct kelurahan")->order("kelurahan asc");
    $result = array();
    foreach ($datas as $data) {
        $result[] = $data["kelurahan"];
    }
    return $response->withJson($result);
});

$slim->get('/postalcodes', function ($request, $response) use ($db) {
    $province = $request->getParam("province");
    $city = $request->getParam("city");
    $kecamatan = $request->getParam("kecamatan");
    $kelurahan = $request->getParam("kelurahan");
    $datas = $db->ms_zipcode
            ->where("province=?", $province)
            ->where("city=?", $city)
            ->where("kecamatan=?", $kecamatan)
            ->where("kelurahan=?", $kelurahan)
            ->select("distinct postal_code");
    $result = array();
    foreach ($datas as $data) {
        $result[] = $data["postal_code"];
    }
    return $response->withJson($result);
});

$slim->get('/coverage', function ($request, $response) use ($db) {
    $province = $request->getParam("province");
    $city = $request->getParam("city");
    $kecamatan = $request->getParam("kecamatan");
    $datas=$db->coverage->where("province=?",$province)->where("city=?",$city)->where("kecamatan=?",$kecamatan);
    if(count($datas)!=0){
        $result=array(
            "coverage"=>true,
            "message"=>"Alamat pengiriman terjangkau oleh kurir"
        );
    }else{
        $result=array(
            "coverage"=>false,
            "message"=>"Alamat pengiriman tidak terjangkau oleh kurir. Silakan menggunakan alamat cabang DNR"
        );
    }
    return $response->withJson($result);
});

function export($data, $writer) {
    $file = "export/data_" . date("Y-m-d_H:i:s", strtotime("+7 hours", strtotime(date("Y-m-d H:i:s")))) . ".xlsx";
    $writer->openToFile($file);
    $writer->addRow(array("company", "nik", "nama", "alamat", "provinsi", "kota", "kecamatan", "kelurahan", "kode pos", "nomor handphone", "informasi tambahan"));
    foreach ($data as $d) {
        $writer->addRow(array($d["company"], $d["nik"], $d["nama"], $d["alamat"], $d["provinsi"], $d["kota"], $d["kecamatan"], $d["kelurahan"], $d["kode_pos"], $d["phone_number"], $d["informasi_tambahan"]));
    }
    $writer->close();
    return $file;
}

$slim->run();
?>