<?php

namespace App\Http\Controllers;

// use Illuminate\Http\Request;
use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;

class DistDfeController extends Controller
{
    public function dist()
    {
        $configJson = json_encode(config('dfe.dfe_config'));
        $tools = new Tools($configJson, Certificate::readPfx(file_get_contents(storage_path(config('dfe.cert_path'))), config('dfe.cert_pass')));
        //só funciona para o modelo 55
        $tools->model('55');
        //este serviço somente opera em ambiente de produção
        $tools->setEnvironment(1);

        //este numero deverá vir do banco de dados nas proximas buscas para reduzir
        //a quantidade de documentos, e para não baixar várias vezes as mesmas coisas.
        $ultNSU = 0;
        $maxNSU = $ultNSU;
        $loopLimit = 2; //mantenha o numero de consultas abaixo de 20, cada consulta retorna até 50 documentos por vez
        $iCount = 0;

        //executa a busca de DFe em loop
        while ($ultNSU <= $maxNSU) {
            $iCount++;
            if ($iCount >= $loopLimit) {
                //o limite de loops foi atingido pare de consultar
                echo 'parou aqui';
                break;
            }
            try {
                //executa a busca pelos documentos
                $resp = $tools->sefazDistDFe($ultNSU);
            } catch (\Exception $e) {
                echo $e->getMessage();
                //pare de consultar e resolva o erro (pode ser que a SEFAZ esteja fora do ar)
                break;
            }

            //extrair e salvar os retornos
            $dom = new \DOMDocument();
            $dom->loadXML($resp);
            $node = $dom->getElementsByTagName('retDistDFeInt')->item(0);
            $tpAmb = $node->getElementsByTagName('tpAmb')->item(0)->nodeValue;
            $verAplic = $node->getElementsByTagName('verAplic')->item(0)->nodeValue;
            $cStat = $node->getElementsByTagName('cStat')->item(0)->nodeValue;
            $xMotivo = $node->getElementsByTagName('xMotivo')->item(0)->nodeValue;
            $dhResp = $node->getElementsByTagName('dhResp')->item(0)->nodeValue;
            $ultNSU = $node->getElementsByTagName('ultNSU')->item(0)->nodeValue;
            $maxNSU = $node->getElementsByTagName('maxNSU')->item(0)->nodeValue;
            $lote = $node->getElementsByTagName('loteDistDFeInt')->item(0);
            if (in_array($cStat, ['137', '656'])) {
                echo '137 - Nenhum documento localizado, a SEFAZ está te informando para consultar novamente após uma hora a contar desse momento<br>';
                echo '656 - Consumo Indevido, a SEFAZ bloqueou o seu acesso por uma hora pois as regras de consultas não foram observadas<br>';
                echo 'nesses dois casos pare as consultas imediatamente e retome apenas daqui a uma hora, pelo menos !!';
                break;
            }
            if (empty($lote)) {
                echo 'lote vazio';
                continue;
            }
            //essas tags irão conter os documentos zipados
            $docs = $lote->getElementsByTagName('docZip');
            foreach ($docs as $doc) {
                $numnsu = $doc->getAttribute('NSU');
                $schema = $doc->getAttribute('schema');
                //descompacta o documento e recupera o XML original
                $content = gzdecode(base64_decode($doc->nodeValue));
                //identifica o tipo de documento
                $tipo = substr($schema, 0, 6);
                //processar o conteudo do NSU, da forma que melhor lhe interessar
                //esse processamento depende do seu aplicativo
            }
            if ($ultNSU == $maxNSU) {
            //quando o numero máximo de NSU foi atingido não existem mais dados a buscar
            //nesse caso a proxima busca deve ser no minimo após mais uma hora
            break;
            }
            sleep(2);
        }
    }
}
