<?php $this->load->view('administrador/dashboard/header'); ?>
<!-- BEGIN PAGE -->
<div id="container" class="row-fluid">
    <!-- BEGIN SIDEBAR -->
    <?php $this->load->view('administrador/dashboard/sidebar'); ?>
    <!-- END SIDEBAR -->
    <!-- BEGIN PAGE -->  
    <div id="main-content">
        <!-- BEGIN PAGE CONTAINER-->
        <div class="container-fluid">
            <!-- BEGIN PAGE HEADER-->   
            <div class="row-fluid">
                <div class="span12">
                    <!-- BEGIN PAGE TITLE & BREADCRUMB-->
                    <h3 class="page-title">
                        <button class="btn btn-danger" onclick="window.history.back()"><i class="icon-arrow-left"></i></button>
                        Consulta de Articulos
                    </h3>
                    <!-- END PAGE TITLE & BREADCRUMB-->
                </div>
            </div>

            <div class="row-fluid">
                <?php $this->load->view('consulta_articulo/detalle_art'); ?>

                <?php
                if (isset($articulo)) {
                    if (count($articulo) > 0) {
                        ?>

                        <div class="span2"  id="foto-articulo">
                            <div class="widget red">
                                <div class="widget-title">
                                    <h4><i class="icon-reorder"></i> Foto</h4>
                                    <span class="tools">
                                        <a href="javascript:;" class="icon-chevron-down"></a>
                                        <a href="javascript:;" class="icon-remove"></a>
                                    </span>
                                </div>
                                <div class="widget-body">
                                    <img src="<?php echo base_url() . 'assets/uploads/files/fotos_articulos/' . ($articulo['imagen']==''?'artsinfoto.jpg':$articulo['imagen']);?>" >
                                </div>
                            </div>
                        </div>
                         
                        <div class="span9"  id="detalles-articulo">
                            <div class="widget red">
                                <div class="widget-title">
                                    <h4><i class="icon-reorder"></i> Detalles del Articulo</h4>
                                    <span class="tools">
                                        <a href="javascript:;" class="icon-chevron-down"></a>
                                        <a href="javascript:;" class="icon-remove"></a>
                                    </span>
                                </div>

                                <div class="widget-body">
                                    <div class="bs-docs-example">
                                        <ul class="nav nav-tabs" id="myTab">
                                            <li class="active"><a data-toggle="tab" href="#comparativo">Comparativo</a></li>
                                            <li class=""><a data-toggle="tab" href="#compras">Compras</a></li>
                                            <li class=""><a data-toggle="tab" href="#stock">Stock</a></li>
                                        </ul>
                                        <div class="tab-content" id="myTabContent">
                                            <div id="comparativo" class="tab-pane fade active in">
                                                <table class="table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>PROVEEDOR</th>
                                                            <th>CODIGO</th>
                                                            <th>DESCRIPCION</th>                                                            
                                                            <th>PRECIO</th>
                                                            <th>MARCA NRO</th>
                                                            <th>MARCA</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $sql_vw = "SELECT original, precio_publico, descripcion, precio_iva, cod_dto
                                                        FROM lista_vw 
                                                        WHERE original = '" . $articulo['codigo_oem'] . "'";    
                                                        $res = mysql_query($sql_vw);
                                                        while ($row = mysql_fetch_array($res)) {
                                                            $precio_publico = $row['precio_publico'];
                                                            $precio_publico = ($precio_publico * 1) / 100;
                                                            $precio_publico = number_format($precio_publico, 2 ,'.', ',');
                                                            
                                                            $codigo_dto = $row['cod_dto'];
                                                            
                                                            $precio_iva = $row['precio_iva'];
                                                            $precio_iva = ($precio_iva * 1) / 100;
                                                            $margen = 1.6666;
                                            
                                                            //Precio Mas IVA
                                                            $precio_venta = $precio_iva; 
                                            
                                            
                                                            //consulta la tabla de dtos del proveedor 5 (001 => FENIX)
                                                            $sql_dto = "select porcentaje_dto from proveedor_dto_vw where proveedor_id = 5 and codigo = '$codigo_dto'";
                                                            $res_dto = mysql_query($sql_dto);
                                                            $row_dto = mysql_fetch_array($res_dto);
                                                            $porc_dto = $row_dto['porcentaje_dto'];

                                                            $precio_venta = $precio_venta - (($precio_venta * $porc_dto) / 100);

                                                            //Margen de Ganancia    
                                                            $precio_venta = $precio_venta * $margen;

                                                            //$precio_venta = $precio_iva;
                                                            $precio_venta = number_format($precio_venta, 2, '.', ',');
                                                        ?>
                                                            <tr>
                                                                <td>VW</td>
                                                                <td><?php echo $row['original'];?></td>
                                                                <td><?php echo $row['descripcion'];?></td>
                                                                <td><?php echo $precio_venta;?></td>
                                                                <td></td>
                                                                <td></td>
                                                            </tr>
                                                        <?php
                                                        //Verifica si tiene oferta
                                                            $sql_oferta = "SELECT articulo, descripcion, precio FROM ofertas WHERE articulo = '" . $row['original'] . "'";
                                                            $res_oferta = mysql_query($sql_oferta);
                                                            $nrows_oferta = mysql_num_rows($res_oferta);
                                                            $row_oferta = mysql_fetch_array($res_oferta);
                                                            if ($nrows_oferta > 0) {
                                                                $precio_oferta = str_replace("\$", "", $row_oferta['precio']); 
                                                                //$precio_oferta = str_replace(".", "", $precio_oferta); 
                                                                //$precio_oferta = str_replace(",", ".", $precio_oferta);
                                                                $precio_oferta = str_replace(".", ",", $precio_oferta); 
                                                                $precio_oferta = str_replace(",", "", $precio_oferta);  
                                                            ?>
                                                            <tr style="background-color: orange">
                                                                <td>OFERTA</td>
                                                                <td><?php echo $row_oferta['articulo'];?></td>
                                                                <td><?php echo $row_oferta['descripcion'];?></td>
                                                                <td><?php echo $precio_oferta;?></td>
                                                                <td></td>
                                                                <td></td>
                                                            </tr>
                                                            <?php
                                                            }
                                                        }
                                                        
                                                        //Otros proveedores
                                                        $sql="SELECT rp.`interno_proveedor`,rp.`proveedor_id` 
                                                              FROM costos_proveedor  rp 
                                                              WHERE codigo_fenix = '" . $articulo['codigo_fenix'] . "'";
                                                        $res = mysql_query($sql);
                                                        while($row = mysql_fetch_array($res)){
                                                            
                                                            $proveedor_costo = $row['proveedor_id'];
                                                            $interno_prov = $row['interno_proveedor'];
                                                            
                                                               $sql="SELECT cod_cromosol, descripcion, marca, precio_lista 
                                                                    FROM lista_cromosol WHERE cod_cromosol = '$interno_prov'";
                                                               $res = mysql_query($sql);
                                                                while($row = mysql_fetch_array($res)){
                                                                ?>
                                                                    <tr>
                                                                        <td>CROMOSOL</td>
                                                                        <td><?php echo $row['cod_cromosol'];?></td>
                                                                        <td><?php echo $row['descripcion'];?></td>
                                                                        <td><?php echo $row['precio_lista'];?></td>
                                                                        <td></td>
                                                                        <td><?php echo $row['marca'];?></td>
                                                                    </tr>
                                                                <?php   
                                                                }
                                                         }  
                                                        ?>

                                                    </tbody>
                                                </table>
                                            </div>
                                            <div id="compras" class="tab-pane fade">
                                                <table class="table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>CODIGO</th>
                                                            <th>PROVEEDOR</th>
                                                            <th>PRECIO</th>

                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    <tr>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                    </tr>

                                                    </tbody>
                                                </table>
                                            </div>
                                            <div id="stock" class="tab-pane fade">
                                                <table class="table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>Sucursal</th>
                                                            <th>Ubicacion</th>
                                                            <th>Stock</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $sql_stock = "SELECT cantidad, c.`nombre`,ubicacion FROM stock s INNER JOIN sucursales c ON c.`id` = s.`sucursal_id` WHERE articulo_fenix = '" . $articulo['codigo_fenix'] . "'";    
                                                        $res = mysql_query($sql_stock);
                                                        while ($row = mysql_fetch_array($res)) {
                                                        ?>
                                                            <tr>
                                                                <td><?php echo $row['nombre'];?></td>
                                                                <td><?php echo $row['ubicacion'];?></td>
                                                                <td><?php echo $row['cantidad'];?></td>
                                                            </tr>
                                                        <?php    
                                                        }
                                                        ?>
                                                        
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        


                        <?php
                    }
                }
                ?>

            </div>

        <!-- END PAGE -->  
    </div>


    <!-- END PAGE -->
    <?php $this->load->view('administrador/dashboard/footer'); ?>

    <script>
        function consultarArt() {
            //$('#detalles-articulo').attr('style','display:bock');
            $('#form_consulta').submit();
        }

        $('input[type=text]').keypress(function(e) {
            if (e.which == 13) {
                $('#form_consulta').submit();
            }
        });
    </script>
