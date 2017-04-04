<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use WHMCS\Database\Capsule;

function npl_shop_config() {
    $configarray = array(
        "name" => "NPL shop",
        "description" => " ",
        "version" => "1.0",
        "author" => "service-voice",
        "fields" => array()
    );
    return $configarray;
}

function npl_shop_activate() {
    try {
        Capsule::schema()->create('addon_npl_shop_files', function ($table) {
            $table->increments('id');
            $table->integer('user_id')->nullable();
            $table->longText('npl_file');
            $table->text('download_link');
            $table->integer('download_count');
            $table->timestamps();
        });
    } catch (\Exception $e) {
        return array('status' => 'error', 'description' => 'Ошибка при создании таблицы');
    }
    return array('status' => 'success', 'description' => 'Таблица была создана');
}

function npl_shop_deactivate() {
    try {
        Capsule::Schema()->drop('addon_npl_shop_files');
    } catch (\Exception $e) {

        return array('status' => 'error', 'description' => 'Ошибка при удалении таблицы:' . $e->getMessage());
    }
    return array('status' => 'success', 'description' => 'Таблица была удалена');
}

function npl_shop_output($vars) {
    $smarty = new Smarty();

    if (!isset($_GET['tab']) || empty($_GET['tab']) || $_GET['tab'] == 'index') {
        $smarty->assign("tab1", 'class="active"');
    } else {
        $smarty->assign("tab2", 'class="active"');
    }

    $smarty->display('file:' . __DIR__ . '/template/nav.tpl');

    if (!isset($_GET['tab']) || empty($_GET['tab']) || $_GET['tab'] == 'index') {
        $npl_list = Capsule::table('addon_npl_shop_files')->select('id', 'user_id', 'npl_file', 'download_count', 'download_link', 'delete_link', 'created_at')->get();

        for ($index = 0; $index < count($npl_list); $index++) {
            $npl_list[$index] = get_object_vars($npl_list[$index]);

            if ($npl_list[$index]['delete_link'] != null) {
                $npl_list[$index]['delete_link'] = '<a href="' . $npl_list[$index]['delete_link'] . '">удалить npl</a>';
                $npl_list[$index]['download_link'] = '<a href="' . $npl_list[$index]['download_link'] . '">Скачать npl</a>';
            }
        }

        $smarty->assign("npl_list", $npl_list);
        $smarty->display('file:' . __DIR__ . '/template/index.tpl');
    }

    if ($_GET['tab'] == 'add') {
        $smarty->display('file:' . __DIR__ . '/template/add.tpl');
    }

    if ($_GET['tab'] == 'save') {
        if (isset($_POST['npl']) && !empty($_POST['npl'])) {
            Capsule::table('addon_npl_shop_files')->insert(
                    [
                        'npl_file' => $_POST['npl'],
                        'created_at' => date("Y-m-d H:i:s")
                    ]
            );
            echo 'ok';
            return;
        }
        echo 'error';
    }
}

function npl_shop_clientarea($vars) {
    if (isset($_GET['downloadkey']) && !empty($_GET['downloadkey'])) {
        $NPL_FILE = Capsule::table('addon_npl_shop_files')->select('npl_file', 'download_count')->where('download_link', 'LIKE', '%' . $_GET['downloadkey'] . '%')->first();

        if ($NPL_FILE == NULL) {
            throw new Exception('File not found');
        }

        Capsule::table('addon_npl_shop_files')->where('download_link', 'LIKE', '%' . $_GET['downloadkey'] . '%')->update(['download_count' => $NPL_FILE->download_count + 1]);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=licensekey.dat');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($NPL_FILE->npl_file));
        echo $NPL_FILE->npl_file;
    }

    if (isset($_GET['deletekey']) && !empty($_GET['deletekey'])) {
        $NPL_ID = Capsule::table('addon_npl_shop_files')->select('id')->where('delete_link', 'LIKE', '%' . $_GET['deletekey'] . '%')->delete();
        echo 'Файл удален';
    }
    die();
}
