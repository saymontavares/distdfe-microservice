<?php

    return [
        'cert_path' => env('CERT_PATH', 'certificate.pfx'),
        'cert_pass' => env('CERT_PASS', 'password'),
        'dfe_config' => [
            'atualizacao' => date('Y-m-d H:i:s'),
            'tpAmb' => (int) env('NF_TPAMB', 2), // 1 - producao, 2 - homologacao
            'razaosocial' => 'ASSOCIACAO BENEFICENTE DA INDUSTRIA CARBONIFERA D:83649830000171',
            'siglaUF' => 'SC',
            'cnpj' => '83649830000171',
            'schemes' => '',
            'versao' => '4.00',
            'tokenIBPT' => '',
            'CSC' => '',
            'CSCid' => '',
            'aProxyConf' => [
                    'proxyIp' => '',
                    'proxyPort' => '',
                    'proxyUser' => '',
                    'proxyPass' => ''
            ],
            'cmun' => '4204608', // CodigoMunicipio
            'im' => '8277' // InscricaoMunicipal
        ]
    ];