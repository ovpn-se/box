<?php
/**
 * Created by PhpStorm.
 * User: davidwibergh
 * Date: 15-04-21
 * Time: 20:52
 */

namespace OpenVPN;

class ClientConfig {

    public function generate($ip, $type)
    {
        $config_types = array(
            'normal' => array(
                'ports' => array(
                    1194,
                    1195
                ),
            ),
            'filtering' => array(
                'ports' => array(
                    1198,
                    1199
                )
            ),
            'public-ipv4' => array(
                'ports' => array(
                    1196,
                    1197
                )
            ),
            'multihop' => array(
                'ports' => array(
                    1201,
                    1202
                )
            )
        );

        if(!isset($config_types[$type])) {
            return false;
        }

        $vpnId   = 1;
        $customId = 1;

        saveOpenVPNConfig($vpnId, $customId, $ip, $config_types[$type]['ports']);

        return true;

    }
} 