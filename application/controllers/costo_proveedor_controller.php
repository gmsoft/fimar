<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class costo_proveedor_controller extends CI_Controller {

    private $_guestProfile;
    
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
                //user is already logged in
                redirect('ingresar');
        } else {
            $this->load->library('grocery_CRUD');
            $this->_guestProfile = $this->session->userdata('logged_in');
        }
    }

    public function index()
    {       
        $this->load->view('administrador/dashboard/dashboard.php');
    }
    
    public function menu() 
    {
       $this->load->view('costos/costos_proveedor.php');
        /*try {

            $crud = new grocery_CRUD();
            $crud->set_theme('twitter-bootstrap');
            $crud->set_table('relacion_proveedor');
            $crud->set_subject('Relacion Proveedor');

            $crud->set_relation('proveedor_id', 'proveedores', '{codigo_proveedor}, {nombre_proveedor}');    
            $crud->set_relation('proveedor_id', 'proveedores', '{codigo_proveedor}, {nombre_proveedor}');    
            
            $output = $crud->render();
            $this->output('administrador/default_layout/abm.php', $output);
            
        } catch(Exception $e){
                show_error($e->getMessage().' --- '.$e->getTraceAsString());
        }*/
    }

   
    public function guardarCosto() {
        $var = $_POST;
        $proveedor = $var['proveedor'];
        $fecha = $var['fecha'];
        $codfenix = $var['codfenix'];
        $interno = $var['interno'];
        $costo = $var['costo'];
        $dto1 = $var['dto1'];
        $dto2 = $var['dto2'];
        $dto3 = $var['dto3'];
        $rec1 = $var['rec1'];
        
        //---
        $var = $_POST;
        $proveedor = $var['proveedor'];
        $codfenix = $var['codfenix'];
        $interno = $var['interno'];
        
        //TODO: Falta filtro por moneda
        $sql_costo = "SELECT costo, moneda, dto1, dto2, dto3, rec1 "
                . "FROM costos_proveedor "
                . "WHERE proveedor_id = $proveedor "
                . "AND codigo_fenix = '$codfenix' "
                . "AND interno_proveedor = '$interno'";                
        $res_costo = mysql_query($sql_costo);
        
        $num_row = mysql_num_rows($res_costo);
        
        //
        if($num_row == 0) {
            $sql = "INSERT INTO costos_proveedor "
                    . "(proveedor_id, codigo_fenix, interno_proveedor, fecha, costo, moneda, dto1, dto2, dto3, rec1 )"
                    . " VALUES".
                "('$proveedor','$codfenix','$interno','$fecha','$costo', 'PES' , '$dto1', '$dto2','$dto3','$rec1')";
        } else {
            $sql = "UPDATE costos_proveedor SET costo = '$costo', costo_anterior = costo, dto1 = '$dto1', dto2 = '$dto2', dto3 = '$dto3', rec1 = '$rec1' "
                    . " WHERE proveedor_id = '$proveedor' AND codigo_fenix = '$codfenix' AND interno_proveedor = '$interno'";
        }
        
        $res = mysql_query($sql);
        
        if($res){
            echo 'Nuevo costo';
        } else {
            echo 'Error: ' . $sql;
        }
    }
    
    public function buscarCosto(){
        
        $var = $_POST;
        $proveedor = $var['proveedor'];
        $codfenix = $var['codfenix'];
        $interno = $var['interno'];
        
        //TODO: Falta filtro por moneda
        $sql_costo = "SELECT costo, moneda, dto1, dto2, dto3, rec1 "
                . "FROM costos_proveedor "
                . "WHERE proveedor_id = $proveedor "
                . "AND codigo_fenix = '$codfenix' "
                . "AND interno_proveedor = '$interno'";
                
        $res_costo = mysql_query($sql_costo);
        
        //die($sql_costo);
        
        $items = array(); 
        $items['costo'] = 0;
        $items['dto1'] = 0;
        $items['dto2'] = 0;
        $items['dto3'] = 0;
        $items['rec1'] = 0;
        
        while($row_costo = mysql_fetch_array($res_costo)) {
            
            $costo = $row_costo['costo'];
            $dto1 = $row_costo['dto1'];
            $dto2 = $row_costo['dto2'];
            $dto3 = $row_costo['dto3'];
            $rec1 = $row_costo['rec1'];
            
            $items['costo'] = $costo * 1;
            $items['dto1'] = $dto1 * 1;
            $items['dto2'] = $dto2 * 1;
            $items['dto3'] = $dto3 * 1;
            $items['rec1'] = $rec1 * 1;
            
        }
        
        echo json_encode($items);
    }


    /**
     * Muestra las vistas
     * @param string $output 
     */
    public function output($view, $output = null)
    {
        $this->load->view($view, $output);
    }
}

?>