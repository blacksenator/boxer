<?php

namespace blacksenator;

use \SimpleXMLElement;
use \DOMDocument;
use \DOMXPath;
use blacksenator\FritzBox\Api;
use blacksenator\fbvalidateurl\fbvalidateurl;
use blacksenator\fritzsoap\fritzsoap;

function getRouterAccess($config)
{
    // validate FRITZ!Box adress
    $validator = new fbvalidateurl;
    $url = $validator->getValidURL($config['url']);

    // login
    $fritz = new Api($url['scheme'] . '://' . $url['host']);
    $fritz->setAuth($config['user'], $config['password']);
    $fritz->mergeClientOptions($config['http'] ?? []);
    $fritz->login();

    return $fritz;
}

/**
 * converting the HTML response into a SimpleXMLElement
 *
 * @param string $response
 * @return SimpleXMLElement $xmlSite
 */
function convertHTMLtoXML($response)
{
    $dom = new DOMDocument();
    $dom->preserveWhiteSpace = false;
    $dom->loadHTML($response);
    $xmlSite = simplexml_import_dom($dom);

    return $xmlSite;
}

/**
 * set new filter to designated device
 *
 * @param array $config
 */
function setKidsFilter($config)
{
    $fritz = getRouterAccess($config);
    // get request (fetching data)
    $response = $fritz->getData('/internet/kids_userlist.lua');

    // convert html response into SimpleXML
    $xmlSite = convertHTMLtoXML($response);

    // delete comment for debugging
    // $xmlSite->asXML('site_GET.xml');

    // initialize processing values
    $devices = [];
    $options = [];
    $filters = [];

    // parse SimpleXML with xpath to get current data
    $rows = $xmlSite->xpath('//tr/td[@title=@datalabel]');  // these are the rows with assignments of devices to filters
    foreach ($rows as $row) {
        $key = utf8_decode((string)$row->attributes()['title']);    // name (label) of the devices
        if (preg_match('/Alle /', $key)) {                          // skip standard settings
            continue;
        }
        $select = $row->xpath('parent::*//select[@name]');  // find the line with the currently assigned ID for the device
        $value = (string)$select[0]->attributes()['name'];  // get the current ID ('profile:user*' or 'profile:landevice*')
        $devices[$key] = $value;

        $options = $select[0]->xpath('option');             // the defined filters (dropdown in each row)
        foreach ($options as $option) {
            $profiles[utf8_decode((string)$option)] = (string)$option->attributes()['value'];   // get label and ID of filters
            if (isset($option->attributes()['selected'])) {     // determine the filter currently assigned to the device
                $filters[$value] = (string)$option->attributes()['value'];  // get device (ID) and filter (ID)
            }
        }
    }

    // delete comments for debugging
    /*
    file_put_contents('arrays.txt', print_r($profiles, true));
    file_put_contents('arrays.txt', print_r($devices, true), FILE_APPEND);
    file_put_contents('arrays.txt', print_r($filters, true), FILE_APPEND);
    */

    if (!array_key_exists($config['device'], $devices)) {
        throw new \Exception(sprintf('The Device %s is not defined in your FRITZ!Box', $config['device']));
    }
    if (!array_key_exists($config['filter'], $profiles)) {
        throw new \Exception(sprintf('The Filter %s is not defined in your FRITZ!Box', $config['filter']));
    }

    // pick the right IDs
    $deviceID = $devices[$config['device']];
    $profilID = $profiles[$config['filter']];

    // assamble the request data
    $formParams = [
        'form_params' => [
            'sid' => '',
            $deviceID => $profilID,
            'apply' => '',
        ]
    ];

    // post the request
    $result = $fritz->postData($formParams, '/internet/kids_userlist.lua');

    // delete comments for debugging
    /*
    $dom->loadHTML($result);
    $xmlSite = simplexml_import_dom($dom);
    $xmlSite->asXML('site_POST.xml');
    */
}

function getMeshList($config)
{
    $fritzbox = new fritzsoap($config['url'], $config['user'], $config['password']);
    /* delete comment to get the example of service list:
    $services = $fritzbox->getServiceDescription();
    $services->asXML('services.xml'); */

    $fritzbox->getClient('hosts');
    $meshList = $fritzbox->getMeshListPath();

    // delete comments for debugging
    // $meshList->asXML('meshlist.xml');

    $nodeIntfs = $meshList->xpath("//node_interfaces/*[starts-with(local-name(), 'item')]");
    foreach ($nodeIntfs as $nodeIntf) {
        if ((string)$nodeIntf->name == $config['connection']) {
            $result = (string)$nodeIntf->mac_address;
            break;
        }
    }

    return $result;
}