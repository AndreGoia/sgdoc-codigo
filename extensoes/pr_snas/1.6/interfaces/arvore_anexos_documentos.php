<?php
/*
 * Copyright 2008 ICMBio
 * Este arquivo é parte do programa SISICMBio
 * O SISICMBio é um software livre; você pode redistribuíção e/ou modifição dentro dos termos
 * da Licença Pública Geral GNU como publicada pela Fundação do Software Livre (FSF); na versão
 * 2 da Licença.
 *
 * Este programa é distribuíção na esperança que possa ser útil, mas SEM NENHUMA GARANTIA; sem
 * uma garantia implícita de ADEQUAÇÃO a qualquer MERCADO ou APLICAÇÃO EM PARTICULAR. Veja a
 * Licença Pública Geral GNU/GPL em português para maiores detalhes.
 * Você deve ter recebido uma cópia da Licença Pública Geral GNU, sob o título "LICENCA.txt",
 * junto com este programa, se não, acesse o Portal do Software Público Brasileiro no endereço
 * www.softwarepublico.gov.br ou escreva para a Fundação do Software Livre(FSF)
 * Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301, USA
 * */

/**
 * Manter o fundo branco...
 */
$DONT_RENDER_BACKGROUND = TRUE;
include(__BASE_PATH__ . '/extensoes/pr_snas/1.6/classes/Arvore.php');
include_once("function/auto_load_statics.php");
include("interfaces/detalhar_documentos.php");


$urlManager = __APPMODELS__ . "arvores/listar_anexos_documentos.php";
$digital = $_GET['digital'];
$arvore = new Arvore();


$vinculacao = new Vinculacao();
$root = $vinculacao->setDocumentoRoot($digital, 1/* Anexos */);
$arvore->setRootId($root);
$elementos = $arvore->getVinculacaoDocumento($root, $urlManager, 1/* Anexos */);
?>


<html>
    <head>
        <title>Anexos/Apensos</title>
        <style type="text/css">
            body{
                margin: 0px;
                padding: 0px;
            }
            .ausente{
                color: red;
            }

        </style>
        <link rel="stylesheet" type="text/css" href="plugins/tree/css/style.css" />
        <script type="text/javascript" src="plugins/tree/js/jquery.simple.tree.js"></script>
        <script type="text/javascript" src="plugins/tree/js/langManager.js"></script>
        <script type="text/javascript" src="plugins/tree/js/treeOperations.js"></script>
        <script type="text/javascript">

            /*Inicializando controladoar de idiomas*/
            var langManager = new languageManager();

            /*Objeto Operation Tree*/
            var treeOperations = null;

            $(document).ready(function() {
                treeOperations = new TreeOperations($('#tree-anexos-documentos'), '<?php print($urlManager); ?>');

                /*Arvore */
                $('#tree-anexos-documentos').simpleTree({
                    animate: true,
                    autoclose: false,
                    restoreTreeState: false,
                    afterClick: function(node) {
                        jquery_detalhar_documento($('span:first', node).text());
                    },
                    afterDblClick: function(node) {
                        //jquery_detalhar_documento($('span:first',node).text());
                    },
                    afterMove: function(destination, source, pos) {
                    },
                    afterAjax: function(node) {

                        /*Documento Selecionado*/
                        if (node.attr('idElemento') == '<?php print($digital); ?>') {
                            $('#' + node.attr('idElemento')).attr('class', 'folder-open-target');
                            $('#' + node.attr('idElemento')).attr('title', 'Documento selecionado');
                            if (node.html().length == 1) {
                                $('#' + node.attr('idElemento')).attr('class', 'folder-close-target');
                            }
                        }

                        /*Documento ausente*/
                        if (node.attr('stAusente') == 'true') {
                            $('#' + node.attr('idElemento')).attr('class', 'folder-open-ausente');
                            $('#' + node.attr('idElemento')).attr('title', 'Este documento nao esta na sua área de trabalho');
                            if (node.html().length == 1) {
                                $('#' + node.attr('idElemento')).attr('class', 'folder-close-ausente');
                            }
                        }
                    },
                    afterContextMenu: function(element, event) {
                        var className = element.attr('class');
                        if (className.indexOf('root') >= 0) {
                            $('#menu-tree-anexos-documentos .edit, #menu-tree-anexos-documentos .delete').hide();
                            $('#menu-tree-anexos-documentos .expandAll, #menu-tree-anexos-documentos .collapseAll').show();
                        }
                        else {
                            $('#menu-tree-anexos-documentos .expandAll, #menu-tree-anexos-documentos .collapseAll').hide();
                        }
                        $('#menu-tree-anexos-documentos').css('top', event.pageY).css('left', event.pageX).show();

                        $('*').click(function() {
                            $('#menu-tree-anexos-documentos').hide();
                        });
                    }

                });

                /*Carregar todos os vinculos*/
                treeOperations.expandAll($('#tree-anexos-documentos > .root > ul'));

                /*Menus*/
                $('#menu-tree-anexos-documentos .expandAll').click(function() {
                    treeOperations.expandAll($('#tree-anexos-documentos > .root > ul'));
                });
                $('#menu-tree-anexos-documentos .collapseAll').click(function() {
                    treeOperations.collapseAll();
                });

                $('#menu-tree-anexos-documentos .expandAll').append(langManager.expandAll);
                $('#menu-tree-anexos-documentos .collapseAll').append(langManager.collapseAll);

            });
        </script>
    </head>
    <body>
        <div class="contextMenu" id="menu-tree-anexos-documentos">
            <li class="expandAll"><img alt="" src="plugins/tree/css/images/expand.png"/></li>
            <li class="collapseAll"><img alt="" src="plugins/tree/css/images/collapse.png"/></li>
        </div>
		<ul id="tree-anexos-documentos" class="arvoreDocumentos">
			<?php
				$documento = current(CFModelDocumento::factory()->findByParam(array('DIGITAL' => $root)));
				$classe = Documento::validarDocumentoAreaDeTrabalho($root) ? 'root' : 'root root-ausente';
				$rootId = $arvore->getRootId();
			?>
            <li class='<?php print($classe) ?>' id='<?php print($rootId); ?>'>
            	<span title="Documento mais relevante da arvore"><?php print($root); ?></span>
            	[ <?php print($documento->ASSUNTO); ?> - <?php print(Util::formatDate($documento->DT_DOCUMENTO) ? Util::formatDate($documento->DT_DOCUMENTO) : "Data Não informada"); ?> ]
                <ul><?php print($elementos); ?></ul>
            </li>
        </ul>
    </body>


</html>

<style type="text/css">

    .ui-progressbar-value { 
        background-image: url('imagens/jqueryui_progressbar.gif'); 
    }
    #progressbar-default{
        width: 300px;
        height: 22px; 
    }
    #progressbar #progressbar-default-container{
        position: absolute;
        margin: 20px;
        right: 0px;
        bottom: 0px;
    }
    #progressbar{
        display: none;
        position: fixed;
        top: 0px;
        bottom: 0px;
        right: 0px;
        left: 0px;   
        z-index: 999999;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.9);
        border: 0px solid white;
    }

</style>

<div id="visualizador_popup" style="display:none">
    <input type="hidden" id="">
    <input class="exitButtonPopup" id="btnClosePopup" type="image" src="imagens/cancelar.png" title="Fechar" onclick="stop_visualizador_processo();">
    <iframe name="frame_visualizador_popup" id="frame_visualizador_popup"></iframe>
</div>