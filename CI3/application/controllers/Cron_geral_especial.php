<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Cron_geral_especial extends CI_Controller
{
    // Situacao_fiscal_ecac_procuracao/cron_situacao_fiscal_com_procuracao/".$nomeBanco;
    // Situacao_cadin_ecac_procuracao/cron_pendencia_cadin_com_procuracao/".$nomeBanco;
    // Mensagens_ecac_procuracao/buscar_ecac_com_procuracao/".$nomeBanco;
    // Das_ecac_procuracao/cron_das/".$nomeBanco;
    // Dctf_ecac_procuracao/cron_dctf/".$nomeBanco;
    // vencimento_procuracao/cron_procuracao/".$nomeBanco;
    // eprocessos_procuracao/buscar/".$nomeBanco;

    // Cron_simplesnacional_pedidos_parcelamento_procuracao/buscar/".$nomeBanco;
    // Simplesnacional_parcela_procuracao/buscar_parcelas/".$nomeBanco;
    // Cron_pert_pedidos_parcelamento_procuracao/buscar/".$nomeBanco;
    // Parcelamento_pert_procuracao/buscar_parcelas/".$nomeBanco;
    // Das_debitos_ecac_procuracao/cron_das_debitos/".$nomeBanco;
    // Cron_parcelamento_lei_12996_procuracao/buscar/".$nomeBanco;

    // Cron_parcelamento_nao_previdenciario_procuracao/buscar/".$nomeBanco;
    private $banco;
    private $params;

    private $myhashmap = array();

    function buscar_teste()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

         
            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);

                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);
            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }

    function buscar_01()
    {
        include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

         
            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);

                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);
            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean(); 
        $this->buscar_02($this->banco);
    }

    function buscar_02($banco)
    {
        // include('PdfToText/PdfToText.phpclass');
        ob_start();
        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_2($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                 // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                 // $this->buscar_simplesnacional_parcelas($item->cnpj);
                 // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                 //  $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                 // $this->buscar_parcelamento_lei_12996($item->cnpj);
                 // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                 // $this->buscar_parcelamento_mei($item->cnpj);
                 // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_03($this->banco);
    }

    function buscar_03($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_3($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);

                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_04($this->banco);
    }

    function buscar_04($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_4($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);

                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_05($this->banco);
    }

    function buscar_05($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_5($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);

                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_06($this->banco);
    }

    function buscar_06($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_6($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);

                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_07($this->banco);
    }

    function buscar_07($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_7($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);

                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_08($this->banco);
    }

    function buscar_08($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_8($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);

                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_09($this->banco);
    }

    function buscar_09($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_9($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);

                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_010($this->banco);
    }

    function buscar_010($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_10($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);

                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_011($this->banco);
    }

    function buscar_011($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_11($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);

                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_012($this->banco);
    }

    function buscar_012($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_12($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);
                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_013($this->banco);
    }

    function buscar_013($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_13($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);
                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_014($this->banco);
    }

    function buscar_014($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_14($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);
                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_015($this->banco);
    }

    function buscar_015($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_15($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);

                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_016($this->banco);
    }

    function buscar_016($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_16($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);

                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_017($this->banco);
    }

    function buscar_017($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_018($this->banco);
    }

    function buscar_018($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_1($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_019($this->banco);
    }

    function buscar_019($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_2($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_020($this->banco);
    }

    function buscar_020($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_3($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_21($this->banco);
    }

    function buscar_21($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_4($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_22($this->banco);
    }

    function buscar_22($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_5($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_23($this->banco);
    }

    function buscar_23($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_6($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_24($this->banco);
    }

    function buscar_24($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_7($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_25($this->banco);
    }

    function buscar_25($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_8($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_26($this->banco);
    }

    function buscar_26($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_9($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_27($this->banco);
    }

    function buscar_27($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_10($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_28($this->banco);
    }

    function buscar_28($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_11($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_29($this->banco);
    }

    function buscar_29($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_12($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_30($this->banco);
    }

    function buscar_30($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_13($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_31($this->banco);
    }

    function buscar_31($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_14($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_32($this->banco);
    }

    function buscar_32($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_15($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_33($this->banco);
    }

    function buscar_33($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_16($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_34($this->banco);
    }

    // CRONS DESTINADOS A NOVAS FUNÇÕES
    function buscar_34($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);
                // $this->grava_conteudo_mensagem($item->cnpj);



            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_35($this->banco);
    }

    function buscar_35($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_1($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);
                // $this->grava_conteudo_mensagem($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_36($this->banco);
    }

    function buscar_36($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_2($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);
                // $this->grava_conteudo_mensagem($item->cnpj);

            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_37($this->banco);
    }

    function buscar_37($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_3($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);
                // $this->grava_conteudo_mensagem($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_38($this->banco);
    }

    function buscar_38($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_4($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);
                // $this->grava_conteudo_mensagem($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_39($this->banco);
    }

    function buscar_39($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_5($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);
                // $this->grava_conteudo_mensagem($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_40($this->banco);
    }

    function buscar_40($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_6($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);
                // $this->grava_conteudo_mensagem($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_41($this->banco);
    }

    function buscar_41($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_7($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);
                // $this->grava_conteudo_mensagem($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_42($this->banco);
    }

    function buscar_42($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_8($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);
                // $this->grava_conteudo_mensagem($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_43($this->banco);
    }

    function buscar_43($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_9($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);
                // $this->grava_conteudo_mensagem($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_44($this->banco);
    }

    function buscar_44($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_10($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);
                // $this->grava_conteudo_mensagem($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_45($this->banco);
    }

    function buscar_45($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_11($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);
                // $this->grava_conteudo_mensagem($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_46($this->banco);
    }

    function buscar_46($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_12($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);
                // $this->grava_conteudo_mensagem($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean();
        $this->buscar_47($this->banco);
    }

    function buscar_47($banco)
    {
        // include('PdfToText/PdfToText.phpclass');

        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_13($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);
                // $this->grava_conteudo_mensagem($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        ob_end_clean(); 
        $this->buscar_extra_01($this->banco);
    }

    function buscar_48()
    {
        // include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_nao_rodou($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);



            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }

    function buscar_49()
    {
        // include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_51($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);

                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }

    function buscar_50()
    {
        // include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_1($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);

                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }

    private function buscar_situacao_fiscal_ecac($cnpj)
    {

        $folder_pdf = FCPATH . 'arquivos/pdf-certidao-ecac-sp/' . $this->banco . '/';

        if (!file_exists($folder_pdf)) {
            mkdir($folder_pdf, DIR_WRITE_MODE, true);
        }

        $params = $this->params;
        $params['caminho_da_pasta_pdfs'] =  $folder_pdf;

        $this->load->library('Ecac/SituacaoFiscalProcuracao', $params, 'ecac_robo_situacao_fiscal_ecac');
       $this->ecac_robo_situacao_fiscal_ecac->set_numero_documento_procuracao($cnpj);

        /**
         * Grava a situação fiscal e o pdf
         */
        $path_pdf = $this->ecac_robo_situacao_fiscal_ecac->baixar_pdf_situacao_fiscal();

        if (empty($path_pdf)) {
            echo "<br>Busca Situação fiscal ECAC - ERRO: pdf invalido - CNPJ: $cnpj";
            return;
        }

        $carregado = false;
        $pdf = new PdfToText();

        try {
            $pdf->LoadFromString(file_get_contents($path_pdf));
            $carregado = true;
        } catch (Exception $e) {
            return;
        }

        $texto_base = "Pendência -";
        $pos = strpos($pdf->Text, $texto_base);

        $possui_pendencia =  false;

        if ($pos !== false)
            $possui_pendencia = true;

        $caminho_download = $path_pdf;

        $existe_situacao = $this->verifica_se_existe_situacao($this->ecac_robo_situacao_fiscal_ecac->obter_numero_documento(), $this->banco);
        if ($existe_situacao > 0) {
            $this->update_situacao_fiscal($possui_pendencia, '', $caminho_download, $this->ecac_robo_situacao_fiscal_ecac->obter_numero_documento(), $this->banco);
        } else {
            $this->inserir_situacao_fiscal($possui_pendencia, '', $caminho_download, $this->ecac_robo_situacao_fiscal_ecac->obter_numero_documento(), $this->banco);
        }

        echo "<br>==============SUCESSO NA OPERAÇÃO==========";
        echo "<br>";
        echo "<br>Documento: {$this->ecac_robo_situacao_fiscal_ecac->obter_numero_documento()}";
        echo "<br>";
        $mensagem_pendencia = $possui_pendencia ? "<br>Possui pendência." : "<br>Não foram encontradas pêndencias.";
        echo "<br>ituação Fiscal: {$mensagem_pendencia}";
        echo "<br>";
        echo "<br>PDF situação: {$caminho_download}";
        echo "<br>";
        echo "<br>===========================================";
        echo "<br>";
        // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection

        unset($this->ecac_robo_situacao_fiscal_ecac);
        echo "<br>Busca Situação fiscal ECAC concluído";
    }

    private function verifica_se_existe_situacao($cnpj_data, $banco)
    {
        $this->load->model('situacao_fiscal_model');
        $resultado = $this->situacao_fiscal_model->verifica_se_existe($cnpj_data, $banco);
        return $resultado->qtd;
    }

    private function inserir_situacao_fiscal($possui_pendencia, $path_pdf, $caminho_download, $cnpj_data, $banco)
    {
        $this->load->model('situacao_fiscal_model');
        $this->situacao_fiscal_model->insert($possui_pendencia, '', $caminho_download, $cnpj_data, $banco);
    }

    private function update_situacao_fiscal($possui_pendencia, $path_pdf, $caminho_download, $cnpj_data, $banco)
    {
        $this->load->model('situacao_fiscal_model');
        $this->situacao_fiscal_model->update($possui_pendencia, '', $caminho_download, $cnpj_data, $banco);
    }

    private function buscar_situacao_cadin($cnpj)
    {

        $this->load->model('situacao_cadin_model');

        $folder_pdf = FCPATH . 'arquivos/pdf-cadin-ecac-sp/' . $this->banco . '/';

        if (!file_exists($folder_pdf)) {
            mkdir($folder_pdf, DIR_WRITE_MODE, true);
        }

        $params = $this->params;
        $params['caminho_da_pasta_pdfs'] =  $folder_pdf;

        $this->load->library('Ecac/Cadin', $params, 'ecac_robo_library_eprocessos_procuracao_cadin');
        $this->ecac_robo_library_eprocessos_procuracao_cadin->set_numero_documento_procuracao($cnpj);
 
        $path_pdf = $this->ecac_robo_library_eprocessos_procuracao_cadin->baixar_pdf_cadin();
        echo $path_pdf . '<br>';

        if (empty($path_pdf))
            return;

        $pdf = new PdfToText();
        $carregado = false;
        try {
            $pdf->LoadFromString(file_get_contents($path_pdf));
            $carregado = true;
        } catch (Exception $e) {
            $carregado = false;
        }

        if ($carregado) {
            $texto_base = "NÃO INCLUÍDO PELA RFB";
            $pos = strpos($pdf->Text, $texto_base);

            $texto_base2 = "EXCLUÍDO PELA RFB";
            $pos2 = strpos($pdf->Text, $texto_base2);

            $possui_pendencia =  true;

            if ($pos !== false)
                $possui_pendencia = false;

            if ($pos2 !== false)
                $possui_pendencia = false;

            $caminho_download = $path_pdf;

            $existe_situacao = $this->situacao_cadin_model->verifica_se_existe($this->ecac_robo_library_eprocessos_procuracao_cadin->obter_numero_documento(), $this->banco);

            if ($existe_situacao->qtd > 0) {
                $this->situacao_cadin_model->update($possui_pendencia, '', $caminho_download, $this->ecac_robo_library_eprocessos_procuracao_cadin->obter_numero_documento(), $this->banco);
            } else {
                $this->situacao_cadin_model->insert($possui_pendencia, '', $caminho_download, $this->ecac_robo_library_eprocessos_procuracao_cadin->obter_numero_documento(), $this->banco);
            }
        }
        // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
        unset($this->ecac_robo_library_eprocessos_procuracao_cadin);
        echo "<br>Busca Situação fiscal Cadin concluído";
    }


    //MENSAGENS
    private function buscar_mensagens_ecac($cnpj)
    {
        $this->load->library('Ecac/Mensagem', $this->params, 'ecac_robo_library_mensagens_ecac');
        $this->ecac_robo_library_mensagens_ecac->set_numero_documento_procuracao($cnpj);

        echo "==============CNPJ ATUAL - {$cnpj}==========\n";
        /**
         * Grava as mensagens da caixa postal
         */
        $caixa_postal = $this->ecac_robo_library_mensagens_ecac->obter_mensagem_caixa_postal();
        if ($caixa_postal)
            $this->inserir_caixa_postal($caixa_postal, $this->ecac_robo_library_mensagens_ecac->obter_numero_documento(), $this->banco);

        /**
         * Grava as parcelas do simples nacional
         */

        // $parcelas = $this->ecac_robo_library_mensagens_ecac->obter_simples_nacional_emissao_parcela();
        // if($parcelas)
        //     $this->inserir_parcelas_emitidas($parcelas, $this->ecac_robo_library_mensagens_ecac->obter_numero_documento(), $banco);
        /**
         * Grava se possui pedidos de parcelas ou não
         */

        // $possui_pedidos = $this->ecac_robo_library_mensagens_ecac->obter_simples_nacional_pedidos_parcela();
        // if($possui_pedidos)
        //     $this->inserir_consulta_pedidos($possui_pedidos, $this->ecac_robo_library_mensagens_ecac->obter_numero_documento(), $banco);
        /**
         * Emite mensagem de sucesso e resumo da operação
         */
        echo "==============SUCESSO NA OPERAÇÃO==========\n";
        // echo "Documento: {$this->ecac_robo_library_mensagens_ecac->obter_numero_documento()}\n";
        // $mensagem_pendencia = $possui_pendencia ? "Possui pendência." : "Não foram encontradas pêndencias.";
        // echo "Situação Fiscal: {$mensagem_pendencia}\n";
        // echo "PDF situação: {$path_pdf}\n";
        echo "===========================================\n";

        // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
        unset($this->ecac_robo_library_mensagens_ecac);
        echo "<br>Busca Mensagens ECAC concluído";
    }

    public function grava_conteudo_mensagem($cnpj){
        $this->load->library('Ecac/Mensagem', $this->params, 'ecac_robo_library_mensagens_ecac');
        $this->ecac_robo_library_mensagens_ecac->set_numero_documento_procuracao($cnpj);

        $this->load->model('caixa_postal_mensagem_model', 'caixa_postal_mensagem');

        $mensagens = $this->caixa_postal_mensagem->busca_mensagens_ja_lidas($cnpj, $this->banco);
        foreach ($mensagens as $m) {
            $conteudo = $this->ecac_robo_library_mensagens_ecac->buscar_conteudo_mensagem($m->codigo_receita_ecac);
            if(!empty($conteudo)){
                $this->caixa_postal_mensagem->update_conteudo($m->id, $conteudo, $this->banco);
            }
        }

        unset($this->ecac_robo_library_mensagens_ecac);
        echo "<br>Busca Mensagens ECAC concluído";
    }

    private function inserir_caixa_postal($data, $cnpj_data, $banco)
    {

        $this->load->model('caixa_postal_mensagem_model', 'caixa_postal_mensagem');
        $this->load->model('caixa_postal_model', 'caixa_postal');

        $result = $this->caixa_postal->existe_caixa_postal($cnpj_data, $banco);
        if ($result->qtd > 0) {
            $caixa_postal_id = $result->id;
            $this->caixa_postal->update($data, $cnpj_data, $caixa_postal_id, $banco);
        } else {
            $caixa_postal_id = $this->caixa_postal->insert($data, $cnpj_data, $banco);
        }

        $this->caixa_postal_mensagem->limpaTabelaMensagens($caixa_postal_id, $banco);

        foreach ($data['mensagens'] as $mensagem) {
            $mensagem['caixa_postal_id'] = $caixa_postal_id;
            $this->caixa_postal_mensagem->insert($mensagem, $banco);
        }
    }

    private function buscar_das_ecac_procuracao($cnpj)
    {
        $this->load->library('Ecac/SimplesNacional/Pgdas', $this->params, 'ecac_robo_library_procuracao_pgdas');
        $this->ecac_robo_library_procuracao_pgdas->set_numero_documento_procuracao($cnpj);

        $this->load->model('das_model');

        $folder_pdf = FCPATH . 'arquivos/pdf-das-ecac-sp/' . $this->banco . '/';

        if (!file_exists($folder_pdf)) {
            mkdir($folder_pdf, DIR_WRITE_MODE, true);
        }

        $lista_das = $this->ecac_robo_library_procuracao_pgdas->obter_pgdas();
        // print_r($lista_das);
        if ($lista_das && count($lista_das) > 0){
            foreach ($lista_das as $dados) {
                $dados['cnpj'] = $cnpj;
                $existe = $this->das_model->verifica_se_existe($dados['numero_declaracao'], $this->banco, $cnpj, $dados['numero_das']);

                if ($existe->qtd > 0) {
                    $this->das_model->update($dados, $this->banco);
                } else {
                    $this->das_model->insert($dados, $this->banco);
                }

                //gerar recibo
                // if ((!isset($existe->caminho_download_recibo) || empty($existe->caminho_download_recibo))) {
                //     try {
                //         $caminho_download = $this->ecac_robo_library_procuracao_pgdas->obter_recibo($folder_pdf, $dados['numero_declaracao'], date('Y'));
                //         echo "<br>({$dados['compentencia']})recibo: $caminho_download";
                //         if ($caminho_download != "") {
                //             $this->das_model->update_caminho_download_recibo($caminho_download, $dados['numero_declaracao'], $this->banco);
                //         }
                //     } catch (Exception $e) {
                //         echo $e->getMessage();
                //     }
                // }
                //gerar declaração
                if ((!isset($existe->caminho_download_declaracao) || empty($existe->caminho_download_declaracao))) {
                    try {
                        $caminho_download = $this->ecac_robo_library_procuracao_pgdas->obter_declaracao($folder_pdf, $dados['numero_declaracao'], date('Y'));
                        echo "<br>({$dados['compentencia']})declaracao: $caminho_download";
                        if ($caminho_download != "") {
                            $this->das_model->update_caminho_download_declaracao($caminho_download, $dados['numero_declaracao'], $this->banco);
                        }
                    } catch (Exception $e) {
                        echo $e->getMessage();
                    }
                }
                // //extrato
                // if ((!isset($existe->caminho_download_extrato) || empty($existe->caminho_download_extrato))) {
                //     try {
                //         $caminho_download = $this->ecac_robo_library_procuracao_pgdas->obter_extrato($folder_pdf, $dados['numero_declaracao'], $dados['numero_das'], date('Y'));
                //         echo "<br>({$dados['compentencia']})extrato: $caminho_download<br>";
                //         if ($caminho_download != "") {
                //             $this->das_model->update_caminho_download_extrato($caminho_download, $dados['numero_declaracao'], $this->banco);
                //         }
                //     } catch (Exception $e) {
                //         echo $e->getMessage();
                //     }
                // }

            }

            // gerar das
            // foreach ($lista_das as $dados) {
            //     $das_pago = $this->das_model->verifica_se_pago($this->banco, $cnpj, $dados['compentencia']);
            //     //se o das for == 0 é pq não foi pago
            //     if ($das_pago->qtd == 0) {
            //         $existe = $this->das_model->verifica_se_existe_caminho_download_das($this->banco, $cnpj, $dados['compentencia']);
            //         echo '<br>'.substr($dados['compentencia'], 3);
            //         try {
            //             if ((!isset($existe->caminho_download_das) || empty($existe->caminho_download_das))) {
            //                 $caminho_download = $existe->caminho_download_das;
            //             } else {
            //                 $tentativa = 5;
            //                 while ($tentativa) {
            //                     $tentativa--;

            //                     $caminho_download = $this->ecac_robo_library_procuracao_pgdas->obter_das($folder_pdf, substr($dados['compentencia'], 3));

            //                     if ($caminho_download != "")
            //                         $tentativa = 0;
            //                 }
            //             }

            //             echo "<br>gerar das: $caminho_download";
            //             if ($caminho_download != "") {
            //                 $this->das_model->update_caminho_download_das($caminho_download, $dados['numero_declaracao'], $this->banco);
            //             }
            //         } catch (Exception $e) {
            //             echo $e->getMessage();
            //         }
            //     }
            // }
        
       }
        // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
        unset($this->ecac_robo_library_procuracao_pgdas);
        echo "<br>Busca PGDAS concluído";
    }

    private function buscar_dctf_ecac_procuracao($cnpj)
    {
        $this->load->library('Ecac/Dctf', $this->params, 'ecac_robo_library_procuracao_dctf');
        $this->ecac_robo_library_procuracao_dctf->set_numero_documento_procuracao($cnpj);


        $dctf = $this->ecac_robo_library_procuracao_dctf->get_dctf($this->myhashmap);
        foreach ($dctf as $item_aux) {
            echo "CNPJ: {$cnpj} - inserido";
            $this->dctf_model->insert($item_aux, $this->banco);
        }

        // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
        unset($this->ecac_robo_library_procuracao_dctf);
        echo "<br>Busca DCFT concluído";
    }

    private function buscar_vencimento_procuracao($cerficado)
    {
        /**
         * Carrega a library principal Ecac_robo_library
         */
        $params = array(
            'caminho_certificado' => 'https://veri-sp.com.br/crons-api/' . str_replace('//', '/', $cerficado->caminho_arq),
            'cerficado_senha' => $cerficado->pass,
            'caminho_da_pasta_pdfs' => ''
        );
        $this->load->library('Ecac_robo_library_eprocessos_procuracao', $params);
        $this->load->library('Ecac/SimplesNacional/Pgdas', $this->params, 'ecac_robo_library_procuracao_pgdas');

        /**
         * PROCURAÇÃO consulta
         */
        $procuracoes = $this->ecac_robo_library_eprocessos_procuracao->get_procuracoes();
        if ($procuracoes) {

            foreach ($procuracoes as $item) {
                $this->procuracao_model->insert($cerficado->cnpj_data, $item, $this->banco);
            }
        }
        unset($this->ecac_robo_library_eprocessos_procuracao);
        echo "<br>Busca vencimento procuração concluído";
    }

    private function buscar_eprocessos_procuracao($cnpj)
    {
        $this->load->model('eprocessos_ativos_historico_model');
        $this->load->model('eprocessos_ativos_model');
        $this->load->model('eprocessos_inativos_historico_model');
        $this->load->model('eprocessos_inativos_model');


        $this->load->library('Ecac/Eprocessos', $this->params, 'ecac_robo_library_procuracao_eprocessos');
        $this->ecac_robo_library_procuracao_eprocessos->set_numero_documento_procuracao($cnpj);

        $ativos = $this->ecac_robo_library_procuracao_eprocessos->get_eprocessos_ativos();
        if ($ativos) {
            foreach ($ativos as $ativo) {
                if (!isset($ativo['id']))
                    continue;
                $this->eprocessos_ativos_model->insert($cnpj, $ativo, $this->banco);

                $historico_lista = $this->ecac_robo_library_procuracao_eprocessos->get_eprocesso_historico($ativo['numero']);
                foreach ($historico_lista as $historico) {
                    $this->eprocessos_ativos_historico_model->insert($historico, $ativo['id'], $this->banco);
                }
            }
        }

        $inativos = $this->ecac_robo_library_procuracao_eprocessos->get_eprocessos_inativos();
        print_r($inativos);

        if ($inativos) {

            foreach ($inativos as $inativo) {
                if (!isset($inativo['id']))
                    continue;
                $this->eprocessos_inativos_model->insert($cnpj, $inativo, $this->banco);

                $historico_lista = $this->ecac_robo_library_procuracao_eprocessos->get_eprocesso_historico($inativo['numero']);
                foreach ($historico_lista as $historico) {
                    $this->eprocessos_inativos_historico_model->insert($historico, $inativo['id'], $this->banco);
                }
            }
        }

        unset($this->ecac_robo_library_eprocessos_procuracao);
        echo "<br>Busca eprocessos procuração concluído";
    }

    private function buscar_simplesnacional_pedidos_parcelamento($cnpj)
    {
        $this->load->model('Simplesnacional_debitos_parcelas_model');
        $this->load->model('Simplesnacional_demonstrativo_pagamentos_model');
        $this->load->model('Simplesnacional_pedidos_parcelamentos_model');

        $folder_pdf = FCPATH . 'arquivos/recibos-parcelamento-simplesnacional/' . $this->banco . '/';

        if (!file_exists($folder_pdf)) {
            mkdir($folder_pdf, DIR_WRITE_MODE, true);
        }

        $this->load->library('Ecac/SimplesNacional/Parcelamento', $this->params, 'ecac_robo_library_procuracao_simplesnacional');


        $registros = $this->ecac_robo_library_procuracao_simplesnacional->obter_parcelamento();
        // echo '<pre>';
        // print_r($registros); echo'</pre>'; die();
        foreach ($registros as $registro) {

            if($registro['situacao'] == "Em parcelamento"){
                $registro['cnpj'] = $cnpj;

                $existe_pedido = $this->Simplesnacional_pedidos_parcelamentos_model->verifica_se_existe($registro['cnpj'], $this->banco, $registro['numero']);

                if ($existe_pedido->qtd > 0) {
                    $id_parcelamento = $existe_pedido->id;
                    $this->Simplesnacional_pedidos_parcelamentos_model->update($registro['cnpj'], $this->banco, $registro['numero'],  $registro['situacao']);
                } else {
                    if ($registro['situacao'] != 'Em parcelamento')
                        continue;
                    $id_parcelamento = $this->Simplesnacional_pedidos_parcelamentos_model->insert($registro, $this->banco);
                }

                $existe_debitos_parcelas = $this->Simplesnacional_debitos_parcelas_model->verifica_se_existe($registro['cnpj'], $this->banco, $id_parcelamento);

                if ($existe_debitos_parcelas->qtd <= 0) {
                    foreach ($registro['relacao_debitos_parcelas'] as $rdp) {
                        // $this->Simplesnacional_debitos_parcelas_model->clear($registro['cnpj'], $banco);
                        $rdp['cnpj'] = $cnpj;
                        $rdp['id_parcelamento'] = $id_parcelamento;
                        $this->Simplesnacional_debitos_parcelas_model->insert($rdp, $this->banco);
                    }
                }


                foreach ($registro['demonstrativo_pagamentos'] as $dp) {
                    $dp['cnpj'] = $cnpj;
                    $dp['id_parcelamento'] = $id_parcelamento;

                    $existe_pagamento = $this->Simplesnacional_demonstrativo_pagamentos_model->verifica_se_existe($dp['cnpj'], $this->banco, $id_parcelamento, $dp['mes_parcela']);
                    if ($existe_pagamento->qtd <= 0) {
                        $this->Simplesnacional_demonstrativo_pagamentos_model->insert($dp, $this->banco);
                    }
                    // $this->Simplesnacional_demonstrativo_pagamentos_model->clear($registro['cnpj'], $banco);
                }
            }else{
                $registro['cnpj'] = $cnpj;
                $this->Simplesnacional_pedidos_parcelamentos_model->update($registro['cnpj'], $this->banco, $registro['numero'],  $registro['situacao']);
            }
            
        }
        //Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
        unset($this->ecac_robo_library_procuracao_simplesnacional);
        echo "<br>Busca simplesnacional pedidos parcelamento concluído";
    }

    private function buscar_simplesnacional_parcelas($cnpj)
    {
        $this->load->model('Simplesnacional_emissao_parcela_model');

        $this->load->library('Ecac/SimplesNacional/Parcelamento', $this->params, 'ecac_robo_library_procuracao_simplesnacional_parcela');

        $parcelas = $this->ecac_robo_library_procuracao_simplesnacional_parcela->obter_simples_nacional_emissao_parcela();

        if ($parcelas) {
            foreach ($parcelas as $parcela) {

                $result = $this->Simplesnacional_emissao_parcela_model->verifica_se_existe($this->banco, $cnpj, $parcela['data_parcela']);
                if ($result->qtd > 0) {
                    $this->Simplesnacional_emissao_parcela_model->update($cnpj, $this->banco, $parcela);
                } else {
                    $this->Simplesnacional_emissao_parcela_model->insert($cnpj, $this->banco, $parcela);
                }

                // if (!isset($result->path_download_parcela) || empty($result->path_download_parcela)) {
                //     try {
                //         $this->baixar_pdf_simplesnacional($this->banco, $cnpj, trim($parcela['data_parcela']));
                //     } catch (Exception $e) {
                //         echo $e->getMessage();
                //     }
                // }
            }
        }
        // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
        unset($this->ecac_robo_library_procuracao_simplesnacional_parcela);
        echo "<br>Busca parcela simplesnacional concluído";
    }

    private function baixar_pdf_simplesnacional($banco, $cnpj, $data_parcela)
    {
        $caminho_download = $this->ecac_robo_library_procuracao_simplesnacional_parcela->gerar_parcela_simplesnacional($data_parcela);
        if ($caminho_download != "" && $caminho_download != 'ERROECAC') {
            echo "<br> $caminho_download";
            $this->Simplesnacional_emissao_parcela_model->update_path($banco, $data_parcela, $cnpj, $caminho_download);
            return $caminho_download;
        }else echo "<br> $caminho_download";

    }

    private function buscar_pert_pedidos_parcelamento($cnpj)
    {
        $this->load->model('Pert_debitos_parcelas_model');
        $this->load->model('Pert_demonstrativo_pagamentos_model');
        $this->load->model('Pert_pedidos_parcelamentos_model');

        $folder_pdf = FCPATH . 'arquivos/recibos-parcelamento-pert/' . $this->banco . '/';

        if (!file_exists($folder_pdf)) {
            mkdir($folder_pdf, DIR_WRITE_MODE, true);
        }
        $this->params['caminho_da_pasta_pdfs']=$folder_pdf;
        $this->load->library('Ecac/SimplesNacional/Parcelamento_pert', $this->params, 'ecac_robo_library_procuracao_pert');

        $registros = $this->ecac_robo_library_procuracao_pert->obter_parcelamento();
 
        foreach ($registros as $registro) {
            $registro['cnpj'] = $cnpj;

            $existe_pedido = $this->Pert_pedidos_parcelamentos_model->verifica_se_existe($registro['cnpj'], $this->banco, $registro['numero']);

            if ($existe_pedido->qtd > 0) {
                $id_parcelamento = $existe_pedido->id;
                $this->Pert_pedidos_parcelamentos_model->update($registro['cnpj'], $this->banco, $registro['numero'],  $registro['situacao']);
            } else {
                if ($registro['situacao'] != 'Em parcelamento')
                    continue;
                $id_parcelamento = $this->Pert_pedidos_parcelamentos_model->insert($registro, $this->banco);
            }


            $existe_debitos_parcelas = $this->Pert_debitos_parcelas_model->verifica_se_existe($registro['cnpj'], $this->banco, $id_parcelamento);

            if ($existe_debitos_parcelas->qtd <= 0) {
                foreach ($registro['relacao_debitos_parcelas'] as $rdp) {
                    // $this->Pert_debitos_parcelas_model->clear($registro['cnpj'], $this->banco);
                    $rdp['cnpj'] = $cnpj;
                    $rdp['id_parcelamento'] = $id_parcelamento;
                    $this->Pert_debitos_parcelas_model->insert($rdp, $this->banco);
                }
            }


            foreach ($registro['demonstrativo_pagamentos'] as $dp) {
                $dp['cnpj'] = $cnpj;
                $dp['id_parcelamento'] = $id_parcelamento;

                $existe_pagamento = $this->Pert_demonstrativo_pagamentos_model->verifica_se_existe($dp['cnpj'], $this->banco, $id_parcelamento, $dp['mes_parcela']);
                if ($existe_pagamento->qtd <= 0) {
                    $this->Pert_demonstrativo_pagamentos_model->insert($dp, $this->banco);
                }
                // $this->Pert_demonstrativo_pagamentos_model->clear($registro['cnpj'], $banco);
            }
        }
        // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
        unset($this->ecac_robo_library_procuracao_pert);
        echo "<br>Busca pert pedidos parcelamento concluído";
    }

    private function buscar_pert_parcelas($cnpj)
    {
        $this->load->model('Parcelas_pert_model');

        $this->load->library('Ecac/SimplesNacional/Parcelamento_pert', $this->params, 'ecac_robo_library_procuracao_pert_parcelas');

        $parcelas = $this->ecac_robo_library_procuracao_pert_parcelas->obter_parcelas_pert();

        if ($parcelas) {
            foreach ($parcelas as $parcela) {

                $result = $this->Parcelas_pert_model->verifica_se_existe($this->banco, $cnpj, $parcela['data_parcela']);
                if ($result->qtd > 0) {
                    $this->Parcelas_pert_model->update($cnpj, $this->banco, $parcela);
                } else {
                    $this->Parcelas_pert_model->insert($cnpj, $this->banco, $parcela);
                }
                // if (!isset($result->path_download_parcela) || empty($result->path_download_parcela)) {
                //     try {
                //         $this->baixar_pdf_pert($this->banco, $cnpj, trim($parcela['data_parcela']));
                //     } catch (Exception $e) {
                //         echo $e->getMessage();
                //     }
                // }
            }
        }
        // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
        unset($this->ecac_robo_library_procuracao_pert_parcelas);
        echo "<br>Busca pert parcelas concluído";
    }

    private function baixar_pdf_pert($banco, $cnpj, $data_parcela)
    {
        $caminho_download = $this->ecac_robo_library_procuracao_pert_parcelas->gerar_parcela_pert($data_parcela);
        echo "<br>$caminho_download";
        if ($caminho_download != "") {
            $this->Parcelas_pert_model->update_path($banco, $data_parcela, $cnpj, $caminho_download);
            return $caminho_download;
        }
    }

    private function buscar_parcelamento_nao_previdenciario($cnpj)
    {
        $folder_pdf = FCPATH . 'arquivos/parcelamento-nao-prividenciario/' . $this->banco . '/';

        $this->params['caminho_da_pasta_pdfs'] = $folder_pdf;

        $this->load->library('Ecac/SimplesNacional/Parcelamento_nao_previdenciario', $this->params, 'ecac_robo_library_procuracao_nao_previdenciario');

        $this->load->model('Parcelamento_nao_previdenciario_processos_negociados_model');
        $this->load->model('Parcelamento_nao_previdenciario_tributos_do_processo_negociados_model');
        $this->load->model('Parcelamento_nao_previdenciario_demonstrativo_das_parcelas_model');

        $registros = $this->ecac_robo_library_procuracao_nao_previdenciario->processos_negociados();

        foreach ($registros as $registro) {
            $registro['cnpj'] = $cnpj;

            $existe_registro = $this->Parcelamento_nao_previdenciario_processos_negociados_model->verifica_se_existe($registro['cnpj'], $this->banco, $registro['processo']);

            if ($existe_registro->qtd > 0) {
                $id_processo = $existe_registro->id;
                if ($registro['situacao'] != 'Parcelado')
                    $this->Parcelamento_nao_previdenciario_processos_negociados_model->update($registro['cnpj'], $this->banco, $registro['processo'],  $registro['situacao']);
            } else {
                if ($registro['situacao'] != 'Parcelado')
                    continue;
                $id_processo = $this->Parcelamento_nao_previdenciario_processos_negociados_model->insert($registro, $this->banco);
            }

            foreach ($registro['tributos_do_processo_negociados'] as $tributos_do_processo) {
                $existe_tributos_processos_negociados = $this->Parcelamento_nao_previdenciario_tributos_do_processo_negociados_model->verifica_se_existe($registro['cnpj'], $this->banco, $id_processo, $tributos_do_processo['tributo']);
                $tributos_do_processo['id_processo'] = $id_processo;
                $tributos_do_processo['cnpj'] = $cnpj;

                if ($existe_tributos_processos_negociados->qtd > 0) {
                    $id_tributo = $this->Parcelamento_nao_previdenciario_tributos_do_processo_negociados_model->update($tributos_do_processo, $this->banco);
                } else {
                    $id_tributo = $this->Parcelamento_nao_previdenciario_tributos_do_processo_negociados_model->insert($tributos_do_processo, $this->banco);
                }

                foreach ($tributos_do_processo['demonstrativo_das_parcelas'] as $demonstrativo) {
                    $result =  $this->Parcelamento_nao_previdenciario_demonstrativo_das_parcelas_model->verifica_se_existe($registro['cnpj'], $this->banco, $id_tributo, $demonstrativo['numero_parcela']);
                    $demonstrativo['id_tributo'] = $id_tributo;
                    $demonstrativo['cnpj'] = $cnpj;

                    if ($result->qtd > 0) {
                        $this->Parcelamento_nao_previdenciario_demonstrativo_das_parcelas_model->update($demonstrativo, $this->banco);
                    } else {
                        $this->Parcelamento_nao_previdenciario_demonstrativo_das_parcelas_model->insert($demonstrativo, $this->banco);
                    }

                    // if ($demonstrativo->situacao != 'Paga'){
                    //     if (!isset($result->path_download_parcela) || empty($result->path_download_parcela)) {
                    //         try {
                    //             $caminho_download = $this->ecac_robo_library_procuracao_nao_previdenciario->gerar_parcela_nao_previdenciario($registro['processo'], $tributos_do_processo['tributo'], $demonstrativo['data_vencimento'], $demonstrativo['valor_ate_vencimento'], $demonstrativo['numero_parcela']);
                    //             echo "<br>Caminho: $caminho_download";
                    //             if ($caminho_download != "") {
                    //                 $this->Parcelamento_nao_previdenciario_demonstrativo_das_parcelas_model->update_path($this->banco, $demonstrativo['numero_parcela'], $demonstrativo['cnpj'], $demonstrativo['id_tributo'], $caminho_download);
                    //             }
                    //         } catch (Exception $e) {
                    //             echo $e->getMessage();
                    //         }
                    //     }
                    // }
                    
                }
            }
        }
        // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
        unset($this->ecac_robo_library_procuracao_nao_previdenciario);
        echo "<br>Busca Parcelamento Não Previdenciário concluído";
    }

    private function buscar_parcelamento_lei_12996($cnpj)
    {
        $folder_pdf = FCPATH . 'arquivos/parcelamento-lei-12996/' . $this->banco . '/';
        $this->params['caminho_da_pasta_pdfs'] = $folder_pdf;

        $this->load->model('Parcelamento_lei_12996_divida_consolidada_model');
        $this->load->model('Parcelamento_lei_12996_demonstrativo_prestacoes_model');

        $this->load->library('Ecac/SimplesNacional/Parcelamento_lei_12996', $this->params, 'ecac_robo_library_procuracao_lei_12996');

        $mes_atual = date('Ym');

        $registros = $this->ecac_robo_library_procuracao_lei_12996->extrato_e_demonstrativos($cnpj);
        print_r($registros);
        if (!empty($registros)) {
            foreach ($registros as $registro) {
                $registro['cnpj'] = $cnpj;

                // $existe_registro = $this->Parcelamento_lei_12996_divida_consolidada_model->verifica_se_existe($registro['cnpj'], $this->banco, $registro['cod_modalidade']);
                $existe_registro = $this->Parcelamento_lei_12996_divida_consolidada_model->verifica_se_existe($registro, $this->banco);

                if ($existe_registro->qtd > 0) {
                    $id_divida_consolidada = $existe_registro->id;
                    $this->Parcelamento_lei_12996_divida_consolidada_model->update($registro, $id_divida_consolidada, $this->banco);
                    // $this->Parcelamento_lei_12996_divida_consolidada_model->update($registro['cnpj'], $this->banco, $registro['data_adesao'],  $registro['nome_situacao']);
                } else {
                    $id_divida_consolidada = $this->Parcelamento_lei_12996_divida_consolidada_model->insert($registro, $this->banco);
                }

                foreach ($registro['parcelas'] as $parcela) {
                    if ($parcela['data_parcela'] != '00000000') {
                    $parcela['cnpj'] = $cnpj;
                    $parcela['id_divida_consolidada'] = $id_divida_consolidada;

                    $result = $this->Parcelamento_lei_12996_demonstrativo_prestacoes_model->verifica_se_existe($cnpj, $this->banco, $parcela['parcela_id'], $id_divida_consolidada);
                    if ($result->qtd > 0) {
                        $this->Parcelamento_lei_12996_demonstrativo_prestacoes_model->update($parcela, $this->banco);
                    } else {
                        $this->Parcelamento_lei_12996_demonstrativo_prestacoes_model->insert($parcela, $this->banco);
                    }

                    // if (substr($parcela['data_parcela'], 0, -2) == $mes_atual && (!isset($result->path_download_parcela) || empty($result->path_download_parcela))) {
                    //     try {
                    //         $caminho_download = $this->ecac_robo_library_procuracao_lei_12996->gerar_parcela_lei_12996($cnpj, $parcela['data_parcela'], $registro['cod_receita']);
                    //         echo "<br>$caminho_download";
                    //         if ($caminho_download != "") {
                    //             $this->Parcelamento_lei_12996_demonstrativo_prestacoes_model->update_path($caminho_download, $parcela, $this->banco);
                    //         }
                    //     } catch (Exception $e) {
                    //         echo $e->getMessage();
                    //     }
                    // }
                    }
                }
            }
        }
        // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
        unset($this->ecac_robo_library_procuracao_lei_12996);
        echo "<br>Busca Parcelamento Lei 12.996 concluído";
    }

    private function buscar_parcelamento_pert_rfb($cnpj)
    {
        $folder_pdf = FCPATH . 'arquivos/parcelamento-pert-rfb/' . $this->banco . '/';

        $this->params['caminho_da_pasta_pdfs'] = $folder_pdf;

        $this->load->model('Parcelamento_pert_rfb_model');
        $this->load->model('Parcelamento_pert_rfb_demonstrativo_de_parcelas_model');
        $this->load->model('Parcelamento_pert_rfb_demonstrativo_de_pagamentos_model');

        $this->load->library('Ecac/SimplesNacional/Parcelamento_pert_rfb', $this->params, 'ecac_robo_library_procuracao_pert_rfb');

        $mes_atual = date('m/Y');

        $parcelamentos = $this->ecac_robo_library_procuracao_pert_rfb->obter_parcelamentos($cnpj);

        if (!is_null($parcelamentos)) {
            foreach ($parcelamentos as $parcelamento) {
                if ($parcelamento->situacao == 'Ativo') {
                    $parcelamento->cnpj = $cnpj;

                    $existe_registro = $this->Parcelamento_pert_rfb_model->verifica_se_existe($parcelamento, $this->banco);

                    if ($existe_registro->qtd == 0) {
                        $id_parcelamento = $this->Parcelamento_pert_rfb_model->insert($parcelamento, $this->banco);
                    } else {
                        $id_parcelamento = $existe_registro->id;
                        $this->Parcelamento_pert_rfb_model->update($parcelamento, $this->banco);
                    }

                    foreach ($parcelamento->demonstrativo_de_pagamentos->pagamentosDtos as $pagamento) {
                        $pagamento->cnpj = $cnpj;
                        $pagamento->id_parcelamento = $id_parcelamento;
                        $pagamento->parcelamentoFoiReconsolidado = $parcelamento->demonstrativo_de_pagamentos->parcelamentoFoiReconsolidado;
                        $pagamento->simboloMoeda = $parcelamento->demonstrativo_de_pagamentos->simboloMoeda;

                        $existe_pagamento = $this->Parcelamento_pert_rfb_demonstrativo_de_pagamentos_model->verifica_se_existe($pagamento, $this->banco);

                        if ($existe_pagamento->qtd == 0) {
                            $this->Parcelamento_pert_rfb_demonstrativo_de_pagamentos_model->insert($pagamento, $this->banco);
                        } else {
                            $this->Parcelamento_pert_rfb_demonstrativo_de_pagamentos_model->update($pagamento, $this->banco);
                        }
                    }

                    foreach ($parcelamento->demonstrativo_de_parcelas[0]->parcelas as $parcela) {
                        $parcela->cnpj = $cnpj;
                        $parcela->id_parcelamento = $id_parcelamento;

                        $existe_parcela = $this->Parcelamento_pert_rfb_demonstrativo_de_parcelas_model->verifica_se_existe($parcela, $this->banco);

                        if ($existe_parcela->qtd == 0) {
                            $this->Parcelamento_pert_rfb_demonstrativo_de_parcelas_model->insert($parcela, $this->banco);
                        } else {
                            $this->Parcelamento_pert_rfb_demonstrativo_de_parcelas_model->update($parcela, $this->banco);
                        }

                        if (substr($parcela->dataVencimento, 3) == $mes_atual && (!isset($existe_parcela->path_download_parcela) || empty($existe_parcela->path_download_parcela))) {
                            try {
                                $caminho_download = $this->ecac_robo_library_procuracao_pert_rfb->gerar_parcela_pert_rfb($parcelamento->idFormatado, $parcela->id);
                                echo "<br>$caminho_download";
                                if ($caminho_download != "") {
                                    $this->Parcelamento_pert_rfb_demonstrativo_de_parcelas_model->update_path($parcela, $this->banco, $caminho_download);
                                }
                            } catch (Exception $e) {
                                echo $e->getMessage();
                            }
                        }
                    }
                }
            }

            unset($this->ecac_robo_library_procuracao_pert_rfb);
            echo "<br>Busca Parcelamento Pert RFB concluído";
        } else echo "<br>Não possui Parcelamento Pert RFB";
    }

    private function buscar_parcelamento_mei($cnpj)
    {
        $folder_pdf = FCPATH . 'arquivos/recibos-parcelamento-mei/' . $this->banco . '/';
        $this->params['caminho_da_pasta_pdfs'] = $folder_pdf;

        $this->load->model('Parcelamento_mei_consolidacao_original_model');
        $this->load->model('Parcelamento_mei_demonstrativo_pagamento_model');
        $this->load->model('Parcelamento_mei_pedido_contribuinte_model');

        $this->load->library('Ecac/SimplesNacional/Parcelamento_mei', $this->params, 'ecac_robo_library_procuracao_mei');


        $registros = $this->ecac_robo_library_procuracao_mei->obter_parcelamentos();

        foreach ($registros as $registro) {
            $registro['cnpj'] = $cnpj;

            $existe_pedido = $this->Parcelamento_mei_pedido_contribuinte_model->verifica_se_existe($registro['cnpj'], $this->banco, $registro['numero']);

            if ($existe_pedido->qtd > 0) {
                $id_parcelamento = $existe_pedido->id;
                $this->Parcelamento_mei_pedido_contribuinte_model->update($registro['cnpj'], $this->banco, $registro['numero'],  $registro['situacao']);
            } else {
                if ($registro['situacao'] != 'Em parcelamento')
                    continue;
                $id_parcelamento = $this->Parcelamento_mei_pedido_contribuinte_model->insert($registro, $this->banco);
            }

            $existe_debitos_parcelas = $this->Parcelamento_mei_consolidacao_original_model->verifica_se_existe($registro['cnpj'], $this->banco, $id_parcelamento);

            if ($existe_debitos_parcelas->qtd <= 0) {
                foreach ($registro['consolidacao_original'] as $consolidacao) {
                    $consolidacao['cnpj'] = $cnpj;
                    $consolidacao['id_parcelamento'] = $id_parcelamento;
                    $this->Parcelamento_mei_consolidacao_original_model->insert($consolidacao, $this->banco);
                }
            }


            foreach ($registro['demonstrativo_pagamento'] as $demonstrativo) {
                $demonstrativo['cnpj'] = $cnpj;
                $demonstrativo['id_parcelamento'] = $id_parcelamento;

                $existe_pagamento = $this->Parcelamento_mei_demonstrativo_pagamento_model->verifica_se_existe($demonstrativo['cnpj'], $this->banco, $id_parcelamento, $demonstrativo['mes_parcela']);
                if ($existe_pagamento->qtd <= 0) {
                    $this->Parcelamento_mei_demonstrativo_pagamento_model->insert($demonstrativo, $this->banco);
                }
            }
        }
        unset($this->ecac_robo_situacao_fiscal_ecac_mei);
        echo "<br>Busca Parcelamento MEI concluído";
    }

    private function buscar_parcelas_mei($cnpj)
    {
        $folder_pdf = FCPATH . 'arquivos/recibos-parcelamento-mei/' . $this->banco . '/';
        $this->params['caminho_da_pasta_pdfs'] = $folder_pdf;
        
        $this->load->model('Parcelas_mei_model');

        $this->load->library('Ecac/SimplesNacional/Parcelamento_mei', $this->params, 'ecac_robo_library_procuracao_mei');

        $parcelas = $this->ecac_robo_library_procuracao_mei->obter_parcelas_mei();
        if ($parcelas) {
            $mes_atual = date('m/Y');
            foreach ($parcelas as $parcela) {
                //0 para parcela não paga e 1 para parcela paga e -1 para parcela atrasada
                $parcela['pago'] = $this->Parcelas_mei_model->verifica_se_pago($this->banco, $cnpj, $parcela['data_parcela'])->qtd;
                if ($parcela['pago'] == 0 && $parcela['data_parcela'] < $mes_atual) {
                    $parcela['pago'] = -1;
                }

                $result = $this->Parcelas_mei_model->verifica_se_existe($this->banco, $cnpj, $parcela['data_parcela']);
                if ($result->qtd > 0) {
                    $this->Parcelas_mei_model->update($cnpj, $this->banco, $parcela);
                } else {
                    $this->Parcelas_mei_model->insert($cnpj, $this->banco, $parcela);
                }

                //Gerar parcela
                // if (!isset($result->path_download_parcela) || empty($result->path_download_parcela)) {
                //     try {
                //         $caminho_download = $this->ecac_robo_library_procuracao_mei->gerar_parcela_mei($parcela['data_parcela']);
                //         echo "<br>$caminho_download";
                //         if ($caminho_download != "") {
                //             $this->Parcelas_mei_model->update_path($this->banco, $parcela['data_parcela'], $cnpj, $caminho_download);
                //         }
                //     } catch (Exception $e) {
                //         echo $e->getMessage();
                //     }
                // }
            }
        }
        unset($this->ecac_robo_situacao_fiscal_ecac_mei);
        echo "<br>Busca Parcelamento MEI concluído";
    }

    private function buscar_dctf_web_teste($cnpj)
    {
        $folder_pdf = FCPATH . 'arquivos/dctf-web/' . $this->banco . '/';

        if (!file_exists($folder_pdf)) {
            mkdir($folder_pdf, DIR_WRITE_MODE, true);
        }

        $this->params['caminho_da_pasta_pdfs'] = $folder_pdf;

        $this->load->library('Ecac/Dctf_web', $this->params, 'ecac_robo_dctf_web');

        $this->load->model('Dctf_web_model');
        $this->load->model('Dctf_web_detalhes_model');

        $registros = $this->ecac_robo_dctf_web->buscar_declaracoes();
        
        if($registros){
           foreach ($registros as $registro) {
                $registro['cnpj'] = $cnpj;
                
                $existe_registro = $this->Dctf_web_model->verifica_se_existe($registro, $this->banco);
                
                if ($existe_registro->qtd > 0) {
                    $id = $existe_registro->id;
                    $this->Dctf_web_model->update($registro, $id, $this->banco);
                } else {
                    $id = $this->Dctf_web_model->insert($registro, $this->banco);
                }

                $this->Dctf_web_detalhes_model->clear($id, $this->banco);
                foreach ($registro['detalhes'] as $detalhe) {
                    $this->Dctf_web_detalhes_model->insert($detalhe, $id, $this->banco);
                }

                if (!isset($existe_registro->path_download_darf) || empty($existe_registro->path_download_darf)) {
                    try {
                        $caminho_download = $this->ecac_robo_dctf_web->gerar_darf($registro['id_declaracao'], $registro['id_controle']);
                        echo "<br>$caminho_download";
                        if ($caminho_download != "") {
                            $this->Dctf_web_model->update_path_darf($id, $caminho_download, $this->banco);
                        }
                    } catch (Exception $e) {
                        echo $e->getMessage();
                    }
                }

                if (!isset($existe_registro->path_download_recibo) || empty($existe_registro->path_download_recibo)) {
                    try {
                        $caminho_download = $this->ecac_robo_dctf_web->gerar_recibo($registro['id_declaracao'], $registro['id_controle']);
                        echo "<br>$caminho_download";
                        if ($caminho_download != "") {
                            $this->Dctf_web_model->update_path_recibo($id, $caminho_download, $this->banco);
                        }
                    } catch (Exception $e) {
                        echo $e->getMessage();
                    }
                }

                if (!isset($existe_registro->path_download_extrato) || empty($existe_registro->path_download_extrato)) {
                    try {
                        $caminho_download = $this->ecac_robo_dctf_web->gerar_extrato($registro['id_declaracao'], $registro['id_controle']);
                        echo "<br>$caminho_download";
                        if ($caminho_download != "") {
                            $this->Dctf_web_model->update_path_extrato($id, $caminho_download, $this->banco);
                        }
                    } catch (Exception $e) {
                        echo $e->getMessage();
                    }
                }
            } 
        }
        
        // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
        unset($this->ecac_robo_dctf_web);
        echo "<br>Busca DCTF Web concluído";
    }

    
    // ***********************************************************************************************
    // ***********************************************************************************************
    // ***********************************************************************************************
    // ***********************************************************************************************
    // ***********************************************************************************************
    // ***********************************************************************************************
    // ***********************************************************************************************
    // ***********************************************************************************************
    // ***********************************************************************************************
    // ***********************************************************************************************
    // ***********************************************************************************************
    // ***********************************************************************************************
    // ***********************************************************************************************
    // ***********************************************************************************************
    // ***********************************************************************************************
    // DIAGNOSTICO

    function buscar_extra_01()
    {
        include('PdfToText/PdfToText.phpclass');
        
        ob_start(); 

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_17($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);

                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
        ob_end_clean();
        $this->buscar_extra_02($this->banco);
    }

    function buscar_extra_02($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_18($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);

                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_03($this->banco);

    }

    function buscar_extra_03($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_19($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);

                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_04($this->banco);

    }

    function buscar_extra_04($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_20($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);

                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_05($this->banco);

    }

    function buscar_extra_05($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_21($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);

                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_06($this->banco);

    }

    function buscar_extra_06($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_22($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);

                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_07($this->banco);

    }

    function buscar_extra_07($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_23($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);

                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_08($this->banco);

    }

    function buscar_extra_08($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_24($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);

                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_09($this->banco);

    }

    function buscar_extra_09($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_25($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);

                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_10($this->banco);

    }

    function buscar_extra_10($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_26($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);

                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_11($this->banco);

    }

    function buscar_extra_11($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_27($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);

                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_12($this->banco);

    }

    function buscar_extra_12($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_28($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);

                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_13($this->banco);

    }

    function buscar_extra_13($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_29($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);

                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_14($this->banco);

    }

    function buscar_extra_14($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_30($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);

                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_15($this->banco);

    }

    function buscar_extra_15($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_31($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);

                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_16($this->banco);

    }

    function buscar_extra_16($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_32($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);

                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_17($this->banco);

    }

    function buscar_extra_17($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_33($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);

                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_18($this->banco);

    }

    function buscar_extra_18($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_34($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);

                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_19($this->banco);

    }

    function buscar_extra_19($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_35($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);

                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_20($this->banco);

    }

    function buscar_extra_20($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_36($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);

                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_21($this->banco);

    }

    function buscar_extra_21($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_37($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);

                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_22($this->banco);

    }

    function buscar_extra_22($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_38($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);

                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_23($this->banco);

    }

    function buscar_extra_23($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_39($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_das_ecac_procuracao($item->cnpj);
                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);
                $this->buscar_situacao_fiscal_ecac($item->cnpj);
                $this->buscar_situacao_cadin($item->cnpj);
                // $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                // $this->buscar_simplesnacional_parcelas($item->cnpj);
                // $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                // $this->buscar_pert_parcelas($item->cnpj);

                // $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                // $this->buscar_parcelamento_lei_12996($item->cnpj);
                // $this->buscar_parcelamento_pert_rfb($item->cnpj);
                // $this->buscar_parcelamento_mei($item->cnpj);
                // $this->buscar_parcelas_mei($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_24($this->banco);

    } 

    // ***********************************************************************************************
    // ***********************************************************************************************
    // ***********************************************************************************************
    // ***********************************************************************************************
    // ***********************************************************************************************
    // ***********************************************************************************************
    // ***********************************************************************************************
    // ***********************************************************************************************
    // ***********************************************************************************************
    // ***********************************************************************************************
    // ***********************************************************************************************
    // ***********************************************************************************************
    // ***********************************************************************************************
    // ***********************************************************************************************
    // ***********************************************************************************************
    // PARCELAMENTO

    function buscar_extra_24($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_17($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_25($this->banco);

    }

    function buscar_extra_25($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_18($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_26($this->banco);

    }

    function buscar_extra_26($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_19($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_27($this->banco);

    }

    function buscar_extra_27($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_20($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_28($this->banco);

    }

    function buscar_extra_28($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_21($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_29($this->banco);

    }

    function buscar_extra_29($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_22($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_30($this->banco);

    }

    function buscar_extra_30($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_23($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_31($this->banco);

    }

    function buscar_extra_31($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_24($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_32($this->banco);

    }

    function buscar_extra_32($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_25($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_33($this->banco);

    }

    function buscar_extra_33($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_26($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_34($this->banco);

    }

    function buscar_extra_34($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_27($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_35($this->banco);

    }

    function buscar_extra_35($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_28($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_36($this->banco);

    }

    function buscar_extra_36($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_29($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_37($this->banco);

    }

    function buscar_extra_37($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_30($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_38($this->banco);

    }

    function buscar_extra_38($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_31($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_39($this->banco);

    }

    function buscar_extra_39($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_32($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_40($this->banco);

    }

    function buscar_extra_40($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_33($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_41($this->banco);

    }

    function buscar_extra_41($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_34($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_42($this->banco);

    }

    function buscar_extra_42($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_35($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_43($this->banco);

    }

    function buscar_extra_43($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_36($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_44($this->banco);

    }

    function buscar_extra_44($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_37($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_45($this->banco);

    }

    function buscar_extra_45($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_38($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_46($this->banco);

    }

    function buscar_extra_46($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_39($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                // $this->buscar_mensagens_ecac($item->cnpj);
                // $this->buscar_das_ecac_procuracao($item->cnpj);
                // $this->buscar_eprocessos_procuracao($item->cnpj);
                // $this->buscar_dctf_ecac_procuracao($item->cnpj);
                // $this->buscar_situacao_fiscal_ecac($item->cnpj);
                // $this->buscar_situacao_cadin($item->cnpj);
                $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
                $this->buscar_simplesnacional_parcelas($item->cnpj);
                $this->buscar_pert_pedidos_parcelamento($item->cnpj);
                $this->buscar_pert_parcelas($item->cnpj);
                $this->buscar_parcelamento_nao_previdenciario($item->cnpj);
                $this->buscar_parcelamento_lei_12996($item->cnpj);
                $this->buscar_parcelamento_pert_rfb($item->cnpj);
                $this->buscar_parcelamento_mei($item->cnpj);
                $this->buscar_parcelas_mei($item->cnpj);
                $this->buscar_relp_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_parcelas($item->cnpj);
                $this->buscar_relp_mei_pedidos_parcelamento($item->cnpj);
                $this->buscar_relp_mei_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_47($this->banco);

    } 

    // ***********************************************************************************************
    // ***********************************************************************************************
    // ***********************************************************************************************
    // ***********************************************************************************************
    // ***********************************************************************************************
    // ***********************************************************************************************
    // ***********************************************************************************************
    // ***********************************************************************************************
    // ***********************************************************************************************
    // ***********************************************************************************************
    // ***********************************************************************************************
    // ***********************************************************************************************
    // ***********************************************************************************************
    // ***********************************************************************************************
    // ***********************************************************************************************
    // MENSAGENS

    function buscar_extra_47($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_17($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_darfs($item->cnpj);
                $this->buscar_dctf_web($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_48($this->banco);

    }

    function buscar_extra_48($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_18($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_darfs($item->cnpj);
                $this->buscar_dctf_web($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_49($this->banco);

    }

    function buscar_extra_49($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_19($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_darfs($item->cnpj);
                $this->buscar_dctf_web($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_50($this->banco);

    }

    function buscar_extra_50($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_20($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_darfs($item->cnpj);
                $this->buscar_dctf_web($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_51($this->banco);

    }

    function buscar_extra_51($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_21($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_darfs($item->cnpj);
                $this->buscar_dctf_web($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_52($this->banco);

    }

    function buscar_extra_52($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_22($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_darfs($item->cnpj);
                $this->buscar_dctf_web($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_53($this->banco);

    }

    function buscar_extra_53($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_23($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_darfs($item->cnpj);
                $this->buscar_dctf_web($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_54($this->banco);

    }

    function buscar_extra_54($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_24($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_darfs($item->cnpj);
                $this->buscar_dctf_web($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_55($this->banco);

    }

    function buscar_extra_55($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_25($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_darfs($item->cnpj);
                $this->buscar_dctf_web($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_56($this->banco);

    }

    function buscar_extra_56($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_26($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_darfs($item->cnpj);
                $this->buscar_dctf_web($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_57($this->banco);

    }

    function buscar_extra_57($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_27($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_darfs($item->cnpj);
                $this->buscar_dctf_web($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_58($this->banco);

    }

    function buscar_extra_58($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_28($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_darfs($item->cnpj);
                $this->buscar_dctf_web($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_59($this->banco);

    }

    function buscar_extra_59($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_29($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_darfs($item->cnpj);
                $this->buscar_dctf_web($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_60($this->banco);

    }

    function buscar_extra_60($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_30($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_darfs($item->cnpj);
                $this->buscar_dctf_web($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_61($this->banco);

    }

    function buscar_extra_61($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_31($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_darfs($item->cnpj);
                $this->buscar_dctf_web($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_62($this->banco);

    }

    function buscar_extra_62($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_32($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_darfs($item->cnpj);
                $this->buscar_dctf_web($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_63($this->banco);

    }

    function buscar_extra_63($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_33($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_darfs($item->cnpj);
                $this->buscar_dctf_web($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_64($this->banco);

    }

    function buscar_extra_64($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_34($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_darfs($item->cnpj);
                $this->buscar_dctf_web($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_65($this->banco);

    }

    function buscar_extra_65($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_35($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_darfs($item->cnpj);
                $this->buscar_dctf_web($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_66($this->banco);

    }

    function buscar_extra_66($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_36($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_darfs($item->cnpj);
                $this->buscar_dctf_web($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_67($this->banco);

    }

    function buscar_extra_67($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_37($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_darfs($item->cnpj);
                $this->buscar_dctf_web($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_68($this->banco);

    }

    function buscar_extra_68($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_38($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_darfs($item->cnpj);
                $this->buscar_dctf_web($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
ob_end_clean();
        $this->buscar_extra_69($this->banco);

    }

    function buscar_extra_69($banco)
    {
        ob_start();

        $this->banco = $banco;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('Contadorprocuracao_especial_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_39($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);
                $this->buscar_darfs($item->cnpj);
                $this->buscar_dctf_web($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }

        echo "###########################################";
        echo "###########################################";
        echo "###########################################";
        echo "<br>";
        echo "<br>";
        echo "FINALIZADO";
        echo "<br>";
        echo "<br>";
        echo "###########################################";
        echo "###########################################";
        echo "###########################################";
    } 
 

    private function buscar_dctf_web($cnpj)
    {
        $folder_pdf = FCPATH . 'arquivos/dctf-web/' . $this->banco . '/';

        if (!file_exists($folder_pdf)) {
            mkdir($folder_pdf, DIR_WRITE_MODE, true);
        }

        $this->params['caminho_da_pasta_pdfs'] = $folder_pdf;

        $this->load->library('Ecac/Dctf_web', $this->params, 'ecac_robo_dctf_web');

        $this->load->model('Dctf_web_model');
        $this->load->model('Dctf_web_detalhes_model');

        $registros = $this->ecac_robo_dctf_web->buscar_declaracoes();
        
        if($registros){
           foreach ($registros as $registro) {
                $registro['cnpj'] = $cnpj;
                
                $existe_registro = $this->Dctf_web_model->verifica_se_existe($registro, $this->banco);
                
                if ($existe_registro->qtd > 0) {
                    $id = $existe_registro->id;
                    $this->Dctf_web_model->update($registro, $id, $this->banco);
                } else {
                    $id = $this->Dctf_web_model->insert($registro, $this->banco);
                }

                $this->Dctf_web_detalhes_model->clear($id, $this->banco);
                if($registro['detalhes']){
                    foreach ($registro['detalhes'] as $detalhe) {
                        $this->Dctf_web_detalhes_model->insert($detalhe, $id, $this->banco);
                    }
                }
                

                // if (!isset($existe_registro->path_download_darf) || empty($existe_registro->path_download_darf)) {
                //     try {
                //         $caminho_download = $this->ecac_robo_dctf_web->gerar_darf($registro['id_declaracao'], $registro['id_controle']);
                //         echo "<br>$caminho_download";
                //         if ($caminho_download != "") {
                //             $this->Dctf_web_model->update_path_darf($id, $caminho_download, $this->banco);
                //         }
                //     } catch (Exception $e) {
                //         echo $e->getMessage();
                //     }
                // }

                // if (!isset($existe_registro->path_download_recibo) || empty($existe_registro->path_download_recibo)) {
                //     try {
                //         $caminho_download = $this->ecac_robo_dctf_web->gerar_recibo($registro['id_declaracao'], $registro['id_controle']);
                //         echo "<br>$caminho_download";
                //         if ($caminho_download != "") {
                //             $this->Dctf_web_model->update_path_recibo($id, $caminho_download, $this->banco);
                //         }
                //     } catch (Exception $e) {
                //         echo $e->getMessage();
                //     }
                // }

                // if (!isset($existe_registro->path_download_extrato) || empty($existe_registro->path_download_extrato)) {
                //     try {
                //         $caminho_download = $this->ecac_robo_dctf_web->gerar_extrato($registro['id_declaracao'], $registro['id_controle']);
                //         echo "<br>$caminho_download";
                //         if ($caminho_download != "") {
                //             $this->Dctf_web_model->update_path_extrato($id, $caminho_download, $this->banco);
                //         }
                //     } catch (Exception $e) {
                //         echo $e->getMessage();
                //     }
                // }
            } 
        }
        
        // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
        unset($this->ecac_robo_dctf_web);
        echo "<br>Busca DCTF Web concluído";
    }

    //PARCELAMENTO RELP
    private function buscar_relp_pedidos_parcelamento($cnpj)
    {
        $this->load->model('Relp_debitos_parcelas_model');
        $this->load->model('Relp_demonstrativo_pagamentos_model');
        $this->load->model('Relp_pedidos_parcelamentos_model');

        $folder_pdf = FCPATH . 'arquivos/recibos-parcelamento-mei/recibos-parcelamento-relp/' . $this->banco . '/';

        if (!file_exists($folder_pdf)) {
            mkdir($folder_pdf, DIR_WRITE_MODE, true);
        }

        $this->load->library('Ecac/SimplesNacional/Parcelamento_relp', $this->params, 'ecac_robo_library_procuracao_relp');


        $registros = $this->ecac_robo_library_procuracao_relp->obter_parcelamento();

        foreach ($registros as $registro) {

            if($registro['situacao'] == "Em parcelamento"){
                $registro['cnpj'] = $cnpj;

                $existe_pedido = $this->Relp_pedidos_parcelamentos_model->verifica_se_existe($registro['cnpj'], $this->banco, $registro['numero']);

                if ($existe_pedido->qtd > 0) {
                    $id_parcelamento = $existe_pedido->id;
                    $this->Relp_pedidos_parcelamentos_model->update($registro['cnpj'], $this->banco, $registro['numero'],  $registro['situacao']);
                } else {
                    if ($registro['situacao'] != 'Em parcelamento')
                        continue;
                    $id_parcelamento = $this->Relp_pedidos_parcelamentos_model->insert($registro, $this->banco);
                }

                $existe_debitos_parcelas = $this->Relp_debitos_parcelas_model->verifica_se_existe($registro['cnpj'], $this->banco, $id_parcelamento);

                if ($existe_debitos_parcelas->qtd <= 0) {
                    foreach ($registro['relacao_debitos_parcelas'] as $rdp) {
                        $rdp['cnpj'] = $cnpj;
                        $rdp['id_parcelamento'] = $id_parcelamento;
                        $this->Relp_debitos_parcelas_model->insert($rdp, $this->banco);
                    }
                }


                foreach ($registro['demonstrativo_pagamentos'] as $dp) {
                    $dp['cnpj'] = $cnpj;
                    $dp['id_parcelamento'] = $id_parcelamento;

                    $existe_pagamento = $this->Relp_demonstrativo_pagamentos_model->verifica_se_existe($dp['cnpj'], $this->banco, $id_parcelamento, $dp['mes_parcela']);
                    if ($existe_pagamento->qtd <= 0) {
                        $this->Relp_demonstrativo_pagamentos_model->insert($dp, $this->banco);
                    }
                }
            }else{
                $registro['cnpj'] = $cnpj;
                $this->Relp_pedidos_parcelamentos_model->update($registro['cnpj'], $this->banco, $registro['numero'],  $registro['situacao']);
            }
            
        }
        //Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
        unset($this->ecac_robo_library_procuracao_relp);
        echo "<br>Busca Relp pedidos parcelamento concluído";
    }

    private function buscar_relp_parcelas($cnpj)
    {
        $this->load->model('Relp_emissao_parcela_model');

        $this->load->library('Ecac/SimplesNacional/Parcelamento_relp', $this->params, 'ecac_robo_library_procuracao_relp_parcela');

        $parcelas = $this->ecac_robo_library_procuracao_relp_parcela->obter_simples_nacional_emissao_parcela();

        if ($parcelas) {
            foreach ($parcelas as $parcela) {

                $result = $this->Relp_emissao_parcela_model->verifica_se_existe($this->banco, $cnpj, $parcela['data_parcela']);
                if ($result->qtd > 0) {
                    $this->Relp_emissao_parcela_model->update($cnpj, $this->banco, $parcela);
                } else {
                    $this->Relp_emissao_parcela_model->insert($cnpj, $this->banco, $parcela);
                }

                // if (!isset($result->path_download_parcela) || empty($result->path_download_parcela)) {
                //     try {
                //         $this->baixar_pdf_simplesnacional($this->banco, $cnpj, trim($parcela['data_parcela']));
                //     } catch (Exception $e) {
                //         echo $e->getMessage();
                //     }
                // }
            }
        }
        // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
        unset($this->ecac_robo_library_procuracao_relp_parcela);
        echo "<br>Busca parcela Relp concluído";
    }



    //PARCELAMENTO RELP MEI
    private function buscar_relp_mei_pedidos_parcelamento($cnpj)
    {
        $this->load->model('Relp_mei_debitos_parcelas_model');
        $this->load->model('Relp_mei_demonstrativo_pagamentos_model');
        $this->load->model('Relp_mei_pedidos_parcelamentos_model');

        $folder_pdf = FCPATH . 'arquivos/recibos-parcelamento-mei/recibos-parcelamento-relp-mei/' . $this->banco . '/';

        if (!file_exists($folder_pdf)) {
            mkdir($folder_pdf, DIR_WRITE_MODE, true);
        }

        $this->load->library('Ecac/SimplesNacional/Parcelamento_relp_mei', $this->params, 'ecac_robo_library_procuracao_relp');


        $registros = $this->ecac_robo_library_procuracao_relp->obter_parcelamento();

        foreach ($registros as $registro) {

            if($registro['situacao'] == "Em parcelamento"){
                $registro['cnpj'] = $cnpj;

                $existe_pedido = $this->Relp_mei_pedidos_parcelamentos_model->verifica_se_existe($registro['cnpj'], $this->banco, $registro['numero']);

                if ($existe_pedido->qtd > 0) {
                    $id_parcelamento = $existe_pedido->id;
                    $this->Relp_mei_pedidos_parcelamentos_model->update($registro['cnpj'], $this->banco, $registro['numero'],  $registro['situacao']);
                } else {
                    if ($registro['situacao'] != 'Em parcelamento')
                        continue;
                    $id_parcelamento = $this->Relp_mei_pedidos_parcelamentos_model->insert($registro, $this->banco);
                }

                $existe_debitos_parcelas = $this->Relp_mei_debitos_parcelas_model->verifica_se_existe($registro['cnpj'], $this->banco, $id_parcelamento);

                if ($existe_debitos_parcelas->qtd <= 0) {
                    foreach ($registro['relacao_debitos_parcelas'] as $rdp) {
                        $rdp['cnpj'] = $cnpj;
                        $rdp['id_parcelamento'] = $id_parcelamento;
                        $this->Relp_mei_debitos_parcelas_model->insert($rdp, $this->banco);
                    }
                }


                foreach ($registro['demonstrativo_pagamentos'] as $dp) {
                    $dp['cnpj'] = $cnpj;
                    $dp['id_parcelamento'] = $id_parcelamento;

                    $existe_pagamento = $this->Relp_mei_demonstrativo_pagamentos_model->verifica_se_existe($dp['cnpj'], $this->banco, $id_parcelamento, $dp['mes_parcela']);
                    if ($existe_pagamento->qtd <= 0) {
                        $this->Relp_mei_demonstrativo_pagamentos_model->insert($dp, $this->banco);
                    }
                }
            }else{
                $registro['cnpj'] = $cnpj;
                $this->Relp_mei_pedidos_parcelamentos_model->update($registro['cnpj'], $this->banco, $registro['numero'],  $registro['situacao']);
            }
            
        }
        //Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
        unset($this->ecac_robo_library_procuracao_relp);
        echo "<br>Busca Relp MEI pedidos parcelamento concluído";
    }


    private function buscar_relp_mei_parcelas($cnpj)
    {
        $this->load->model('Relp_mei_emissao_parcela_model');

        $this->load->library('Ecac/SimplesNacional/Parcelamento_relp_mei', $this->params, 'ecac_robo_library_procuracao_relp_parcela');

        $parcelas = $this->ecac_robo_library_procuracao_relp_parcela->obter_simples_nacional_emissao_parcela();

        if ($parcelas) {
            foreach ($parcelas as $parcela) {

                $result = $this->Relp_mei_emissao_parcela_model->verifica_se_existe($this->banco, $cnpj, $parcela['data_parcela']);
                if ($result->qtd > 0) {
                    $this->Relp_mei_emissao_parcela_model->update($cnpj, $this->banco, $parcela);
                } else {
                    $this->Relp_mei_emissao_parcela_model->insert($cnpj, $this->banco, $parcela);
                }

                // if (!isset($result->path_download_parcela) || empty($result->path_download_parcela)) {
                //     try {
                //         $this->baixar_pdf_simplesnacional($this->banco, $cnpj, trim($parcela['data_parcela']));
                //     } catch (Exception $e) {
                //         echo $e->getMessage();
                //     }
                // }
            }
        }
        // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
        unset($this->ecac_robo_library_procuracao_relp_parcela);
        echo "<br>Busca parcela Relp concluído";
    }

    private function buscar_darfs($cnpj)
    {   
        $folder_pdf = FCPATH . 'arquivos/recibos-parcelamento-mei/darf/' . $this->banco . '/';

        if (!file_exists($folder_pdf)) {
            mkdir($folder_pdf, DIR_WRITE_MODE, true);
        }

        $this->params['caminho_da_pasta_pdfs'] = $folder_pdf;

        $this->load->model('Darf_model');

        $this->load->library('Ecac/Darf', $this->params, 'darf');

        $mapa_darfs = array();
        $darfs_existentes = $this->Darf_model->find_all_darfs($this->banco);
        foreach ($darfs_existentes as $d) {
            $mapa_darfs[$d->numero_documento] = $d;
        }

        $darfs = $this->darf->busca_darfs($mapa_darfs, $cnpj);

        if ($darfs) {
            foreach ($darfs as $d) {
                $this->Darf_model->insert($d, $this->banco);
            }
        }
        // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
        unset($this->darf);
        echo "<br>Busca DARF concluído";
    }
}



