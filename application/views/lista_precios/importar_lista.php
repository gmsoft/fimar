<?php ini_set('MAX_EXECUTION_TIME', -1);



if (isset($_GET['lista'])) {
    
    $registros_importados = 0;
    
     $contador_actualizo = 0;
     $contador_nuevos = 0;
     $contador_bajo_costo = 0;
     $contador_noactualizo = 0;

     $articulos_nuevos_csv = '';
     $articulos_noactualizo_csv = '';
     $articulos_bajo_costo_csv = '';
    
    $id_lista = $_GET['lista'];

    $query = "SELECT proveedor_id, p.`nombre_proveedor` , l.`nombre_archivo`, l.`id`
        , l.`nombre_tabla`, l.`columnas`, l.`separador_columnas`,l.`columna_cod_interno`,p.`codigo_proveedor`   
        FROM listas l
        INNER JOIN proveedores p ON p.`id` =  l.`proveedor_id`
        WHERE l.`id` = $id_lista";
    $result = mysql_query($query);     
    
    $row = mysql_fetch_array($result);         
    $archivo = $row['nombre_archivo'];
    $archivo = str_replace('\\', '/', $archivo);
    $proveedor_id = $row['proveedor_id'];
    $proveedor_nombre = $row['nombre_proveedor'];
    $codigo_proveedor = $row['codigo_proveedor'];
    $nombre_tabla = $row['nombre_tabla'];
    $columnas = $row['columnas'];
    $separador_columnas = $row['separador_columnas'];
    $columna_cod_interno = $row['columna_cod_interno'];
    /*
        Lista VW
    */
    if($proveedor_id == 1) {
        //truncate
        $sql_truncate = "truncate table lista_vw";
        $res = mysql_query($sql_truncate);

        //Load Data
        $load_data = "LOAD DATA LOCAL INFILE '$archivo' 
                        INTO TABLE lista_vw (@row) 
                        SET 
                        campo1 = TRIM(SUBSTR(@row,1,4)),
                        campo2 = TRIM(SUBSTR(@row,5,1)),
                        original = TRIM(SUBSTR(@row,6,20)), 
                        precio_publico = TRIM(SUBSTR(@row,26,11)),
                        campo5 = TRIM(SUBSTR(@row,37,4)), 
                        ue = TRIM(SUBSTR(@row,41,7)), 
                        descripcion = TRIM(SUBSTR(@row,48,25)),
                        reemplazo = TRIM(SUBSTR(@row,85,21)),
                        cod_dto = TRIM(SUBSTR(@row,133,1)),
                        precio_iva = TRIM(SUBSTR(@row,142,11))";
        $res = mysql_query($load_data);

        //Agrega columna ID
        $sql_columna_id = "ALTER TABLE lista_vw ADD id INT(11)";//" PRIMARY KEY AUTO_INCREMENT";
        $res_ci = mysql_query($sql_columna_id);

        //Agrega Clave Primaria
        $sql_pk = "ALTER TABLE lista_vw ADD PRIMARY KEY (id)";//" ";
        $res_pk = mysql_query($sql_pk);

        //cantidad de registros
        $sql_cant = "select count(*) as registros from lista_vw";    
        $res = mysql_query($sql_cant);
        $row = mysql_fetch_array($res);
        $registros_importados = $row['registros'];
    }

    /*
        Lista FENIX
    */
    if($proveedor_id == 5) {
        //truncate
        $sql_truncate = "truncate table lista_fenix";
        $res = mysql_query($sql_truncate);

        //Load Data
        $load_data = "LOAD DATA LOCAL INFILE '$archivo' 
                      INTO TABLE lista_fenix  
                      FIELDS TERMINATED BY ';'";
        $res = mysql_query($load_data);

        //Actualiza los articulos
        
        $sql_actualiza = "select codigo_fenix, codigo_oem, descripcion, stock, ubicacion, proveedor, reemplazo, marca, precio 
                          from lista_fenix";    
        $res = mysql_query($sql_actualiza);
        while($row = mysql_fetch_array($res)){
            
            $codigo_fenix = $row['codigo_fenix'];
            $codigo_oem = $row['codigo_oem'];
            $descripcion = $row['descripcion'];
            $descripcion = str_replace("'", "", $descripcion);
            $stock = $row['stock'];
            $ubicacion = $row['ubicacion'];
            $proveedor = $row['proveedor'];
            $reemplazo = $row['reemplazo'];
            $marca = $row['marca'];
            $precio = str_replace("\$", "", $row['precio']);
            $precio = str_replace(".", "", $precio);

            $sql_art = "select * from articulos WHERE codigo_fenix = '$codigo_fenix'";
            $res_art = mysql_query($sql_art);
            $c = 0;
            while($row_art = mysql_fetch_array($res_art)) {
                $c++;    
                $update_sql = "UPDATE articulos SET codigo_oem = '$codigo_oem',"
                        . " descripcion = '$descripcion', precio_lista = '$precio',"
                        . " marca = '$marca', proveedor_testigo = '$proveedor' "
                        . " WHERE codigo_fenix = '$codigo_fenix'";
                $res_update = mysql_query($update_sql);
                if(!$res_update){
                    die('Error en:' . $update_sql);
                }
            }

            if($c == 0) {
                 $insert_sql = "INSERT INTO articulos(codigo_fenix, codigo_oem, descripcion, precio_lista) 
                                            values('$codigo_fenix', '$codigo_oem', '$descripcion', '$precio')";   
                 $res_insert = mysql_query($insert_sql);        
                 if(!$res_update){
                    die('Error en:' . $insert_sql);
                 }
            }
            
            //Actualiza stock y ubicacion
            /*
            $sql_stk = "SELECT articulo_fenix FROM stock WHERE articulo_fenix = '$codigo_fenix'";
            $res_stk = mysql_query($sql_stk);
            $c_stk = 0;
            while($row_stk = mysql_fetch_array($res_stk)) {
                $c_stk++;    
                $update_stk = "UPDATE stock SET cantidad = '$stock', ubicacion = '$ubicacion'
                                WHERE articulo_fenix = '$codigo_fenix'";
                $res_update_stk = mysql_query($update_stk); 
            }

            if($c_stk == 0) {
                 $insert_stk = "INSERT INTO stock(articulo_fenix, cantidad, sucursal_id, ubicacion) 
                                            values('$codigo_fenix', '$stock', '1', '$ubicacion')";   
                 $res_insert = mysql_query($insert_stk);        
            }
            */
        }

        //cantidad de registros
        $sql_cant = "select count(*) as registros from lista_fenix";    
        $res = mysql_query($sql_cant);
        $row = mysql_fetch_array($res);
        $registros_importados = $row['registros'];
    }
    
    /*
        Lista OFERTAS
    */
    if($proveedor_id == 7) {
        //truncate
        $sql_truncate = "truncate table ofertas";
        $res = mysql_query($sql_truncate);

        //Load Data
        $load_data = "LOAD DATA LOCAL INFILE '$archivo' 
                      INTO TABLE ofertas  
                      FIELDS TERMINATED BY ';'";
        $res = mysql_query($load_data);

        //cantidad de registros
        $sql_cant = "select count(*) as registros from ofertas";    
        $res = mysql_query($sql_cant);
        $row = mysql_fetch_array($res);
        $registros_importados = $row['registros'];
    }
    
    /*
        Otros proveedores
    */
    if($proveedor_id != 1 && $proveedor_id != 5 && $proveedor_id != 7) {
        
        $sql_articulos_actualizar = ''; 
        //Borra la tabla
        $sql_droptable = "DROP TABLE IF EXISTS $nombre_tabla;";
        $res_droptable = mysql_query($sql_droptable);
       
        
        if ($res_droptable) {
            //Create tabla
            $sql_createtable = "CREATE TABLE $nombre_tabla (";
            $cols = explode(',', $columnas);
            foreach ($cols as $k => $colname) {
                $sql_createtable .= "$colname varchar(255)". ($k < (count($cols)-1)?',':'');
            }
            $sql_createtable .= ');';
            $res_createtable = mysql_query($sql_createtable);

            if ($res_createtable) {
                //Importa los datos
                 $load_data = "LOAD DATA LOCAL INFILE '$archivo' 
                              INTO TABLE $nombre_tabla  
                              FIELDS TERMINATED BY '$separador_columnas'";
                 $res_loaddata = mysql_query($load_data);
                 
                 //Si cargo bien el archivo de la lista
                 if ($res_loaddata) {
                    //Busca los articulos cargados en costos del proveedor
                    $sql_costos_cargados = "SELECT codigo_fenix, interno_proveedor, descripcion, marca, costo, utilidad"
                            . " FROM costos_proveedor WHERE proveedor_id = $proveedor_id";
                    $res_costos_cargados = mysql_query($sql_costos_cargados);
                    //echo $sql_costos_cargados.'<br>';
                    while($row_costop = mysql_fetch_array($res_costos_cargados)) {
                        $interno_prov = $row_costop['interno_proveedor'];
                        $codigo_fenix = $row_costop['codigo_fenix'];
                        $descripcion_costo = $row_costop['descripcion'];
                        $marca_costo = $row_costop['marca'];
                        $costo_actual = $row_costop['costo'];
                        
                        //Busca el articulo en la lista 
                        $sql_costos_lista = "SELECT $columnas FROM $nombre_tabla "
                                . " WHERE $columna_cod_interno = '$interno_prov'";
                        $res_costo_lista = mysql_query($sql_costos_lista);
                        
                        while($row_costo_lista = mysql_fetch_array($res_costo_lista)) {
                            //id, cod_cromosol, descripcion, marca, precio_lista, cod_oem
                            $descripcion_lista = $row_costo_lista['descripcion'];
                            $marca_lista = $row_costo_lista['marca'];
                            $costo_nuevo = $row_costo_lista['precio_lista'];
                            
                            $sql_update = "UPDATE costos_proveedor";
                            
                            $sql_update .= " SET ";
                            
                            if(($descripcion_costo == $descripcion_lista) || $descripcion_costo == '') {
                                $sql_update .= " descripcion = '$descripcion_lista', ";
                            }
                            
                            if ($marca_costo == $marca_lista || $marca_costo == '') {
                                $sql_update .= " marca = '$marca_lista', "; 
                            }
                            
                            //BAJO COSTO
                            if(($costo_nuevo * 1) < ($costo_actual * 1)){
                                $sql_update .= " costo = '$costo_nuevo', ";
                            } else {
                                $sql_update .= " costo = '$costo_nuevo', ";
                            }
                            
                            $sql_update .= " costo_anterior = '$costo_actual', ";
                            
                            $sql_update .= " fecha = now() ";
                            
                            $sql_update .= " WHERE proveedor_id = $proveedor_id AND codigo_fenix = '$codigo_fenix' AND interno_proveedor = '$interno_prov';";
                            
                            $res_update = mysql_query($sql_update);
                            
                            if ($res_update) {
                                $contador_actualizo++;
                                
                                //Bajo el costo
                                if(($costo_nuevo * 1) < ($costo_actual * 1)){
                                    $contador_bajo_costo++;
                                    $articulos_bajo_costo_csv .= $codigo_fenix.';'.$interno_prov.';'.$descripcion_lista.';'.$descripcion_costo.'\r\n';
                                }
                                
                                //No actualizados
                                if(($costo_nuevo * 1) == ($costo_actual * 1)){
                                    $contador_noactualizo++;
                                    $articulos_noactualizo_csv .= $codigo_fenix.';'.$interno_prov.','.$descripcion_lista.';'.$descripcion_costo.'\r\n';
                                }
                                
                            }
                            
                            $sql_articulos_actualizar .= $sql_update;
                        }    
                    }
                    
                    //
                    //Articulos nuevos
                    //
                    $sql_nuevos = "select $columna_cod_interno, descripcion, marca, precio_lista FROM $nombre_tabla c where c.`$columna_cod_interno` NOT IN (SELECT interno_proveedor FROM costos_proveedor WHERE proveedor_id = $proveedor_id)";
                    $res_nuevos = mysql_query($sql_nuevos);
                    while ($row_nuevos = mysql_fetch_array($res_nuevos)) {
                        $articulos_nuevos_csv .= $row_nuevos[$columna_cod_interno].';'.$res_nuevos['descripcion'].';'.$res_nuevos['marca']. '\n\r';
                        $contador_nuevos++;
                    }
                    
                    $filename_nuevos = 'assets/uploads/files/importacion/' . $codigo_proveedor.'-'.date('Ymd').'-NUEVOS.csv';
                    $file_nuevos = fopen($filename_nuevos, "w");
                    fwrite($file_nuevos, $articulos_nuevos_csv);
                    fclose($file_nuevos);
                    
                    //
                    //Bajaron Costo
                    //
                    $filename_bajaron = 'assets/uploads/files/importacion/' .$codigo_proveedor.'-'.date('Ymd').'-SUBEUTILIDAD.csv';
                    $file_bajocosto = fopen($filename_bajaron, "w");
                    fwrite($file_bajocosto, $articulos_bajo_costo_csv);
                    fclose($file_bajocosto);
                    
                    //
                    //No Actualizaron
                    //
                    $filename_noactualizaron = 'assets/uploads/files/importacion/' . $codigo_proveedor.'-'.date('Ymd').'-NOACTUALIZADOS.csv'; 
                    $file_noactualizo = fopen($filename_noactualizaron, "w");
                    fwrite($file_noactualizo, $articulos_noactualizo_csv);
                    fclose($file_noactualizo);
                    
                    
                    //cantidad de registros
                   $sql_cant = "select count(*) as registros from $nombre_tabla";    
                   $res = mysql_query($sql_cant);
                   $row = mysql_fetch_array($res);
                   $registros_importados = $row['registros'];
                 }
            }
        }
        
    }

    //Update lista
    $sql_update_fecha = "UPDATE listas SET fecha_actualizacion = now() where id = $id_lista";
    $res = mysql_query($sql_update_fecha);
    

}
?>

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
                        Importar listas de precio
                    </h3>
                    <!-- END PAGE TITLE & BREADCRUMB-->
                </div>
            </div>
            <?php
               $query = "SELECT proveedor_id, p.`nombre_proveedor` , l.`nombre_archivo`, l.`id`
                    FROM listas l
                    INNER JOIN proveedores p ON p.`id` =  l.`proveedor_id`";
               $result = mysql_query($query);     
            ?>
            <div>
                <form method="get" action="" id="form-importar">
                    <label>Lista<label>
                    <select name="lista">
                        <?php 
                         while($row = mysql_fetch_array($result)) {
                            ?>
                            <option value="<?php echo $row['id'];?>"><?php echo $row['nombre_proveedor'];?></option>
                            <?php    
                         }  
                        ?>                    
                    </select>
                    <br/>
                    <button type="submit" id="btn-importar" class="btn btn-primay">Importar</button>
                </form>  
            </div>
            <br/>
            <?php

            if(isset($_GET['lista'])) {
                echo 'Se importaron ' . number_format($registros_importados,0,'','.') . ' registros de la lista '. $proveedor_nombre . '<br/>';
                echo 'Se actualizaron ' . number_format($contador_actualizo,0,'','.') . ' registros en el costo del proveedor<br>';
                echo 'Hay ' . number_format($contador_nuevos,0,'','.') . ' articulos nuevos <a href="http://'.$_SERVER['HTTP_HOST'].'/sistema/'.$filename_nuevos.'">Descargar CSV</a><br>';
                echo 'Hay ' . number_format($contador_bajo_costo,0,'','.') . ' articulos que bajaron de precio <a href="http://'.$_SERVER['HTTP_HOST'].'/sistema/'.$filename_bajaron.'">Descargar CSV</a><br>';
                echo 'Hay ' . number_format($contador_noactualizo,0,'','.') . ' articulos que no se actualizaron <a href="http://'.$_SERVER['HTTP_HOST'].'/sistema/'.$filename_noactualizaron.'">Descargar CSV</a><br>';
            }

            ?>    
            <script src="//oss.maxcdn.com/jquery/1.8.3/jquery-1.8.3.min.js"></script>
            
            <script src="<?= base_url() ?>assets/js/jquery.blockUI.js"></script>
            <script src="<?= base_url() ?>assets/js/ajaxupload.3.5.js"></script>
            <script src="<?= base_url() ?>assets/js/application.js"></script>

        </div>
    </div>
</div>
<script>
    $('#btn-importar').click(function(){
        $(this).text("Importando...");
        $(this).attr('disabled',true);
        $('#form-importar').submit();
    });
</script>
 <!-- END PAGE -->
 <?php $this->load->view('administrador/dashboard/footer'); ?>

