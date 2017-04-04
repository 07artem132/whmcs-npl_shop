<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use WHMCS\Database\Capsule;

function npl_shop_MetaData() {
    return array(
        'DisplayName' => 'npl_shop',
        'APIVersion' => '1.1', // Use API Version 1.1
        'RequiresServer' => FALSE, // Set true if module requires a server to work
    );
}

function npl_shop_CreateAccount(array $params) {
    try {
        if (Capsule::table('addon_npl_shop_files')->select('id')->where('prod_id', '=', $params['serviceid'])->first() != NULL) {
            throw new Exception('Данному продукту уже была назначена NPL лицензия');
        }

        $NPL_id = Capsule::table('addon_npl_shop_files')->select('id')->where('user_id', '=', null)->first();
        $SystemURL = Capsule::table('tblconfiguration')->select('value')->where('setting', '=', 'SystemUrl')->first();

        $leng = 20;
        $cstrong = true;

        $download_link = $SystemURL->value . 'index.php?m=npl_shop&downloadkey=' . base64_encode(sha1(md5($params['userid'] . $params['serviceid'] . bin2hex(openssl_random_pseudo_bytes($leng, $cstrong)))));
        $delete_link = $SystemURL->value . 'index.php?m=npl_shop&deletekey=' . base64_encode(sha1(md5($params['userid'] . $params['serviceid'] . bin2hex(openssl_random_pseudo_bytes($leng, $cstrong)))));

        $insert = [
            'user_id' => $params['userid'],
            'prod_id' => $params['serviceid'],
            'download_link' => $download_link,
            'delete_link' => $delete_link,
            'download_count' => 0,
            'updated_at' => date("Y-m-d H:i:s")
        ];

        Capsule::table('addon_npl_shop_files')->where('id', $NPL_id->id)->update($insert);
    } catch (Exception $e) {
        logModuleCall(
                'provisioningmodule', __FUNCTION__, $params, $e->getMessage(), $e->getTraceAsString()
        );
        return $e->getMessage();
    }
    return 'success';
}

function npl_shop_ClientArea(array $params) {

    try {
        $data = Capsule::table('addon_npl_shop_files')->select('delete_link', 'download_link')->where('prod_id', '=', $params['serviceid'])->first();

        return '<h2>Внимание! Если вы нажимаете "Удалить NPL из базы биллинга" то NPL удаляется из нашей базы на всегда, не каких копий мы не храним!</h2><br/>'
        . '<button class="btn btn-success"><a style="color: white;" href="' . $data->download_link . '">Скачать NPL из базы биллинга</a></button>'
                . ' <button  class="btn btn-danger"><a style="color: white;" href="' . $data->delete_link . '">Удалить NPL из базы биллинга</a> </button>';
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
                'provisioningmodule', __FUNCTION__, $params, $e->getMessage(), $e->getTraceAsString()
        );
        // In an error condition, display an error page.
        return array(
            'tabOverviewReplacementTemplate' => 'error.tpl',
            'templateVariables' => array(
                'usefulErrorHelper' => $e->getMessage(),
            ),
        );
    }
}
